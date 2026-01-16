<?php

/**
 * @name c.session.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
    exit('No se permite el acceso directo al script');
}

final class Database
{
    private static ?self $instance = null;
    private mysqli $connection;

    private function __construct()
    {
        $this->connect();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function connect(): void
    {
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

    /* ================= Legacy-safe API ================= */

    public function rawQuery(string $sql): mysqli_result|bool
    {
        return $this->connection->query($sql);
    }

    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }

    public function insertId(): int
    {
        return $this->connection->insert_id;
    }

    public function error(): string
    {
        return $this->connection->error;
    }

    /* ================= Modern API ================= */

    public function prepare(string $sql): mysqli_stmt
    {
        return $this->connection->prepare($sql);
    }

    public function fetchAll(mysqli_result $result): array
    {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetch(mysqli_result $result): ?array
    {
        return $result->fetch_assoc() ?: null;
    }

    public function numRows(mysqli_result $result): int
    {
        return $result->num_rows;
    }

    /* ================= Transactions ================= */

    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /* ================= Low-level access ================= */

    public function query(string $sql): mysqli_result|bool
    {
        return $this->connection->query($sql);
    }

}