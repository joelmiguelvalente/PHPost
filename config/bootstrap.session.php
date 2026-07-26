<?php

declare(strict_types=1);

/**
 * @package    Config
 * @author     Miguel92
 * @copyright  2026
 */

$sessionName = Config::app('security.session.name') ?? 'phpost_session';

// Verificar si la sesión ya está activa
if (session_status() === PHP_SESSION_ACTIVE) {
	// La sesión ya está iniciada, solo verificar configuración
	if (session_name() !== $sessionName) {
		session_write_close();
		session_name($sessionName);
		session_start();
	}
} else {
	// Obtener configuración de sesión con valores por defecto seguros
	$secure = Config::app('debug.active') ? Config::app('security.session.secure') : true;
	$sessionConfig = [
		'name' 		=> $sessionName,
		'lifetime' 	=> Config::app('security.session.lifetime') ?? 0,
		'secure' 	=> $secure ?? false,
		'httponly' 	=> Config::app('security.session.httponly'),
		// Ya esta definido en config/Config.Application.php
		'samesite' 	=> Config::app('security.session.samesite'),
		'path' 		=> '/',
		'domain' 	=> null,
		'save_path' => Config::app('security.session.save_path')
	];

	$lifetime = (int)$sessionConfig['lifetime'];
	$gcMax    = (int)(Config::app('security.session.gc_maxlifetime') ?? 1440);
	if ($lifetime > 0 && $gcMax < $lifetime) {
	    ini_set('session.gc_maxlifetime', (string)$lifetime);
	}

	// Configurar parámetros de sesión ANTES de iniciarla
	if (session_status() === PHP_SESSION_NONE) {
		session_name($sessionConfig['name']);

		// Configurar parámetros de cookies de sesión
		session_set_cookie_params([
			'lifetime' 	=> $sessionConfig['lifetime'],
			'path' 		=> $sessionConfig['path'],
			'domain' 	=> $sessionConfig['domain'],
			'secure' 	=> $sessionConfig['secure'],
			'httponly' 	=> $sessionConfig['httponly'],
			'samesite' 	=> $sessionConfig['samesite']
		]);

		// Configurar modo estricto para sesiones
		ini_set('session.use_strict_mode', '1');
		ini_set('session.use_cookies', '1');
		ini_set('session.use_only_cookies', '1');
		ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');

		// Prevenir fijación de sesión
		ini_set('session.use_trans_sid', '0');

		// Configurar handler de sesión personalizado si existe
		if (!empty($sessionConfig['save_path']) && is_dir($sessionConfig['save_path']) && is_writable($sessionConfig['save_path'])) {
			ini_set('session.save_path', $sessionConfig['save_path']);
		}

		// Iniciar sesión
		if (!session_start()) {
			throw new \RuntimeException('No se pudo iniciar la sesión');
		}
	 }
}

// Definir constante para el nombre de sesión
define('SESSION_NAME', session_name());

// Inicializar la sesión con protección contra CSRF y fijación de sesión
if (!isset($_SESSION['__initiated'])) {
    $_SESSION['__initiated']  = true;
    $_SESSION['__created_at'] = time();
} else {
    // Regenerar cada N segundos (ej: 1800 = 30 min)
    $regInterval = Config::app('security.session.regenerate_interval') ?? 1800;
    if (($_SESSION['__created_at'] + $regInterval) < time()) {
        session_regenerate_id(true);
        $_SESSION['__created_at'] = time();
    }
}
