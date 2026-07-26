<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

/**
 * Clase de manejo de sesiones
 */
class tsSession {

	/** @var string ID de la sesión */
	public string $ID = '';

	/** @var array|null Datos completos de la sesión */
	public ?array $userdata = null;

	/** @var string IP del usuario (binario para BD) */
	public string $ip_binary = '';

	/** @var string IP legible para logs */
	public string $ip_human = '';

	/** @var string User Agent del usuario */
	private string $user_agent = '';

	/** @var int Timestamp actual */
	public int $time_now;

	/** @var int Tiempo máximo de vida de la sesión */
	private int $sess_expiration = 7200;

	/** @var bool Requerir coincidencia de IP */
	private bool $sess_match_ip = false;

	/** @var bool Requerir coincidencia de User Agent */
	private bool $sess_match_ua = true;

	/** @var int Intervalo de actualización de actividad */
	private int $sess_time_online = 300;

	/** @var string Prefijo de cookies */
	private string $cookie_prefix = 'cookie_set_';
	private string $set_cookie_name = 'sid';

	/** @var string Nombre base de la cookie */
	private string $cookie_name = '';

	/** @var string Path de la cookie */
	private string $cookie_path = '/';

	/** @var string Dominio de la cookie */
	private string $cookie_domain = '';

	/** @var bool Cookie segura */
	private bool $cookie_secure = false;

	/** @var bool Cookie HttpOnly */
	private bool $cookie_httponly = true;

	/** @var string SameSite policy */
	private string $cookie_samesite;

	/** @var IP Instancia de IP handler */

	/**
	 * Constructor
	 */
	public function __construct(
		protected tsCore $Core,
		protected IP $IP,
		protected Routes $Routes
	) {
		$this->time_now = time();

		// Inicializar IP handler
		$this->ip_human = $this->IP->getIP();
		$this->ip_binary = $this->IP->getIPBinary() ?? '';

		// User Agent
		$this->user_agent = $this->validateUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '');

		// Resolver dominio base para cookies
		$hostData = parse_url($this->Routes->route('url'));
		$host = strtolower(str_replace('www.', '', $hostData['host'] ?? ''));
		$this->cookie_domain = ($host === 'localhost' || empty($host)) ? '' : '.' . $host;
		$this->cookie_name = $this->cookie_prefix . substr(md5($host), 0, 6);

		// Configuraciones
		$this->sess_match_ip = (int)($Core->settings['c_allow_sess_ip'] ?? 0) === 1;
		$this->sess_match_ua = (int)($Core->settings['c_allow_sess_ua'] ?? 1) === 1;

		if (!empty($Core->settings['c_last_active'])) {
			$this->sess_time_online = max(60, (int)$Core->settings['c_last_active'] * 60);
		}

		if (!empty($Core->settings['c_session_expiration'])) {
			$this->sess_expiration = max(3600, (int)$Core->settings['c_session_expiration']);
		}

