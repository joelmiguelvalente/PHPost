<?php

declare(strict_types=1);

/**
 * @package    Database
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class DB {

	public static function db(): Database {
		return Database::instance();
	}

	// Sección: Fetch --------------------------------------------------

	public static function fetch(string $sql, array $params = []): ?array {
		return self::db()->preparedFetch($sql, $params);
	}

	public static function fetchAll(string $sql, array $params = []): array {
		return self::db()->preparedFetchAll($sql, $params);
	}

	public static function fetchRow(string $sql, array $params = []): ?array {
		$stmt = self::db()->preparedQuery($sql, $params);
		$row = $stmt->fetch(PDO::FETCH_NUM) ?: null;
		$stmt->closeCursor();
		return $row;
	}

	// Sección: Query --------------------------------------------------

	public static function query(string $sql, array $params = []): PDOStatement {
		return self::db()->preparedQuery($sql, $params);
	}

	public static function numRows(string $sql, array $params = []): int {
		$stmt = self::db()->preparedQuery($sql, $params);
		$count = $stmt->rowCount();
		$stmt->closeCursor();
		return $count;
	}

	/**
	 * USO:
	 * if (DB::exists("SELECT 1 FROM users WHERE email = :mail", ['mail' => $mail])) {
	 * 	throw new Exception('Email ya existe');
	 * }
	 */
	public static function exists(string $sql, array $params = []): bool {
		return self::numRows($sql, $params) > 0;
	}

	/**
	 * USO:
	 * $username = DB::value("SELECT username FROM users WHERE id = :id", ['id' => 10]);
	 */
	public static function value(string $sql, array $params = []): mixed {
		$row = self::fetchRow($sql, $params);
		return $row[0] ?? null;
	}

	/**
	 * USO:
	 * $usernames = DB::column("SELECT username FROM users WHERE active = :active", ['active' => 1]);
	 * resultado: ["Miguel92","Admin","Pepe"]
	 */
	public static function column(string $sql, array $params = []): array {
		return array_column(self::fetchAll($sql, $params), 0);
	}

	/**
	 * USO:
	 * $id = DB::insert('users', ['username' => 'Miguel92', 'email' => 'test@mail.com']);
	*/
	public static function insert(string $table, array $data): int {
		if (!$data) {
			throw new InvalidArgumentException('Insert vacío');
		}
		$columns = array_keys($data);
		$fields = implode(', ', $columns);
		$placeholders = ':' . implode(', :', $columns);
		$sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
		self::query($sql, $data);
		return self::insertId();
	}

	/**
	 * USO:
	 * DB::update('users', ['username' => 'NuevoNombre'], 'id = :id', ['id' => 5]);
	*/
	public static function update(string $table, array $data, string $where, array $whereParams = []): int {
		$set = [];
		foreach ($data as $column => $value) {
			$set[] = "{$column} = :set_{$column}";
		}
		$sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
		$params = [];
		foreach ($data as $column => $value) {
			$params["set_{$column}"] = $value;
		}
		$params += $whereParams;
		$stmt = self::query($sql, $params);
		$count = $stmt->rowCount();
		$stmt->closeCursor();
		return $count;
	}

	/**
	 * USO:
	 * DB::delete('users', 'id = :id', ['id' => 5]);
	*/
	public static function delete(string $table, string $where, array $params = []): int {
		$sql = "DELETE FROM {$table} WHERE {$where}";
		$stmt = self::query($sql, $params);
		$count = $stmt->rowCount();
		$stmt->closeCursor();
		return $count;
	}

	/**
	 * UPSERT - Inserta o actualiza si existe
	 * USO:
	 * DB::upsert('u_lockout',
	 *    ['user_id' => $userId, 'locked_until' => DB::raw('NOW() + INTERVAL 30 MINUTE')],
	 *    ['user_id'] // columnas únicas
	 * );
	*/
	public static function upsert(string $table, array $data, array $uniqueColumns): int {
		if (!$data) {
			throw new InvalidArgumentException('Upsert vacío');
		}
		$columns = array_keys($data);
		$fields = implode(', ', $columns);
		$placeholders = ':' . implode(', :', $columns);
		$updates = [];
		foreach (array_diff($columns, $uniqueColumns) as $column) {
			$updates[] = "{$column} = VALUES({$column})";
		}
		$updateClause = !empty($updates) ? ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates) : '';
		$sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders}){$updateClause}";
		$stmt = self::query($sql, $data);
		$count = $stmt->rowCount();
		$stmt->closeCursor();
		return $count;
	}

	/**
	 * Consulta raw con parámetros
	 * USO:
	 * DB::raw("SELECT * FROM users WHERE id = :id AND status = :status", ['id' => $id, 'status' => $status]);
	*/
	public static function raw(string $sql, array $params = []): PDOStatement {
		return self::query($sql, $params);
	}

	/**
	 * Incrementa un contador numérico en cualquier tabla
	 * USO:
	 * DB::increment('w_stats', 'stats_comments', 'stats_no = :stats_no', ['stats_no' => 1]);
	 */
	public static function increment(string $table, string $column, string $where, array $params = []): void {
		$sql = "UPDATE {$table} SET {$column} = {$column} + 1 WHERE {$where}";
		self::query($sql, $params);
	}

	/**
	 * Decrementa un contador numérico en cualquier tabla
	 * USO:
	 * DB::decrement('w_stats', 'stats_comments', 'stats_no = :stats_no', ['stats_no' => 1]);
	 */
	public static function decrement(string $table, string $column, string $where, array $params = []): void {
		$sql = "UPDATE {$table} SET {$column} = GREATEST({$column} - 1, 0) WHERE {$where}";
		self::query($sql, $params);
	}

	// Sección: Utility --------------------------------------------------

	public static function insertId(): int {
		return self::db()->insertId();
	}

	public static function escape(string $value): string {
		return self::db()->escape($value);
	}

	public static function lastError(): array {
		return self::db()->lastError();
	}

	// Sección: Low level --------------------------------------------------

	public static function free(PDOStatement $result): void {
		$result->closeCursor();
	}

	// Sección: Transactions --------------------------------------------------

	public static function begin(): void {
		self::db()->beginTransaction();
	}

	public static function commit(): void {
		self::db()->commit();
	}

	public static function rollback(): void {
		self::db()->rollback();
	}

}
