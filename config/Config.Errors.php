<?php

declare(strict_types=1);

/**
 * @package    Config
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once TS_LOGGER . '/LogLevel.php';
require_once TS_LOGGER . '/LoggerFormatter.php';
require_once TS_LOGGER . '/LogParser.php';
require_once TS_LOGGER . '/Logger.php';

$level = match(Config::app('debug.level')) {
   1 => E_ALL,
   2 => E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED,
   default => 0
};

// Reporte de errores
error_reporting($level);

$__DebugActive = Config::app('debug.active') ? '1' : '0';
$__DebugLogs = Config::app('debug.logs') === 'always';

ini_set('display_errors',           $__DebugActive);
ini_set('display_startup_errors',   $__DebugActive);
ini_set('log_errors',   $__DebugLogs);
ini_set('html_errors',  $__DebugLogs);

set_error_handler(function (int $severity, string $message, string $file, int $line) {
   if (!(error_reporting() & $severity)) {
      return false;
   }
   Logger::log($message, LogLevel::ERROR, compact('file', 'line', 'severity'), 'php' );
   return !Config::app('debug.active');
});

set_exception_handler(function (Throwable $e) {
   Logger::exception($e);
   if (Config::app('debug.active')) {
      echo "<pre>{$e}</pre>";
   }
});

register_shutdown_function(function () {
   $error = error_get_last();
   if (!$error) {
      return;
   }
   if (in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
      Logger::log($error['message'], LogLevel::FATAL, $error, 'php');
      if (Config::app('debug.active')) {
         echo "<pre>" . print_r($error, true) . "</pre>";
      }
   }
});