		// Detectar HTTPS
		$this->cookie_secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
		// Ya esta definido en config/Config.Application.php
		$this->cookie_samesite = Config::app('security.session.samesite');
	}

	/**
	 * Leer sesión activa desde cookie y base de datos
	 */
	public function read(): bool {
		$cookieKey = $this->cookie_name . '_' . $this->set_cookie_name;

		if (empty($_COOKIE[$cookieKey])) {
			return false;
		}

		$this->ID = (string)$_COOKIE[$cookieKey];

		// Validar formato (CHAR 64)
		if (!preg_match('/^[a-f0-9]{64}$/i', $this->ID)) {
			Logger::warning('Invalid session ID format', ['session_id' => substr($this->ID, 0, 10)]);
			$this->destroy();
			return false;
		}

		$session = DB::fetch("SELECT * FROM u_sessions WHERE session_id = :session", ['session' => $this->ID]);

		if (!$session || empty($session['session_id'])) {
			$this->destroy();
			return false;
		}

		$session_time = (int)($session['session_time'] ?? 0);

		// Verificar expiración
		if (($session_time + $this->sess_expiration) < $this->time_now && empty($session['session_autologin'])) {
			Logger::info('Session expired', ['user_id' => $session['session_user_id']]);
			$this->destroy();
			return false;
		}

		// Verificar IP si está habilitado
		if ($this->sess_match_ip && !empty($this->ip_binary)) {
			$stored_ip = $session['session_ip'] ?? '';
			if ($stored_ip !== $this->ip_binary) {
				Logger::warning('IP mismatch', [
					'user_id' => $session['session_user_id'],
					'stored_ip' => $this->IP->binaryToIp($stored_ip),
					'current_ip' => $this->ip_human
				]);
				$this->destroy();
				return false;
			}
		}

		// Verificar User Agent
		if ($this->sess_match_ua && !empty($session['session_ua'])) {
			if ($session['session_ua'] !== $this->user_agent) {
				Logger::warning('User Agent mismatch', ['user_id' => $session['session_user_id']]);
				$this->destroy();
				return false;
			}
		}

		$this->userdata = $session;

		// Regenerar ID periódicamente (cada 30 min)
		$this->regenerateIdIfNeeded();

		return true;
	}

	/**
	 * Crear una nueva sesión
	 */
	public function create(): string {
		$this->ID = $this->generateSessionId();

		DB::insert('u_sessions', [
			'session_id' => $this->ID,
			'session_user_id' => 0,
			'session_ip' => $this->ip_binary,
			'session_ua' => $this->user_agent,
			'session_time' => $this->time_now,
			'session_created_at' => $this->time_now,
			'session_last_activity' => $this->time_now,
			'session_autologin' => 0,
			'session_token' => ''
		]);

		$this->setSecureCookie($this->set_cookie_name, $this->ID, $this->sess_expiration);

		return $this->ID;
	}

	/**
	 * Actualizar sesión existente
	 */
	public function update(int $user_id = 0, bool $autologin = false, bool $force_update = false): bool {
		if (empty($this->userdata)) {
			return false;
		}

		$last_activity = (int)($this->userdata['session_last_activity'] ?? $this->userdata['session_time']);

		if (($last_activity + $this->sess_time_online) >= $this->time_now && !$force_update) {
			return false;
		}

		$new_user_id = $user_id ?: (int)($this->userdata['session_user_id'] ?? 0);
		$autoLogin = $autologin ? 1 : 0;

		DB::update('u_sessions', [
			'session_user_id' => $new_user_id,
			'session_ip' => $this->ip_binary,
			'session_ua' => $this->user_agent,
			'session_time' => $this->time_now,
			'session_last_activity' => $this->time_now,
			'session_autologin' => $autoLogin
		], 'session_id = :id', ['id' => $this->ID]);

		// Actualizar userdata local
		$this->userdata['session_user_id'] = $new_user_id;
		$this->userdata['session_ip'] = $this->ip_binary;
		$this->userdata['session_ua'] = $this->user_agent;
		$this->userdata['session_time'] = $this->time_now;
		$this->userdata['session_last_activity'] = $this->time_now;
		$this->userdata['session_autologin'] = $autoLogin;

		$this->sess_gc();

		// Renovar cookie
		$expiration = ($autoLogin === 1) ? 31536000 : $this->sess_expiration;
		$this->setSecureCookie($this->set_cookie_name, $this->ID, $expiration);

		return true;
	}

	/**
	 * Destruir sesión actual
	 */
	public function destroy(): void {
		if (!empty($this->ID)) {
			DB::delete('u_sessions', 'session_id = :id', ['id' => $this->ID]);
		}

		$this->expireCookie();
		$this->ID = '';
		$this->userdata = null;
	}

	/**
	 * Regenerar ID de sesión (prevenir fijación)
	 */
	public function regenerateId(): bool {
		if (empty($this->userdata)) {
			return false;
		}

		$old_id = $this->ID;
		$new_id = $this->generateSessionId();

		DB::update('u_sessions', [
			'session_id' => $new_id,
			'session_regenerated_at' => $this->time_now
		], 'session_id = :old_id', ['old_id' => $old_id]);

		$this->ID = $new_id;
		$expiration = ($this->userdata['session_autologin'] === 1) ? 31536000 : $this->sess_expiration;
		$this->setSecureCookie($this->set_cookie_name, $new_id, $expiration);
		$this->userdata['session_id'] = $new_id;

		return true;
	}

	/**
	 * Verificar si la sesión es válida
	 */
	public function isValid(): bool {
		if (empty($this->userdata)) {
			return false;
		}

		$session_time = (int)($this->userdata['session_time'] ?? 0);

		if (($session_time + $this->sess_expiration) < $this->time_now && empty($this->userdata['session_autologin'])) {
			return false;
		}

		return true;
	}

	/**
	 * Generar ID de sesión seguro
	 */
	private function generateSessionId(): string {
		try {
			// Lo duplica, así que es de 64
			return bin2hex(random_bytes(32));
		} catch (\Throwable $e) {
			Logger::warning('[Session] random_bytes no disponible', ['message' => $e->getMessage()]);
			throw new \RuntimeException('No se puede generar un ID de sesión seguro.');
		}
	}

	/**
	 * Regenerar ID periódicamente
	 */
	private function regenerateIdIfNeeded(): void {
		$last_regeneration = (int)($this->userdata['session_regenerated_at'] ?? $this->userdata['session_time']);
		$regeneration_interval = 1800; // 30 minutos

		if (($last_regeneration + $regeneration_interval) < $this->time_now) {
			$this->regenerateId();
		}
	}

	/**
	 * Establecer cookie segura
	 */
	private function setSecureCookie(string $name, string $value, int $expire): void {
		$cookie_name = $this->cookie_name . '_' . $name;

		$options = [
			'expires' => $this->time_now + $expire,
			'path' => $this->cookie_path,
			'domain' => $this->cookie_domain,
			'secure' => $this->cookie_secure,
			'httponly' => $this->cookie_httponly,
			'samesite' => $this->cookie_samesite
		];

		setcookie($cookie_name, $value, $options);
		$_COOKIE[$cookie_name] = $value;
	}

	/**
	 * Expirar cookie
	 */
	private function expireCookie(): void {
		$cookie_name = $this->cookie_name . '_' . $this->set_cookie_name;

		$options = [
			'expires' => $this->time_now - 86400,
			'path' => $this->cookie_path,
			'domain' => $this->cookie_domain,
			'secure' => $this->cookie_secure,
			'httponly' => $this->cookie_httponly,
			'samesite' => $this->cookie_samesite
		];

		setcookie($cookie_name, '', $options);

		if (isset($_COOKIE[$cookie_name])) {
			unset($_COOKIE[$cookie_name]);
		}
	}

	/**
	 * Validar User Agent
	 */
	private function validateUserAgent(?string $ua): string {
		if (empty($ua)) {
			return '';
		}

		$ua = substr($ua, 0, 255);
		$ua = preg_replace('/[^\x20-\x7E]/', '', $ua);

		return $ua;
	}

	/**
	 * Garbage Collector
	 */
	public function sess_gc(): void {
		// 30% de probabilidad
		if (mt_rand(1, 100) > 30) {
			return;
		}

		$expire = $this->time_now - $this->sess_time_online;
		DB::query(
			"DELETE FROM u_sessions WHERE session_time < :expire AND session_autologin = 0",
			['expire' => $expire]
		);
	}
}
