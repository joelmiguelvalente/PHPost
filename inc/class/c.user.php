<?php

/**
 * @name c.user.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once dirname(__DIR__, 1) . '/utils/AsignarMedallas.php';
require_once dirname(__DIR__, 1) . '/utils/Avatar.php';
require_once dirname(__DIR__, 1) . '/utils/PasswordHandler.php';
require_once __DIR__ . '/c.session.php';

class tsUser {

	private ?tsSession $session = null;
	protected tsCore $Core;

	public $permisos = [];
	public $info = [];
	public $is_member = 0;		// EL USUARIO ESTA LOGUEADO?
	public $is_admod = 0;
	public $is_banned = 0;
	public $avatar = '';
	public $nick = 'Visitante';// NOMBRE A MOSTRAR
	public $uid = 0;			// USER ID
	public $is_error;			// SI OCURRE UN ERROR ESTA VARIABLE CONTENDRA EL NUMERO DE ERROR

	public function __construct() {
		global $tsCore;
		$this->Core = $tsCore;
		/* CARGAR SESSION */
		$this->session = new tsSession($tsCore);
		$this->setSession();
		# Esta logueado, actualiza puntos por día
		if($this->is_member) $this->actualizarPuntos();
	}

	/*
		CARGA LA SESSION
		setSession()
	*/
	private function setSession(): void {
		if ($this->session === null) {
      	throw new RuntimeException('Session no inicializada');
    	}
		// Si no existe una sessión la creamos
		if (!$this->session->read()) $this->session->create();
		// si existe la actualizamos...
		else {
			// Actualizamos sesión
			$this->session->update();
			// Cargamos información
			$this->loadUser();
		}
	}

	/**
	 * @name actualizarPuntos
	 * @access public
	 * @return bool
	 */
	public function actualizarPuntos(): bool {
		// HORA EN LA CUAL RECARGAR PUNTOS 0 = MEDIA NOCHE DEL SERVIDOR
		$ultimaRecarga = $this->info['user_nextpuntos'];
		$tiempoActual = time();
		// SI YA SE PASO EL TIEMPO RECARGAMOS...
		if ($ultimaRecarga < $tiempoActual) {
			// CALCULAR LA SIGUIENTE RECARGA A LAS 24 HRS
			$sigRecarga = strtotime('tomorrow', $tiempoActual);
			// ACTUALIZAR LA BASE DE DATOS
			$keepPoints = (int)$this->Core->settings['c_keep_points'] === 0;
			$points = (int)$this->permisos['gopfd'];
			$puntosxdar = $keepPoints ? $points : "user_puntosxdar + $points";
			// Actualizamos
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_puntosxdar = $puntosxdar, user_nextpuntos = $sigRecarga WHERE user_id = {$this->uid}");
			// VAMONOS
			return true;
		}
		return false;
	}

	/**
	 * @name loadUser
	 * @access public
	 * @param bool
	 * @return mixed
	 */
	public function loadUser(bool $login = false) {
		// Cargar datos
		$sql = "SELECT u.*, s.* FROM u_sessions s, u_miembros u WHERE s.session_id = '{$this->session->ID}' AND u.user_id = s.session_user_id";
		$query = db_exec([__FILE__, __LINE__], 'query', $sql);
		$this->info = db_exec('fetch_assoc', $query);
		// Existe el usuario?
		if(!isset($this->info['user_id'])) return false;
		// PERMISOS SEGUN RANGO
		$this->info['rango'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT r_name, r_color, r_image, r_allows FROM u_rangos WHERE rango_id = '.$this->info['user_id'].' LIMIT 1'));
		// PERMISOS SEGUN RANGO
		$rango = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT r_allows FROM u_rangos WHERE rango_id = \''.$this->info['user_rango'].'\' LIMIT 1'));
		$this->permisos = @unserialize($rango['r_allows']);
		foreach(['moat', 'sumo', 'suad', 'gopp', 'gorpap', 'most'] as $perm) {
			if(!isset($this->permisos[$perm])) $this->permisos[$perm] = false;
		}
		/* ES MIEMBRO */
		$this->is_member = 1;
		$this->is_admod = match(true) {
			!$this->permisos['sumo'] && $this->permisos['suad'] => 1,
			$this->permisos['sumo'] && !$this->permisos['suad'] => 2,
			$this->permisos['sumo'] || $this->permisos['suad'] => 1,
			default => 0
		};
		// NOMBRE
		$this->nick = $this->info['user_name'];
		$this->uid = $this->info['user_id'];
		$this->is_banned = $this->info['user_baneado'];
		// Avatar
		$Avatar = new Avatar;
		$this->avatar = $Avatar->get((int)$this->uid);
		$time = time();
		// ULTIMA ACCION
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_lastactive = $time WHERE user_id = {$this->uid}");
		# Si ha iniciado sesión cargamos estos datos.
		if($login) {
			// Last login
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_lastlogin = {$this->session->time_now} WHERE user_id = {$this->uid}");
			/* REGISTAR IP */
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_last_ip = '{$this->session->ip_address}' WHERE user_id = {$this->uid}");
	  	}
	  	// Borrar variable session
	  	#unset($this->session);
	}

	private function DarMedalla(int $uid): void {
		$q1 = (int)db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT wm.medal_id FROM w_medallas AS wm LEFT JOIN w_medallas_assign AS wma ON wm.medal_id = wma.medal_id WHERE wm.m_type = 1 AND wma.medal_for = $uid"));
		$q2 = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_id = $uid AND f_type = 1"))[0];
		$q3 = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_user = $uid AND f_type = 1"))[0];
		$q4 = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM p_comentarios WHERE c_user = $uid AND c_status = 0"))[0];
		$q5 = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM f_comentarios WHERE c_user = $uid"))[0];
		$q6 = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT foto_id FROM f_fotos WHERE f_status = 0 AND f_user = $uid"))[0];
		$q7 = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id FROM p_posts WHERE post_user = $uid AND post_status = 0"))[0];
		//
		$AsignarMedallas = new AsignarMedallas(1, $uid);
		$medalla->setOwnerUser($uid)
		->setRango($this->info['user_rango'] ?? null)
		->setNotificationType(15)
		->addMetric(1, (int)$this->info['user_puntos'])
		->addMetric(2, $q2)->addMetric(3, $q3)->addMetric(4, $q4)
		->addMetric(5, $q5)->addMetric(6, $q7)->addMetric(7, $q6)
		->addMetric(8, $q1)
		->ejecutar();
	}

	/**
	 * @name loginUser
	 * @access public
	 * @return string
	 */
	public function loginUser(): string {
		[$username, $password, $remember, $redirectTo] = array_pad(func_get_args(), 4, null);
		# Filtramos si es nombre o email
		$filter = (filter_var($username, FILTER_VALIDATE_EMAIL)) ? 'email' : 'name';
		# Consultamos
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_name, user_password, user_activo, user_baneado FROM u_miembros WHERE user_$filter = '$username' LIMIT 1"));
		# Comprobamos que el usuario exista
		if(empty($data)) return '0: El usuario no existe.';
		# Comprobamos la contraseña
		$PasswordHandler = new PasswordHandler;
		if($PasswordHandler->verify($password, $data["user_password"]) === false) {
			return '2: Tu contrase&ntilde;a es incorrecta.';
		}
		# Comprobamos que el usuario este activo
		if((int)$data['user_activo'] === 0) {
			return '3: Debes activar tu cuenta';
		}
		// Actualizamos la session
		if($this->session->update((int)$data['user_id'], $remember, TRUE)) {
			// Cargamos la información del usuario
			$this->loadUser(true);
			// COMPROBAMOS SI TENEMOS QUE ASIGNAR MEDALLAS
			# $this->DarMedalla((int)$data['user_id']);                
			/* REDERIGIR */
			if($redirectTo !== NULL) $this->Core->redirectTo($redirectTo);
			else return '1: Bien, estas ingresando...';
		}
		return '0: Hubo un error al crear su sesion.';
	}

	/**
	 * @name logoutUser
	 * @access public
	 * @param int
	 * @param string
	 * @return bool|void
	 */
	public function logoutUser(int $user_id = 0, string $redirectTo = ''): mixed {
		/* BORRAR SESSION */
		$this->session = new tsSession($this->Core);
		$this->session->read();
		$this->session->destroy();
		$this->session = null;
		/* LIMPIAR VARIABLES */
		$this->info = '';
		$this->is_member = 0;
		# UPDATE
		$lastActive = (int)(time() - (((int)$this->Core->settings['c_last_active'] * 60) * 3));
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_lastactive = $lastActive WHERE user_id = $user_id");
		/* REDERIGIR */
		if($redirectTo !== NULL) $this->Core->redirectTo($redirectTo);	// REDIRIGIR
		else return true;
	}

	/**
	 * @name userActivate
	 * @access public
	 * @param int
	 * @param string
	 * @return bool
	 */
	public function userActivate(int $userID, string $pin): bool {
    	$userID = (int)$userID;
    	// Buscamos si activo o no su cuenta
    	$row = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT id, code_hash, expire_at FROM w_activate WHERE user_id = $userID AND type = 'activation' AND used = 0 LIMIT 1"));
    	// Ya no existe
    	if (empty($row)) {
      	return false;
    	}
    	// Ya expiro
    	if ($row['expires_at'] < time()) {
      	return false;
    	}
    	// El pin no coincide
    	if (!password_verify($pin, $row['code_hash'])) {
      	return false;
    	}
    	// Activamos cuenta
    	db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_activo = 1 WHERE user_id = $userID");
    	// Marcamos código como usado
    	db_exec([__FILE__, __LINE__], 'query', "UPDATE w_activate SET used = 1 WHERE id = {$row['id']}");
    	return true;
	}

	/**
	 * @name getUserBanned
	 * @access public
	 * @return bool|array
	 */
	public function getUserBanned(): bool|array {
   	$uid = (int)$this->uid;
    	$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM u_suspension WHERE user_id = $uid LIMIT 1"));
   	if (empty($data)) {
   	   return false;
   	}
    	$now    = time();
    	$endsAt = (int)$data['susp_termina'];
    	// Suspensión expirada
    	if ($endsAt > 0 && $endsAt < $now) {
        	db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_baneado = 0 WHERE user_id = $uid");
        	db_exec([__FILE__, __LINE__], 'query', "DELETE FROM u_suspension WHERE user_id = $uid");
        	return false;
    	}
    	return $data;
	}

	private function fetchUserField(string $selectField, string $whereField, string|int $value): array {
	   $value = is_int($value) ? (int)$value : $this->Core->setSecure($value);
	   $query = "SELECT $selectField  FROM u_miembros WHERE $whereField = '$value' LIMIT 1";
	   return db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', $query)) ?: [];
	}

	/**
	 * @name getUserID
	 * @access public
	 * @param string
	 * @return int
	 */
	public function getUserID(string $username = ''): int {
		$row = $this->fetchUserField('user_id', 'user_name', $username);
		return (int)($row['user_id'] ?? 0);
	}

	/**
	 * @name getUserName
	 * @access public
	 * @param int
	 * @return string
	 */
	public function getUserName(int $userId = 0): string {
		$row = $this->fetchUserField('user_name', 'user_id', $userId);
		return (string)($row['user_name'] ?? '');
	}

	/**
	 * @name iFollow
	 * @access public
	 * @param int
	 * @return bool
	 */
	public function iFollow(int $user_id = 0): bool {
		$data = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_id = $user_id AND f_user = {$this->uid} AND f_type = 1 LIMIT 1"));
		//
		return ($data > 0);
	}

	private function getUserStatus(int $lastActive, int $onlineLimit, int $inactiveLimit): array {
	   return match (true) {
	      $lastActive > $onlineLimit   => ['t' => 'Online', 'css' => 'online'],
	      $lastActive > $inactiveLimit => ['t' => 'Inactivo', 'css' => 'inactive'],
	      default                      => ['t' => 'Offline', 'css' => 'offline'],
	   };
	}

	/**
	 * @name getUsuarios
	 * @access public
	 * @return array
	 */
	public function getUsuarios(): array {
		$filters = [];
		$data    = [];

		// --- TIEMPOS ---
		$lastActive   = (int) $this->Core->settings['c_last_active'] * 60;
		$now          = time();
		$onlineLimit  = $now - $lastActive;
		$inactiveLimit = $now - ($lastActive * 2);

		// --- FILTROS ---
		if (($_GET['online'] ?? null) === 'true') {
			$filters[] = "u.user_lastactive > $onlineLimit";
		}
		if (($_GET['avatar'] ?? null) === 'true') {
			$filters[] = "p.p_avatar = 1";
		}
		if (!empty($_GET['sexo'])) {
			$sexo = $this->Core->setSecure(trim($_GET['sexo']));
			$filters[] = "p.user_sexo = '$sexo'";
		}
		if (!empty($_GET['pais'])) {
			$pais = $this->Core->setSecure($_GET['pais']);
			$filters[] = "p.user_pais = '$pais'";
		}
		if (!empty($_GET['rango'])) {
			$rango = (int) $_GET['rango'];
			$filters[] = "u.user_rango = $rango";
		}

		// --- WHERE BASE ---
		$where = "u.user_activo = 1 AND u.user_baneado = 0";
		if ($filters) {
			$where .= ' AND ' . implode(' AND ', $filters);
		}
		
		// --- TOTAL ---
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(u.user_id) AS total FROM u_miembros u LEFT JOIN u_perfil p ON u.user_id = p.user_id WHERE $where");
		$total = (int) db_exec('fetch_assoc', $query)['total'];
		$pages = $this->Core->getPagination($total, 12);

		// --- DATA ---
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, p.user_pais, p.user_sexo, p.p_avatar, p.p_mensaje, u.user_rango, u.user_puntos, u.user_comentarios, u.user_posts, u.user_lastactive, u.user_baneado, r.r_name, r.r_color, r.r_image FROM u_miembros u LEFT JOIN u_perfil p ON u.user_id = p.user_id LEFT JOIN u_rangos r ON r.rango_id = u.user_rango WHERE $where ORDER BY u.user_id DESC LIMIT {$pages['limit']}");

		while ($row = db_exec('fetch_assoc', $query)) {
			$row['status'] = $this->getUserStatus((int)$row['user_lastactive'], $onlineLimit, $inactiveLimit);
			$row['rango'] = [
				'title' => $row['r_name'],
				'color' => $row['r_color'],
				'image' => $row['r_image'],
			];
			$data[] = $row;
		}

		// --- TOTAL ACTUAL ---
		$offset = (int) explode(',', $pages['limit'])[0];
		$totalActual = $offset + count($data);

		return [
			'data'  => $data,
			'pages' => $pages,
			'total' => $totalActual,
		];
	}
	
}