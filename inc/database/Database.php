<?php

/**
 * @name Database.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class Database {

	private static ?self $instance = null;
	private mysqli $connection;

	private const INTERNAL_CLASSES = [
	   Database::class,
	   DB::class
	];

	private function __construct() {
		$this->connect();
	}

	private function __clone() {}

	public function __wakeup() {
		throw new Exception("Cannot unserialize singleton");
	}

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	private function connect(): void {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {
			$this->connection = new mysqli(
				Config::db('hostname'),
				Config::db('username'),
				Config::db('password'),
				Config::db('database')
			);

			$this->connection->set_charset(
				Config::db('charset') ?? 'utf8mb4'
			);

		} catch (mysqli_sql_exception) {
			show_error('Error de conexión a la base de datos.', 'db');
		}
	}

	/* ================= Named Prepared ================= */

	public function preparedQuery(string $sql, array $params = []): mysqli_stmt {
		try {
			$types = '';
			$values = [];
			$usedParams = [];

			$sql = preg_replace_callback('/:([a-zA-Z0-9_]+)/',
				function ($match) use ($params, &$types, &$values, &$usedParams) {
					$key = $match[1];
					if (!array_key_exists($key, $params)) {
						throw new InvalidArgumentException("Missing parameter: $key");
					}
					$value = $params[$key];
					$usedParams[] = $key;
					if (is_int($value)) {
						$types .= 'i';
					} elseif (is_float($value)) {
						$types .= 'd';
					} elseif (is_null($value)) {
						$types .= 's';
					} else {
						$types .= 's';
					}
					$values[] = $value;
					return '?';
				},
				$sql
			);

			// Validar params extra
			$extra = array_diff(array_keys($params), $usedParams);
			if ($extra) {
				throw new InvalidArgumentException('Unused parameters: ' . implode(', ', $extra));
			}
			$stmt = $this->connection->prepare($sql);
			if ($values) {
				$stmt->bind_param($types, ...$values);
			}
			$stmt->execute();
			return $stmt;
		} catch (Throwable $e) {
        	$this->handleException($e, $sql);
        	throw $e;
    	}
	}

	/* ================= Raw Query ================= */

	public function rawQuery(string $sql): mysqli_result|bool {
		try {
			return $this->connection->query($sql);
		} catch (mysqli_sql_exception $e) {
			error_log('[SQL ERROR] ' . $e->getMessage());
			return false;
		}
	}

	public function escape(string $value): string {
		return $this->connection->real_escape_string($value);
	}

	public function insertId(): int {
		return $this->connection->insert_id;
	}

	public function lastError(string $type): string|int {
		return match ($type) {
			'errno'    => $this->connection->errno,
			'error'    => $this->connection->error,
			'sqlstate' => $this->connection->sqlstate,
			default    => '',
		};
	}

	/* ================= Modern Helpers ================= */

	public function prepare(string $sql): mysqli_stmt {
		return $this->connection->prepare($sql);
	}

	public function fetchAll(mysqli_result $result): array {
		return $result->fetch_all(MYSQLI_ASSOC);
	}

	public function fetch(mysqli_result $result): ?array {
		return $result->fetch_assoc() ?: null;
	}

	public function fetchRow(mysqli_result $result): ?array {
		return $result->fetch_row() ?: null;
	}

	public function numRows(mysqli_result $result): int {
		return $result->num_rows;
	}

	private function getCaller(): array {
	   $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
	   foreach ($trace as $frame) {
	   	if (!isset($frame['file'])) {
	         continue;
	      }
	      // Ignorar cualquier llamada dentro de Database.php o DB.php
	      if (str_contains($frame['file'], 'Database.php') || str_contains($frame['file'], 'DB.php')) {
	         continue;
	      }
	      return [
	         'file' => $frame['file'],
	         'line' => $frame['line'] ?? null,
	         'function' => $frame['function'] ?? null,
	         'class' => $frame['class'] ?? null
	      ];
	    }

	    return [];
	}

	/* ================= Prepared Fetch Helpers ================= */

	public function preparedFetch(string $sql, array $params = []): ?array {
		$stmt = $this->preparedQuery($sql, $params);
		$result = $this->safeGetResult($stmt)->fetch_assoc() ?: null;
		$stmt->close();
		return $result;
	}

	public function preparedFetchAll(string $sql, array $params = []): array {
		$stmt = $this->preparedQuery($sql, $params);
		$result = $this->safeGetResult($stmt)->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
		return $result;
	}

	private function safeGetResult(mysqli_stmt $stmt): mysqli_result {
		$result = $stmt->get_result();

		if (!$result) {
			throw new RuntimeException(
				'mysqlnd is required for get_result()'
			);
		}

		return $result;
	}

	/* ================= Transactions ================= */

	public function beginTransaction(): void {
		$this->connection->begin_transaction();
	}

	public function commit(): void {
		$this->connection->commit();
	}

	public function rollback(): void {
		$this->connection->rollback();
	}

	/* ================= Low-level ================= */

	public function connection(): mysqli {
		return $this->connection;
	}

	private function handleException(Throwable $e, string $query): void {
	   global $tsUser, $tsAjax;

	   $caller = $this->getCaller();

	   if (!$tsAjax && Config::app('debug.active') && ($tsUser->is_admod || Config::app('debug.active'))) {
	     	show_error('Error en consulta SQL.', 'db', [
	     	   'file'  => $caller['file'],
	     	   'line'  => $caller['line'],
	     	   'query' => $query,
	     	   'error' => $e->getMessage()
	     	]);
	   }
	}

}
