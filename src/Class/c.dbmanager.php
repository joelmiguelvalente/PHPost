<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsDBManager
{
	// Tablas que NO se deben truncar ni eliminar nunca
	private const PROTECTED_TABLES = [
		'w_configuracion',
		'w_registro',
		'w_stats',
		'w_temas',
		'w_migrations',
		'u_miembros',
		'u_miembros_sets',
		'p_categorias',
	];

	// Tablas seguras para truncar (logs, caché, sesiones temporales)
	public const TRUNCATABLE_TABLES = [
		'u_sessions'        => 'Sesiones activas',
		'u_actividad'       => 'Actividad de usuarios',
		'u_login_attempts'  => 'Intentos de login',
		'u_lockout'         => 'Bloqueos de login',
		'w_visitas'         => 'Registro de visitas',
		'w_historial'       => 'Historial de moderación',
		'w_sitemap'         => 'Sitemap generado',
	];

	// Acceso a la conexión cruda solo para DDL y comandos especiales
	private function conn(): PDO
	{
		return Database::instance()->connection();
	}

	/* TABLAS */

	/**
	 * Devuelve tablas agrupadas por prefijo con info de tamaño
	 */
	public function getTablesInfo(): array
	{
		$rows = DB::fetchAll("SELECT TABLE_NAME, TABLE_ROWS, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024, 2) AS size_kb, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME ASC");

		$tables = [];
		foreach ($rows as $row) {
			$prefix = $this->getPrefix($row['TABLE_NAME']);
			$tables[$prefix][] = [
				'name'      => $row['TABLE_NAME'],
				'rows'      => (int) $row['TABLE_ROWS'],
				'size_kb'   => (float) $row['size_kb'],
				'engine'    => $row['ENGINE'],
				'protected' => in_array($row['TABLE_NAME'], self::PROTECTED_TABLES, true),
			];
		}
		return $tables;
	}

	private function sanitizeTableName(string $table, array $realTables): ?string {
	    $clean = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
	    return in_array($clean, $realTables, true) ? $clean : null;
	}

	/* BACKUP */

	/**
	 * Genera un archivo .sql de backup y lo guarda en storage/backups/
	 * Devuelve la ruta relativa del archivo generado
	 */
	public function createBackup(array $tables): array
	{
		$storageBackupPath = TS_BACKUPS;
		if (empty($tables)) {
			return ['ok' => false, 'message' => 'Selecciona al menos una tabla.'];
		}

		if (!is_dir($storageBackupPath)) {
			mkdir($storageBackupPath, 0755, true);
		}

		$filename  = 'backup_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.sql';
		$filepath  = rtrim($storageBackupPath, '/') . '/' . $filename;
		$handle    = fopen($filepath, 'w');

		if (!$handle) {
			return ['ok' => false, 'message' => 'No se pudo crear el archivo de backup.'];
		}

		$conn = $this->conn();
		$db   = DB::value('SELECT DATABASE()');

		fwrite($handle, "-- PHPost V".Config::app('app.version')." - Database Backup\n");
		fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n");
		fwrite($handle, "-- Database: {$db}\n");
		fwrite($handle, "-- Tables: " . implode(', ', $tables) . "\n\n");
		fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

		// Validar contra tablas reales para evitar inyección o valores inválidos
		$realTables = array_column(
			DB::fetchAll("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()"), 'TABLE_NAME');

		foreach ($tables as $table) {
			$table = $this->sanitizeTableName($table, $realTables);
			// Estructura (SHOW CREATE no soporta prepared statements)
			$createResult = $conn->query("SHOW CREATE TABLE `{$table}`");
			$createRow    = $createResult->fetch(PDO::FETCH_NUM);
			fwrite($handle, "-- Tabla: {$table}\n");
			fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
			fwrite($handle, $createRow[1] . ";\n\n");

			// Datos (SELECT * sin WHERE no necesita params)
			$rows = $conn->query("SELECT * FROM `{$table}`");
			$data = $rows ? $rows->fetchAll(PDO::FETCH_NUM) : [];
			if ($data) {
				fwrite($handle, "INSERT INTO `{$table}` VALUES\n");
				$lines = [];
				foreach ($data as $row) {
					$values = array_map(function ($val) {
						if ($val === null) return 'NULL';
						return Database::escape((string) $val);
					}, $row);
					$lines[] = '(' . implode(', ', $values) . ')';
				}
				fwrite($handle, implode(",\n", $lines) . ";\n\n");
			}
		}

		fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
		fclose($handle);

		return [
			'ok'       => true,
			'filename' => $filename,
			'filepath' => $filepath,
			'size'     => round(filesize($filepath) / 1024, 2),
		];
	}

	/**
	 * Lista los backups guardados en storage/backups/
	 */
	public function listBackups(): array
	{
		$storageBackupPath = TS_BACKUPS;
		if (!is_dir($storageBackupPath)) return [];

		$files = glob(rtrim($storageBackupPath, '/') . '/*.sql') ?: [];
		$backups = [];
		foreach ($files as $file) {
			$backups[] = [
				'filename' => basename($file),
				'size_kb'  => round(filesize($file) / 1024, 2),
				'date'     => date('d/m/Y H:i', filemtime($file)),
				'ts'       => filemtime($file),
			];
		}
		usort($backups, fn($a, $b) => $b['ts'] - $a['ts']);
		return $backups;
	}

	/**
	 * Elimina un backup por nombre de archivo
	 */
	public function deleteBackup(): string|bool
	{
		$filename = trim($_POST['filename'] ?? '');
		$storageBackupPath = TS_BACKUPS;
		// Seguridad: solo nombre de archivo, sin paths
		$filename = basename($filename);
		if (!preg_match('/^backup_[\w\-]+\.sql$/', $filename)) return false;
		$path = rtrim($storageBackupPath, '/') . '/' . $filename;
		return (file_exists($path) && unlink($path)) ? '1: Backup eliminado.' : '0: No se pudo eliminar.';
	}

	/* MANTENIMIENTO */

	/**
	 * OPTIMIZE TABLE en las tablas seleccionadas
	 */
	public function optimizeTables(array $tables): array
	{
		$results = [];
		foreach ($tables as $table) {
			$table = $this->sanitizeTableName($table, $realTables);
			$result = $this->conn()->query("OPTIMIZE TABLE `{$table}`");
			$row    = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
			$results[$table] = $row['Msg_type'] ?? 'error';
		}
		return $results;
	}

	public function repairTables(array $tables): array
	{
		$results = [];
		foreach ($tables as $table) {
			$table = $this->sanitizeTableName($table, $realTables);
			$result = $this->conn()->query("REPAIR TABLE `{$table}`");
			$row    = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
			$results[$table] = $row['Msg_type'] ?? 'error';
		}
		return $results;
	}

	public function checkTables(array $tables): array
	{
		$results = [];
		foreach ($tables as $table) {
			$table = $this->sanitizeTableName($table, $realTables);
			$result = $this->conn()->query("CHECK TABLE `{$table}`");
			$row    = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
			$results[$table] = [
				'status'  => $row['Msg_type'] ?? 'error',
				'message' => $row['Msg_text'] ?? '',
			];
		}
		return $results;
	}

	public function truncateTable(string $table): array
	{
		if (in_array($table, self::PROTECTED_TABLES, true)) {
			return ['ok' => false, 'message' => "La tabla '{$table}' está protegida."];
		}
		$table = $this->sanitizeTableName($table, $realTables);
		$conn  = $this->conn();
		try {
			$conn->exec("SET FOREIGN_KEY_CHECKS=0");
			$conn->exec("TRUNCATE TABLE `{$table}`");
			$conn->exec("SET FOREIGN_KEY_CHECKS=1");
		} catch (PDOException $e) {
			return ['ok' => false, 'message' => $e->getMessage()];
		}
		return ['ok' => true, 'message' => "Tabla '{$table}' vaciada correctamente."];
	}

	/* HELPERS */

	private function getPrefix(string $table): string
	{
		if (preg_match('/^([a-z]+)_/', $table, $m)) {
			return $m[1] . '_';
		}
		return 'otros';
	}
}
