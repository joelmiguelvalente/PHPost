<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
 * @copyright  2026
*/

final class Connection {

    private array $dbConfig = [];

    private ?PDO $pdo = null;

    private bool $connected = false;

    private ?string $lastError = null;

    private bool $inTransaction = false;

    private const REQUIRED = ['hostname', 'username', 'password', 'database'];

    private const FINDER = ['dbhost', 'dbuser', 'dbpass', 'dbname'];

    private const TIMEOUT = 5;

    public function __construct(array $dbConfig = [])
    {
        if (!empty($dbConfig)) {
            $this->validate($dbConfig);
            $this->dbConfig = $dbConfig;
        }
    }

    private function validate(array $dbConfig): void
    {
        foreach (self::REQUIRED as $key) {
            if (!array_key_exists($key, $dbConfig)) {
                throw new InvalidArgumentException("Falta el parámetro '{$key}'.");
            }
        }
    }

    public function getFileConfig(): string
    {
        $suffix = Config::app('app.development') ? '.local' : '';
        return dirname(__DIR__, 2) . "/config/Config.Database{$suffix}.php";
    }

    public function saveData(): bool
    {
        $file = $this->getFileConfig();

        if (!file_exists($file)) {
            $this->lastError = "Archivo de configuración no encontrado: {$file}";
            return false;
        }

        if (!is_writable($file)) {
            $this->lastError = "El archivo de configuración no tiene permisos de escritura.";
            return false;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            $this->lastError = "No se pudo leer el archivo de configuración.";
            return false;
        }

        $replace = str_replace(self::FINDER, $this->dbConfig, $content);

        if (file_put_contents($file, $replace) === false) {
            $this->lastError = "No se pudo guardar la configuración.";
            return false;
        }

        return true;
    }

    public function check(?array $dbConfig = null): bool
    {
        $config = $dbConfig ?? $this->dbConfig;

        if (empty($config)) {
            $this->lastError = "No hay datos de conexión.";
            return false;
        }

        try {
            $this->validate($config);

            $driver = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4;connect_timeout=%d',
                $config['hostname'],
                $config['database'],
                self::TIMEOUT
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => self::TIMEOUT,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $pdo = new PDO($driver, $config['username'], $config['password'], $options);
            $pdo = null;

            return true;

        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function connect(): bool
    {
        if ($this->connected && $this->pdo !== null) {
            return true;
        }

        if (empty($this->dbConfig)) {
            $this->lastError = "No hay datos de conexión.";
            return false;
        }

        try {
            $this->validate($this->dbConfig);

            $driver = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4;connect_timeout=%d',
                $this->dbConfig['hostname'],
                $this->dbConfig['database'],
                self::TIMEOUT
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => self::TIMEOUT,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $this->pdo = new PDO($driver, $this->dbConfig['username'], $this->dbConfig['password'], $options);
            $this->connected = true;
            $this->inTransaction = false;

            return true;

        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            $this->connected = false;
            return false;
        }
    }

    public function close(): void
    {
        if ($this->inTransaction) {
            try {
                $this->rollBack();
            } catch (Throwable $e) {
                // Ignorar errores al cerrar
            }
        }
        $this->pdo = null;
        $this->connected = false;
        $this->inTransaction = false;
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->pdo !== null;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    // ============================================
    // TRANSACCIONES CON ESTADO
    // ============================================

    public function beginTransaction(): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = "No hay conexión a la base de datos.";
            return false;
        }

        if ($this->inTransaction) {
            $this->lastError = "Ya hay una transacción activa.";
            return false;
        }

        try {
            $result = $this->pdo->beginTransaction();
            if ($result) {
                $this->inTransaction = true;
            }
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function commit(): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = "No hay conexión a la base de datos.";
            return false;
        }

        if (!$this->inTransaction) {
            $this->lastError = "No hay una transacción activa para confirmar.";
            return false;
        }

        try {
            $result = $this->pdo->commit();
            if ($result) {
                $this->inTransaction = false;
            }
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function rollBack(): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = "No hay conexión a la base de datos.";
            return false;
        }

        if (!$this->inTransaction) {
            $this->lastError = "No hay una transacción activa para revertir.";
            return false;
        }

        try {
            $result = $this->pdo->rollBack();
            if ($result) {
                $this->inTransaction = false;
            }
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    // ============================================
    // QUERY HELPERS
    // ============================================

    private function executeQuery(string $sql, array $params = []): PDOStatement
    {
        if (!$this->isConnected()) {
            throw new RuntimeException("No hay conexión a la base de datos.");
        }

        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $key => $value) {
                $type = match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_bool($value) => PDO::PARAM_BOOL,
                    is_null($value) => PDO::PARAM_NULL,
                    default => PDO::PARAM_STR,
                };
                $stmt->bindValue(":{$key}", $value, $type);
            }

            $stmt->execute();
            return $stmt;

        } catch (PDOException $e) {
            throw new RuntimeException("Error en consulta: " . $e->getMessage());
        }
    }

