<?php

/**
 * ------------------------------------------------------------
 * PHPost Connection File
 * ------------------------------------------------------------
 *
 * @name      connection.php
 * @author    PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

final class InstallerDB {

    private mysqli $db;

    public function __construct(string $host, string $user, string $pass, string $name) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->db = new mysqli($host, $user, $pass, $name);
        $this->db->set_charset('utf8mb4');
    }

    /* =========================
     * QUERY
     * ========================= */
    private function query(string $sql, array $params): mysqli_stmt {
        $stmt = $this->db->prepare($sql);

        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    /* =========================
     * EXEC SQL (DDL / INSTALL)
     * ========================= */
    public function execute(string $sql): bool {
       // Solo permitimos instrucciones típicas de instalación
       if (!preg_match('/^\s*(CREATE|ALTER|DROP|INSERT|TRUNCATE)\s+/i', $sql)) {
          throw new InvalidArgumentException('SQL no permitido en el instalador');
       }

       return (bool) $this->db->query($sql);
    }

    /* =========================
     * DROP TABLE
     * ========================= */
    public function dropTable(string $table): void {
       // Seguridad mínima: solo letras, números y _
       if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
          throw new InvalidArgumentException('Nombre de tabla inválido');
       }

       $sql = "DROP TABLE IF EXISTS `{$table}`";
       $this->db->query($sql);
    }

    /* =========================
     * SELECT
     * ========================= */
    public function select(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function selectOne(string $sql, array $params = []): ?array {
        $result = $this->select($sql, $params);
        return $result[0] ?? null;
    }

    /* =========================
     * INSERT
     * ========================= */
    public function insert(string $table, array $data): int {
        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(',', $columns),
            $placeholders
        );

        $stmt = $this->query($sql, array_values($data));

        return $stmt->insert_id;
    }

    /* =========================
     * UPDATE
     * ========================= */
    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);

        return $stmt->affected_rows;
    }

    public function numRows(string $sql, array $params = []): int {
        $stmt = $this->query($sql, $params);
        return $stmt->get_result()->num_rows;
    }

    /* =========================
     * EXISTS
     * ========================= */
    public function exists(string $sql, array $params = []): bool
    {
       return $this->numRows($sql, $params) > 0;
    }

}
