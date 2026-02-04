<?php

/**
 * @name Config.Errors.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once TS_UTILS . '/Logger.php';

define('REPORTING', 
	(Config::app('app.debug_all') ? E_ALL : 
		(Config::app('app.debug') ? (E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : 0)
	)
);

// Reporte de errores
error_reporting(REPORTING);

ini_set('display_errors', Config::app('app.env') === 'development' ? '1' : '0');
ini_set('log_errors', '1');

if(!is_dir(Config::app('paths.logs'))) {
	mkdir(Config::app('paths.logs'), 0777);
}

set_error_handler(function (int $severity, string $message, string $file, int $line) {
   Logger::log('php', 'ERROR', $message, compact('file', 'line', 'severity'));
});

set_exception_handler(function (Throwable $e) {
   Logger::log('php', 'EXCEPTION', $e->getMessage(), [
      'file'  => $e->getFile(),
      'line'  => $e->getLine(),
      'trace' => $e->getTraceAsString(),
   ]);
});

register_shutdown_function(function () {
   $error = error_get_last();
   if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
      Logger::log('php', 'FATAL', $error['message'], $error);
   }
});