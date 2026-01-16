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

/**
 * Clase de manejo de sesiones
 *
 * NOTA:
 * - Mantiene consultas legacy (db_exec)
 * - Mantiene uso de globals
 * - Mantiene nombres de métodos
 */
class tsSession {

	/** @var string ID de la sesión */
	public string $ID = '';

	/** @var array|null Datos completos de la sesión */
	public $userdata = null;

	/** @var string IP del usuario */
	public string $ip_address = '';

	/** @var int Timestamp actual */
	public int $time_now;

	/** @var mixed Conexión DB (legacy) */
	public $db;

	/** @var int Tiempo máximo de vida de la sesión */
	private int $sess_expiration = 7200;

	/** @var bool Requerir coincidencia de IP */
	private bool $sess_match_ip = false;

	/** @var int Intervalo de actualización de actividad */
	private int $sess_time_online = 300;

	/** @var string Prefijo de cookies */
	private string $cookie_prefix = 'pp_';

	/** @var string Nombre base de la cookie */
	private string $cookie_name = '';

	/** @var string Path de la cookie */
	private string $cookie_path = '/';

	/** @var string Dominio de la cookie */
	private string $cookie_domain = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		global $tsCore;

		// Timestamp actual
		$this->time_now = time();

		// Resolver dominio base para cookies
		$hostData = parse_url($tsCore->route('url'));
		$host = strtolower(str_replace('www.', '', $hostData['host'] ?? ''));

		$this->cookie_domain = ($host === 'localhost' || empty($host)) ? '' : '.' . $host;
		$this->cookie_name   = $this->cookie_prefix . substr(md5($host), 0, 6);

		// IP del usuario
		$this->ip_address = (string)$tsCore->getIP();

		// Configuración: validar IP
		$this->sess_match_ip = !empty($tsCore->settings['c_allow_sess_ip']);

		// Configuración: intervalo de actividad
		if (!empty($tsCore->settings['c_last_active'])) {
			$this->sess_time_online = (int)$tsCore->settings['c_last_active'] * 60;
		}
	}

	/**
	 * Leer sesión activa desde cookie y base de datos
	 *
	 * @return bool
	 */
	public function read(): bool {
		$cookieKey = $this->cookie_name . '_sid';
		if (empty($_COOKIE[$cookieKey])) {
			return false;
		}

		$this->ID = (string)$_COOKIE[$cookieKey];

		// Validación básica del ID
		if (strlen($this->ID) !== 32) {
			return false;
		}

		// Obtener sesión desde DB
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_sessions WHERE session_id = \'' . $this->ID . '\'');

		$session = db_exec('fetch_assoc', $query);

		// No existe en DB
		if (empty($session['session_id'])) {
			$this->destroy();
			return false;
		}

		// Sesión expirada (si no es autologin)
		if (
			($session['session_time'] + $this->sess_expiration) < $this->time_now
			&& empty($session['session_autologin'])
		) {
			$this->destroy();
			return false;
		}

		// Cambio de IP
		if ($this->sess_match_ip && $session['session_ip'] !== $this->ip_address) {
			$this->destroy();
			return false;
		}

		// Sesión válida
		$this->userdata = $session;

		return true;
	}

	/**
	 * Crear una nueva sesión
	 *
	 * @return void
	 */
	public function create(): void {
		$this->ID = $this->gen_session_id();

		// Crear sesión base (usuario 0)
		db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_sessions (session_id, session_user_id, session_ip, session_time) VALUES (\'' . $this->ID . '\', \'0\', \'' . $this->ip_address . '\', \'' . $this->time_now . '\')');
		// Cookie de sesión
		$this->set_cookie('sid', $this->ID, $this->sess_expiration);
	}

	/**
	 * Actualizar sesión existente
	 *
	 * @param int  $user_id
	 * @param bool $autologin
	 * @param bool $force_update
	 */
	public function update($user_id = 0, $autologin = false, $force_update = false): void {
		if (empty($this->userdata)) {
			return;
		}

		// Evitar updates innecesarios
		if (
			($this->userdata['session_time'] + $this->sess_time_online) >= $this->time_now
			&& !$force_update
		) {
			return;
		}

		// Preparar datos
		$this->userdata['session_user_id'] = $user_id ?: $this->userdata['session_user_id'];
		$this->userdata['session_ip']      = $this->ip_address;
		$this->userdata['session_time']    = $this->time_now;

		$autologin = $autologin ? 1 : 0;
		$this->userdata['session_autologin'] = $autologin;

		// Actualizar DB
		db_exec([__FILE__, __LINE__], 'query',
			"UPDATE u_sessions SET
				session_user_id = '{$this->userdata['session_user_id']}',
				session_ip = '{$this->userdata['session_ip']}',
				session_time = '{$this->userdata['session_time']}',
				session_autologin = $autologin
			 WHERE session_id = '{$this->ID}'"
		);

		// Limpieza ocasional
		$this->sess_gc();

		// Expiración cookie
		$expiration = !empty($this->userdata['session_autologin'])
			? 31500000 // ~1 año
			: $this->sess_expiration;

		$this->set_cookie('sid', $this->ID, $expiration);
	}

	/**
	 * Destruir sesión actual
	 *
	 * @return void
	 */
	public function destroy(): void {
		if (!empty($this->ID)) {
			db_exec([__FILE__, __LINE__], 'query',
				'DELETE FROM u_sessions WHERE session_id = \'' . $this->ID . '\''
			);
		}

		// Expirar cookie
		$this->set_cookie('sid', '', -31500000);
	}

	/**
	 * Crear / actualizar cookie
	 *
	 * @param string $name
	 * @param string $cookiedata
	 * @param int    $cookietime
	 */
	public function set_cookie($name, $cookiedata, $cookietime): void {
		$cookiename = rawurlencode($this->cookie_name . '_' . $name);
		$cookiedata = rawurlencode((string)$cookiedata);

		setcookie(
			$cookiename,
			$cookiedata,
			$this->time_now + (int)$cookietime,
			$this->cookie_path,
			$this->cookie_domain
		);
	}

	/**
	 * Generar ID de sesión
	 *
	 * @return string
	 */
	public function gen_session_id(): string {
		$sessid = '';
		while (strlen($sessid) < 32) {
			$sessid .= mt_rand(0, mt_getrandmax());
		}
		// Mezclar con IP para mayor entropía
		$sessid .= $this->ip_address;
		return md5(uniqid($sessid, true));
	}

	/**
	 * Garbage Collector de sesiones
	 *
	 * @return void
	 */
	public function sess_gc(): void {
		// Ejecutar solo aleatoriamente (~30%)
		if ((rand() % 100) >= 30) {
			return;
		}
		$expire = $this->time_now - $this->sess_time_online;
		db_exec([__FILE__, __LINE__], 'query',
			'DELETE FROM u_sessions
			 WHERE session_time < ' . $expire . '
			 AND session_autologin = \'0\''
		);
	}
}