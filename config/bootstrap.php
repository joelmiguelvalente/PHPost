<?php

// bootstrap.php
declare(strict_types=1);

/**
 * @package    Config
 * @author     Miguel92
 * @copyright  2026
*/

define('SOURCEPATH', BASEPATH . '/src');

// Definir paths sin verificar existencia (solo estructura)
define('TS_ROOT',    BASEPATH);
define('TS_SOURCES', SOURCEPATH);
// ./*
define('TS_ASSETS',  BASEPATH . '/assets');
define('TS_THEMES',  BASEPATH . '/themes');
define('TS_VIEWS',   BASEPATH . '/views');
define('TS_CONFIG',  BASEPATH . '/config');
define('TS_STORAGE', BASEPATH . '/storage');
// ./src/*
// Crear automáticamente las constantes de ./src/*
foreach (scandir(SOURCEPATH) as $path) {
    if ($path === '.' || $path === '..') continue;
    $dir = SOURCEPATH . '/' . $path;
    if (is_dir($dir)) {
        define('TS_' . strtoupper($path), $dir);
    }
}

// ./storage/*
define('TS_BACKUPS', TS_STORAGE . '/backups');

// Crear automáticamente las constantes de ./src/Utils/*
if (is_dir(TS_UTILS)) {
    foreach (scandir(TS_UTILS) as $path) {
        if ($path === '.' || $path === '..') continue;
        $dir = TS_UTILS . '/' . $path;
        if (is_dir($dir)) {
            define('TS_' . strtoupper($path), $dir);
        }
    }
}

// Directorios donde buscar clases
$directorios = [];
foreach (get_defined_constants(true)['user'] as $const => $dir) {
    if (
        !str_starts_with($const, 'TS_') ||
        !is_string($dir) ||
        !is_dir($dir) ||
        in_array(basename($dir), ['Api', 'Php'], true)
    ) {
        continue;
    }
    $directorios[basename($dir)] = $dir;
}

// Autoloader
spl_autoload_register(function (string $className) use ($directorios): bool {
    foreach ($directorios as $nombre => $dir) {
        // Compatibilidad con c.*.php
        if ($nombre === 'Class' && str_starts_with($className, 'ts')) {
            $file = $dir . '/c.' . strtolower(substr($className, 2)) . '.php';
            if (is_file($file)) {
                require_once $file;
                return true;
            }
        }
        $file = $dir . '/' . $className . '.php';

        if (is_file($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// Cargar configuración SOLO si los archivos existen
$configDir = TS_CONFIG;
if (is_dir($configDir)) {
    $configs = ['Config.php', 'bootstrap.session.php', 'Config.Errors.php'];
    foreach ($configs as $config) {
        $configFile = $configDir . '/' . $config;
        if (file_exists($configFile)) {
            require_once $configFile;
        }
    }
}

// Configuración global (segura)
if (function_exists('date_default_timezone_set') && class_exists('Config')) {
    try {
        $timezone = Config::app('localization.timezone') ?? 'UTC';
        date_default_timezone_set($timezone);
    } catch (Throwable $e) {
        date_default_timezone_set('UTC');
    }
}

if (function_exists('set_time_limit')) {
    set_time_limit(300);
}

# Si no usas PHP 8.5 se ejecuta el archivo
require_once TS_EXTRAS . '/polyfill.uri.php';
