<?php

declare(strict_types=1);

/**
 * @package    Config
 * @author     Miguel92
 * @copyright  2026
 */

require_once TS_LOGGER . '/LogLevel.php';
require_once TS_LOGGER . '/LoggerFormatter.php';
require_once TS_LOGGER . '/LogParser.php';
require_once TS_LOGGER . '/Logger.php';

// Configuración de errores
try {
	$debugActive = Config::app('debug.active') ?? false;
	$debugLevel = Config::app('debug.level.development') ?? E_ALL;
	$debugLogs = Config::app('debug.logs') ?? 'always';
} catch (\Throwable $e) {
	// Fallback seguro si no se puede obtener la configuración
	$debugActive = false;
	$debugLevel = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED;
	$debugLogs = 'always';
}

// Configurar reporte de errores
error_reporting($debugActive ? $debugLevel : 0);

// Configurar display de errores según entorno
$displayErrors = $debugActive ? '1' : '0';
$logErrors = ($debugLogs === 'always' || $debugLogs === true) ? '1' : '0';

ini_set('display_errors', $displayErrors);
ini_set('display_startup_errors', $displayErrors);
ini_set('log_errors', $logErrors);
ini_set('html_errors', $logErrors);

// Configurar log de errores de PHP
if ($logErrors === '1') {
	$path = Config::app('paths.logs.full_path');
	if (is_writable($path)) {
		ini_set('error_log', $path . '/php_errors.log');
	}
}

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
	global $debugActive;
	// Verificar si el error debe ser reportado según el nivel de error_reporting
	if (!(error_reporting() & $severity)) {
		return false;
	}

	// Mapear severidad a nivel de log
	$logLevel = match ($severity) {
		E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE => LogLevel::FATAL,
		E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => LogLevel::WARNING,
		E_NOTICE => LogLevel::NOTICE,
		E_DEPRECATED, E_USER_DEPRECATED => LogLevel::DEBUG,
		default => LogLevel::ERROR
	};

	$context = [
		'file' => $file,
		'line' => $line,
		'severity' => $severity,
		'severity_name' => errorSeverityToString($severity)
	];
	try {
		if (class_exists(Logger::class) && !defined('LOGGER_FALLBACK')) {
			Logger::log($message, $logLevel, $context, 'php');
		} elseif ($logErrors === '1') {
			// Fallback: escribir en el log de errores de PHP
			error_log(sprintf("[%s] %s: %s in %s on line %d", date('Y-m-d H:i:s'), $logLevel, $message, $file, $line));
		}
	} catch (\Throwable $e) {
		// Si el logger falla, no detener la ejecución
		if ($debugActive) {
			error_log("Logger fallback: " . $e->getMessage());
		}
	}

	// En producción, no mostrar errores al usuario
	return !$debugActive;
});

set_exception_handler(function (Throwable $e) use ($debugActive, $logErrors): void {
	$context = [
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'trace' => $e->getTraceAsString(),
		'code' => $e->getCode()
	];

	try {
		if (class_exists(Logger::class) && !defined('LOGGER_FALLBACK')) {
			Logger::exception($e);
		} elseif ($logErrors === '1') {
			error_log(sprintf("[%s] EXCEPTION: %s (%s) in %s on line %d\n%s", date('Y-m-d H:i:s'), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString()));
		}
	} catch (\Throwable $loggerError) {
		// Si el logger falla, al menos guardar en el log de PHP
		error_log("EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
	}
	if (!headers_sent()) {
		http_response_code(500);
	}
	// Mostrar error al usuario solo en modo debug
	if ($debugActive) {
		$title   = 'Error no capturado';
		$message = $e->getMessage();
		$file    = $e->getFile();
		$line    = $e->getLine();
		$trace   = $e->getTraceAsString();
		$code    = $e->getCode();
		require TS_VIEWS . '/error/debug.php';
	} else {
		require TS_VIEWS . '/error/500.html';
	}
});

/**
 * Función auxiliar: Convierte constante de error a string legible
 */
function errorSeverityToString(int $severity): string
{
	return match ($severity) {
		E_ERROR => 'E_ERROR',
		E_WARNING => 'E_WARNING',
		E_PARSE => 'E_PARSE',
		E_NOTICE => 'E_NOTICE',
		E_CORE_ERROR => 'E_CORE_ERROR',
		E_CORE_WARNING => 'E_CORE_WARNING',
		E_COMPILE_ERROR => 'E_COMPILE_ERROR',
		E_COMPILE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE',
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
		E_DEPRECATED => 'E_DEPRECATED',
		E_USER_DEPRECATED => 'E_USER_DEPRECATED',
		default => 'UNKNOWN'
	};
}

/**
 * Registra shutdown function para capturar errores fatales
 */
register_shutdown_function(function () use ($debugActive, $logErrors): void {
	$error = error_get_last();

	if (!$error) {
		return;
	}

	// Solo capturar errores fatales
	$fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
	if (!in_array($error['type'], $fatalTypes, true)) {
		return;
	}

	$context = [
		'file' => $error['file'],
		'line' => $error['line'],
		'type' => $error['type'],
		'type_name' => errorSeverityToString($error['type'])
	];

	try {
		if (class_exists(Logger::class) && !defined('LOGGER_FALLBACK')) {
			Logger::log($error['message'], LogLevel::FATAL, $context, 'php');
		} elseif ($logErrors === '1') {
			error_log(sprintf("[%s] FATAL: %s (%s) in %s on line %d", date('Y-m-d H:i:s'), $error['message'], $error['type_name'], $error['file'], $error['line']));
		}
	} catch (\Throwable $e) {
		error_log("FATAL ERROR: " . $error['message']);
	}

	// En producción, mostrar error 500
	if (!$debugActive && !headers_sent()) {
		http_response_code(500);
		require TS_VIEWS . '/error/500.html';
	}
});

// Configuración adicional: Control de excepciones para funciones obsoletas
if (function_exists('set_magic_quotes_runtime')) {
	@set_magic_quotes_runtime(0);
}

// Si está en modo debug, mostrar los errores capturados
if ($debugActive) {
	// Configurar para mostrar errores en la salida
	ini_set('html_errors', '1');
	ini_set('docref_root', 'https://www.php.net/manual/en/');
} else {
	// En producción, ocultar toda la información sensible
	ini_set('expose_php', '0');
}
