<?php

declare(strict_types=1);

/**
 * @package    Database
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class Database {

	private static ?self $instance = null;
	private PDO $connection;
	private int $transactionLevel = 0;

	private const INTERNAL_CLASSES = [
	   Database::class,
	   DB::class
	];

	private const RECOVERABLE_ERRORS = [2006, 2013];

	private const SLOW_QUERY_THRESHOLD_MS = 200;

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
		$dsn = sprintf(
			'mysql:host=%s;port=%d;dbname=%s;charset=%s',
			Config::db('hostname'),
			Config::db('port') ?? 3306,
			Config::db('database'),
			Config::db('charset') ?? 'utf8mb4'
		);

		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
			PDO::ATTR_TIMEOUT            => Config::db('timeout') ?? 5,
			PDO::ATTR_PERSISTENT         => (bool) (Config::db('persistent') ?? false),
		];

		if (!empty(Config::db('ssl.ca')) || !empty(Config::db('ssl.cert'))) {
			$options[PDO::MYSQL_ATTR_SSL_KEY]  = Config::db('ssl.key') ?? null;
			$options[PDO::MYSQL_ATTR_SSL_CERT] = Config::db('ssl.cert') ?? null;
			$options[PDO::MYSQL_ATTR_SSL_CA]   = Config::db('ssl.ca') ?? null;
		}

		try {
			$this->connection = new PDO($dsn, Config::db('username'), Config::db('password'), $options);
		} catch (PDOException $e) {
			error_log('[DB CONNECT ERROR] ' . $e->getMessage());
			show_error('Error de conexión a la base de datos.', 'db');
		}
	}

	// Sección: Named Prepared --------------------------------------------------

	public function preparedQuery(string $sql, array $params = []): PDOStatement {
		return $this->executeQuery($sql, $params);
	}

	private function executeQuery(string $sql, array $params, bool $isRetry = false): PDOStatement {
		$start = hrtime(true);

		try {
			$stmt = $this->connection->prepare($sql);
			$stmt->execute($params);
			$this->logSlowQuery($sql, $start);

			return $stmt;
		} catch (PDOException $e) {
			if (!$isRetry && $this->isRecoverable($e)) {
				$this->connect();
				return $this->executeQuery($sql, $params, isRetry: true);
			}

			$this->handleException($e, $sql);
			throw new DatabaseException('Error al ejecutar la consulta', previous: $e);
		}
	}

	private function isRecoverable(PDOException $e): bool {
		$code = (int) ($e->errorInfo[1] ?? 0);
		return in_array($code, self::RECOVERABLE_ERRORS, true);
	}

	private function logSlowQuery(string $sql, int $start): void {
		$elapsedMs = (hrtime(true) - $start) / 1_000_000;
		if ($elapsedMs > self::SLOW_QUERY_THRESHOLD_MS) {
			error_log(sprintf('[SLOW QUERY] %.2fms | %s', $elapsedMs, $sql));
		}
	}

	// Sección: Raw Query --------------------------------------------------

	public function rawQuery(string $sql): PDOStatement|bool {
		try {
			return $this->connection->query($sql);
		} catch (PDOException $e) {
			error_log('[SQL ERROR] ' . $e->getMessage());
			return false;
		}
	}

	public function escape(string $value): string {
		return trim($this->connection->quote($value), "'");
	}

	public function insertId(): int {
		return (int) $this->connection->lastInsertId();
	}

	public function lastError(): array {
		return [
			'errno'    => (int) ($this->connection->errorInfo[1] ?? 0),
			'error'    => $this->connection->errorInfo[2] ?? '',
			'sqlstate' => $this->connection->errorInfo[0] ?? ''
		];
	}

	// Sección: Modern Helpers --------------------------------------------------

	public function prepare(string $sql): PDOStatement {
		return $this->connection->prepare($sql);
	}

	public function fetchAll(PDOStatement $stmt): array {
		return $stmt->fetchAll();
	}

	public function fetch(PDOStatement $stmt): ?array {
		return $stmt->fetch() ?: null;
	}

	public function fetchRow(PDOStatement $stmt): ?array {
		return $stmt->fetch(PDO::FETCH_NUM) ?: null;
	}

	public function numRows(PDOStatement $stmt): int {
		return $stmt->rowCount();
	}

	// Sección: Prepared Fetch Helpers --------------------------------------------------

	public function preparedFetch(string $sql, array $params = []): ?array {
		$stmt = $this->preparedQuery($sql, $params);
		$row = $stmt->fetch() ?: null;
		$stmt->closeCursor();
		return $row;
	}

	public function preparedFetchAll(string $sql, array $params = []): array {
		$stmt = $this->preparedQuery($sql, $params);
		$result = $stmt->fetchAll();
		$stmt->closeCursor();
		return $result;
	}

	// Sección: Transactions --------------------------------------------------

	public function beginTransaction(): void {
		if ($this->transactionLevel === 0) {
			$this->connection->beginTransaction();
		} else {
			$this->connection->exec("SAVEPOINT level_{$this->transactionLevel}");
		}
		$this->transactionLevel++;
	}

	public function commit(): void {
		$this->transactionLevel--;
		if ($this->transactionLevel === 0) {
			$this->connection->commit();
		} else {
			$this->connection->exec("RELEASE SAVEPOINT level_{$this->transactionLevel}");
		}
	}

	public function rollback(): void {
		$this->transactionLevel--;
		if ($this->transactionLevel === 0) {
			$this->connection->rollBack();
		} else {
			$this->connection->exec("ROLLBACK TO SAVEPOINT level_{$this->transactionLevel}");
		}
	}

	// Sección: Low Level --------------------------------------------------

	public function connection(): PDO {
		return $this->connection;
	}

	private function handleException(Throwable $e, string $query): void {
		global $tsUser, $tsAjax;

		$caller = $this->getCaller();

		error_log(sprintf(
			'[SQL ERROR] %s | file=%s line=%s | query=%s',
			$e->getMessage(),
			$caller['file'] ?? 'unknown',
			$caller['line'] ?? '?',
			$query
		));

		if (!$tsAjax && Config::app('debug.active') && $tsUser->is_admod) {
			show_error('Error en consulta SQL.', 'db', [
				'file'  => $caller['file'],
				'line'  => $caller['line'],
				'query' => $query,
				'error' => $e->getMessage()
			]);
		}
	}

	private function getCaller(): array {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
		foreach ($trace as $frame) {
			if (isset($frame['class']) && in_array($frame['class'], self::INTERNAL_CLASSES, true)) {
				continue;
			}
			if (!isset($frame['file'])) {
				continue;
			}
			return [
				'file' => $frame['file'],
				'line' => $frame['line'] ?? null,
				'function' => $frame['function'] ?? null,
				'class' => $frame['class'] ?? null,
			];
		}
		return [];
	}

}

final class DatabaseException extends \RuntimeException {}
