<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Migration
 * @author     Miguel92
*/

require_once dirname(__DIR__, 1) . '/app.php';

header('Content-Type: application/json; charset=UTF-8');

$name = "sessions";
$time = time();

// Verificar si la columna session_ua existe
$check_column = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'u_sessions' AND COLUMN_NAME = 'session_ua' LIMIT 1";

if (DB::numRows($check_column) == 0) {
    // Agregar columnas una por una (sin IF NOT EXISTS)
    DB::query("ALTER TABLE `u_sessions` ADD `session_ua` VARCHAR(255) DEFAULT '' AFTER `session_ip`");
    DB::query("ALTER TABLE `u_sessions` ADD `session_created_at` INT NOT NULL DEFAULT 0 AFTER `session_time`");
    DB::query("ALTER TABLE `u_sessions` ADD `session_last_activity` INT NOT NULL DEFAULT 0 AFTER `session_created_at`");
    DB::query("ALTER TABLE `u_sessions` ADD `session_regenerated_at` INT NOT NULL DEFAULT 0 AFTER `session_last_activity`");

    // Agregar índices
    DB::query("ALTER TABLE `u_sessions` ADD INDEX `session_user_id` (`session_user_id`)");
    DB::query("ALTER TABLE `u_sessions` ADD INDEX `session_time` (`session_time`)");
    DB::query("ALTER TABLE `u_sessions` ADD INDEX `session_last_activity` (`session_last_activity`)");

    if(DB::insert('w_migrations', [ 'migration' => $name, 'executed_at' => $time ])) {
        echo json_encode([
            'success' => true,
            'message' => $name . ' migrado'
        ]);
    }
    exit;
} else {
    // Ya migrado
    echo json_encode(['success' => false, 'message' => $name . ' ya fue migrado']);
    exit;
}