    private function buildWhere(array $conditions, string $operator = 'AND'): array
    {
        if (empty($conditions)) {
            return ['', []];
        }

        $sql = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $placeholder = "w_{$column}";
            $sql[] = "{$column} = :{$placeholder}";
            $params[$placeholder] = $value;
        }

        return ["WHERE " . implode(" {$operator} ", $sql), $params];
    }

    public function select(string $table, $columns = '*', array $conditions = [], array $options = []): array
    {
        $columnStr = is_array($columns) ? implode(', ', $columns) : $columns;
        $sql = "SELECT {$columnStr} FROM {$table}";

        $params = [];
        if (!empty($conditions)) {
            $operator = $options['operator'] ?? 'AND';
            [$whereSql, $whereParams] = $this->buildWhere($conditions, $operator);
            $sql .= " {$whereSql}";
            $params = $whereParams;
        }

        if (isset($options['order']) && !empty($options['order'])) {
            $sql .= " ORDER BY {$options['order']}";
        }

        if (isset($options['limit']) && is_numeric($options['limit'])) {
            $sql .= " LIMIT " . (int)$options['limit'];
        }

        return $this->executeQuery($sql, $params)->fetchAll();
    }

    public function selectOne(string $table, $columns = '*', array $conditions = [], array $options = []): ?array
    {
        $options['limit'] = 1;
        $result = $this->select($table, $columns, $conditions, $options);
        return $result[0] ?? null;
    }

    public function insert(string $table, array $data)
    {
        if (empty($data)) {
            throw new InvalidArgumentException("No hay datos para insertar.");
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $this->executeQuery($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $conditions, array $options = []): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("No hay datos para actualizar.");
        }

        if (empty($conditions)) {
            throw new InvalidArgumentException("Se requieren condiciones para actualizar.");
        }

        $params = [];
        $set = [];
        foreach ($data as $col => $val) {
            $placeholder = "s_{$col}";
            $set[] = "{$col} = :{$placeholder}";
            $params[$placeholder] = $val;
        }

        $operator = $options['operator'] ?? 'AND';
        [$whereSql, $whereParams] = $this->buildWhere($conditions, $operator);
        $params = array_merge($params, $whereParams);

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " {$whereSql}";

        return $this->executeQuery($sql, $params)->rowCount();
    }

    public function delete(string $table, array $conditions, array $options = []): int
    {
        if (empty($conditions)) {
            throw new InvalidArgumentException("Se requieren condiciones para eliminar.");
        }

        $operator = $options['operator'] ?? 'AND';
        [$whereSql, $params] = $this->buildWhere($conditions, $operator);

        $sql = "DELETE FROM {$table} {$whereSql}";

        return $this->executeQuery($sql, $params)->rowCount();
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        return $this->executeQuery($sql, $params);
    }

    public function exec(string $sql): int|false
    {
        if (!$this->isConnected()) {
            throw new RuntimeException('No hay conexión.');
        }

        return $this->pdo->exec($sql);
    }

    public function getPDO(): ?PDO
    {
        return $this->pdo;
    }
}
