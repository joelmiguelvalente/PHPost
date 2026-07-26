<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Migration
 * @author     Miguel92
*/

// Prevenir acceso directo no autorizado
if (!defined('TS_HEADER') && basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
	header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	exit('Access denied');
}

defined('TS_HEADER') OR define('TS_HEADER', TRUE);

// Validar origen de la solicitud
$allowed_hosts = [$_SERVER['HTTP_HOST'], 'localhost', '127.0.0.1'];
$current_host = $_SERVER['HTTP_HOST'] ?? '';

if (!in_array($current_host, $allowed_hosts) && !empty($current_host)) {
	error_log("Intento de acceso no autorizado desde: " . $current_host);
	header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
	exit('Access denied');
}

define('BASEPATH', dirname(__DIR__, 1));

// Validar que las rutas son seguras (no contienen caracteres peligrosos)
$base_path_real = realpath(BASEPATH);
$script_path = realpath(dirname(__FILE__));

if ($base_path_real === false || $script_path === false) {
	exit('Security error');
}

// Asegurarse de que estamos en el directorio correcto
if (strpos($script_path, $base_path_real) !== 0) {
	exit('Security error');
}

// Cargar configuraciones de forma segura
$config_paths_file = BASEPATH . '/config/bootstrap.php';
if (!file_exists($config_paths_file) || !is_readable($config_paths_file)) {
	exit('Configuration not available');
}

require_once $config_paths_file;

// Validar que las constantes de configuración existen y son válidas
$config_files = [
	TS_CONFIG . '/Config.php',
	TS_CONFIG . '/bootstrap.session.php',
	TS_CONFIG . '/Config.Errors.php'
];

foreach ($config_files as $config_file) {
	if (!file_exists($config_file) || !is_readable($config_file)) {
	  error_log("Config file not accessible: " . $config_file);
	  exit('Configuration error');
	}
	require_once $config_file;
}

// Límite de ejecución
set_time_limit(300);

// Validar que TS_EXTRA existe y es seguro
if (!defined('TS_EXTRAS') || !file_exists(TS_EXTRAS) || !is_readable(TS_EXTRAS)) {
	exit('System configuration error');
}

require_once TS_EXTRAS . '/functions.php';

// Validar que la base de datos está disponible antes de hacer consultas
if (!class_exists('DB')) {
	exit('Database connection not available');
}

# Tablas migradas
try {
	$settings = DB::fetch("SELECT url FROM w_configuracion");
	if (!$settings) {
	  error_log("No se encontraron configuraciones en w_configuracion");
	  $settings = ['url' => '']; // Valor por defecto seguro
	}
} catch (Exception $e) {
	error_log("Error al obtener configuración: " . $e->getMessage());
	exit('System configuration error');
}

$time = time();

$urlBase = $settings['url'] ?? '';

// Validar que URL sea segura (prevenir open redirect)
if (!empty($urlBase) && !filter_var($urlBase, FILTER_VALIDATE_URL)) {
	error_log("URL de configuración inválida: " . $urlBase);
	$urlBase = ''; // Valor por defecto seguro
}
