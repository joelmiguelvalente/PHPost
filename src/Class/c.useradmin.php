<?php

/**
 * @name src/Class/c.useradmin.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_CLASS . '/c.emails.php';
require_once TS_UTILS . '/PasswordHandler.php';

class tsUserAdmin {

	protected Paginator $Paginator;
	protected PasswordHandler $PasswordHandler;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected tsAdmin $Admin
	) {
		$this->Paginator = new Paginator($this->Core->settings['url']);
		$this->PasswordHandler = new PasswordHandler;
	}

	public function getUserID(bool $isArray = false): int|array {
		$uid = (int)($_GET['uid'] ?? 0);
		if($isArray) {
			return ['uid' => $uid];
		}
		return $uid;
	}

	/**
	* ------------------------------
	* USUARIOS
	* getUsuarios() :: Obtenemos todos los usuarios
	* getUserPrivacidad() :: Obtenemos privacidad del usuario
	* setUserPrivacidad() :: Guardamos privacidad del usuario
	* getUserData() :: Obtenemos datos del usuario
	* setUserData() :: Guardamos datos del usuario
	* deleteContent() :: Eliminamos el contenido del usuario
	* getUserRango() :: Obtenemos el rango del usuario
	* setUserFirma() :: Guardamos nueva firma del usuario
	* setUserInActivo() :: Activar/Desactivar usuario (AJAX)
	* ------------------------------
	*/
	public function getUsuarios() {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);

		$modo = strtoupper((string)($_GET['modo'] ?? 'desc'));
		//
		$order = (string)($_GET['order'] ?? 'id');
		$userOrder = match($order) {
			'status'    => 'u.user_activo, u.user_baneado',
			'email'     => 'u.user_email',
			'ip'        => 'u.user_last_ip',
			'activity'  => 'u.user_lastactive',
			default     => 'u.user_id'
		};
		//
		$data['data'] = DB::fetchAll("SELECT u.*, r.*, p.* FROM u_perfil AS p LEFT JOIN u_miembros AS u ON u.user_id = p.user_id LEFT JOIN u_rangos AS r ON r.rango_id = u.user_rango ORDER BY $userOrder $modo LIMIT $limit");
		# Paginamos
		$total = DB::value("SELECT COUNT(*) FROM u_miembros WHERE user_id > 0");

		$data['pages'] = $this->Paginator->pageIndex("/admin/users?order=$order&modo=" . strtolower($modo), (int)($_GET['s'] ?? 0), (int)$total, (int)$max);

		# Retornamos
		return $data;
	}

	public function getUserPrivacidad() {
		$data = DB::fetch("SELECT p.p_privacidad, p.p_mensajes_privados, p.p_publicar_muro, p.p_muro_visitas FROM u_perfil WHERE user_id = :uid LIMIT 1", $this->getUserID(true));
		//
		return $data;
	}

	private function setUserPrivacidad(): bool {
		$perfilData['p_privacidad'] 		= trim($_POST['privacidad']);
		$perfilData['p_publicar_muro'] 		= trim($_POST['publicar_muro'] ?? 'nobody');
		$perfilData['p_mensajes_privados'] 	= trim($_POST['mensajes_privados'] ?? 'nobody');
		$perfilData['p_muro_visitas'] 		= trim($_POST['muro_visitas'] ?? 'nobody');

		return (DB::update('u_perfil', $perfilData, 'user_id = :id', $this->getUserID(true)));
	}

	public function getUserData() {
		$data = DB::fetch('SELECT u.*, r.*, p.* FROM u_perfil AS p LEFT JOIN u_miembros AS u ON u.user_id = p.user_id LEFT JOIN u_rangos AS r ON r.rango_id = u.user_rango WHERE u.user_id = :uid LIMIT 1', $this->getUserID(true));
		# Retornamos
		return $data;
	}

	/**
	 * Valida un email, retorna el email limpio o lanza excepción.
	 */
	private function checkedEmail(string $email): string
	{
	    $email = $this->Core->setSecure(trim($email));
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	        return 'Correo electrónico incorrecto.';
	    }
	    return $email;
	}

	/**
	 * Valida y hashea la contraseña.
	 * Si no viene password en POST, retorna null (sin cambio).
	 */
	private function checkedPassword(): ?string
	{
	    $password         = trim($_POST['user_password'] ?? '');
	    $password_confirm = trim($_POST['user_confirm']  ?? '');
	    // Si ambos están vacíos, el admin no quiere cambiar la contraseña
	    if ($password === '' && $password_confirm === '') {
	        return null;
	    }
	    if ($password === '' || $password_confirm === '') {
	        return 'Debe completar ambos campos de contraseña.';
	    }
	    if (strlen($password) < 6) {
	        return 'La contraseña es demasiado corta (mínimo 6 caracteres).';
	    }
	    if (!$this->PasswordHandler->isStrong($password)) {
	        return 'La contraseña no cumple los requisitos: debe contener al menos un símbolo, un número y una mayúscula.';
	    }
	    if ($password !== $password_confirm) {
	        return 'Las contraseñas no coinciden.';
	    }
	    return $this->PasswordHandler->create($password);
	}

	/**
	 * Parsea un entero no negativo desde input externo.
	 * Si $raw es null, devuelve $fallback.
	 */
	private function parseNonNegativeInt(mixed $raw, int $fallback, string $errorMsg): int
	{
	    if ($raw === null) {
	        return $fallback;
	    }
	    $value = filter_var($raw, FILTER_VALIDATE_INT);
	    if ($value === false || $value < 0) {
	        return $errorMsg;
	    }
	    return $value;
	}


	public function setUserData() {
		# DATA
		$param = $this->getUserID(true);
		$current = DB::fetch('SELECT user_name, user_email, user_password, user_puntos, user_puntosxdar, user_name_changes FROM u_miembros WHERE user_id = :uid', $param);
		if (!$current) {
            return 'Usuario no encontrado.';
        }
		// --- Campos básicos ---
        $username = trim($_POST['user_name'] ?? $current['user_name']);
        $email = $this->checkedEmail(trim($_POST['user_email'] ?? $current['user_email']));
		// --- Contraseña (opcional) ---
        $newPassword = $this->checkedPassword();
        // --- Campos numéricos ---
        $userPoints  = $this->parseNonNegativeInt($_POST['user_puntos'] ?? null, $current['user_puntos'], 'Los puntos del usuario no son válidos.');
        $pointsToGive = $this->parseNonNegativeInt($_POST['user_puntosxdar'] ?? null, $current['user_puntosxdar'], 'Los puntos para dar no son válidos.');
        $nameChanges  = $this->parseNonNegativeInt($_POST['user_name_changes'] ?? null, $current['user_name_changes'], 'Las disponibilidades de cambio de nombre deben ser numéricas.');

		// --- Construir update ---
        $update = [
            'user_name'         => $username,
            'user_email'        => $email,
            'user_puntos'       => $userPoints,
            'user_puntosxdar'   => $pointsToGive,
            'user_name_changes' => $nameChanges,
        ];

        if ($newPassword !== null) {
            $update['user_password'] = $newPassword;
        }
        // --- Persistir ---
        if (!DB::update('u_miembros', $update, 'user_id = :uid', $param)) {
            return 'No se pudieron guardar los cambios.';
        }
        // --- Notificar al usuario (opcional) ---
        if (isset($_POST['sendata']) && $_POST['sendata'] === 'on') {
			$Email = new tsEmail($this->Core);
			$Email->asunto = 'new_access';
		    $Email->sendFast($email, ['USERNAME' => $username]);
        }
	}

	public function deleteContent(int $user_id = 0){
		#
		$pass = md5(md5($_POST['password']) . strtolower($this->User->nick));
		if(db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_id FROM u_miembros WHERE user_id = \''.$this->User->uid.'\' && user_password = \''.$pass.'\''))) {
			# Nuevo formato mejorado (entendible)
			$todo = isset($_POST['bocuenta']);
			# Creamos un arreglo que tenga las tablas y columnas con datos
			$arreglo = [
				'boposts' => ['tabla' => 'p_posts', 'columna' => 'post_user'],
				'bofotos' => ['tabla' => 'f_fotos', 'columna' => 'f_user'],
				'boestados' => ['tabla' => 'u_muro', 'columna' => 'p_user_pub'],
				'bocomposts' => ['tabla' => 'p_comentarios', 'columna' => 'c_user'],
				'bocomfotos' => ['tabla' => 'f_comentarios', 'columna' => 'c_user'],
				'bocomestados' => ['tabla' => 'u_muro_comentarios', 'columna' => 'c_user'],
				'bolikes' => ['tabla' => 'u_muro_likes', 'columna' => 'user_id'],
				'boseguidores' => ['tabla' => 'u_follows', 'columna' => 'f_type = 1 && f_id'],
				'bosiguiendo' => ['tabla' => 'u_follows', 'columna' => 'f_type = 1 && f_user'],
				'bofavoritos' => ['tabla' => 'p_favoritos', 'columna' => 'fav_user'],
				'bovotosposts' => ['tabla' => 'p_votos', 'columna' => 'tuser'],
				'bovotosfotos' => ['tabla' => 'f_votos', 'columna' => 'v_user'],
				'boactividad' => ['tabla' => 'u_actividad', 'columna' => 'user_id'],
				'boavisos' => ['tabla' => 'u_avisos', 'columna' => 'user_id'],
				'bobloqueos' => ['tabla' => 'u_bloqueos', 'columna' => 'b_user'],
				'bomensajes' => ['tabla' => ['u_mensajes', 'u_respuestas'], 'columna' => ['mp_from', 'mr_from']],
				'bosesiones' => ['tabla' => 'u_sessions', 'columna' => 'session_user_id'],
				'bovisitas' => ['tabla' => 'w_visitas', 'columna' => 'user']
			];
			foreach($arreglo as $accion => $tipo) {
				if(isset($_POST[$accion]) && $_POST[$accion] === 'on') {
					if(is_array($tipo["tabla"]) OR is_array($tipo["columna"])) {
						foreach ($tipo["tabla"] as $t => $tabla) {
							db_exec([__FILE__, __LINE__], 'query', "DELETE FROM {$tipo["tabla"][$t]} WHERE {$tipo["columna"][$t]} = {$user_id}");
						}
					} else {
						db_exec([__FILE__, __LINE__], 'query', "DELETE FROM {$tipo["tabla"]} WHERE {$tipo["columna"]} = {$user_id}");
					}
				}
			}
			//
			if($todo && $this->User->uid != $user_id){
				$array = [
					['tabla' => 'u_miembros', 'columna' => 'user_id'],
					['tabla' => 'u_perfil', 'columna' => 'user_id'],
					['tabla' => 'u_portal', 'columna' => 'user_id'],
					['tabla' => 'w_denuncias', 'columna' => 'd_user'],
					['tabla' => 'u_bloqueos', 'columna' => 'b_auser'],
					['tabla' => 'u_mensajes', 'columna' => 'b_auser'],
					['tabla' => 'w_visitas', 'columna' => 'type = 1 && for']
				];
				foreach($array as $item) {
					db_exec([__FILE__, __LINE__], 'query', "DELETE FROM {$item["tabla"]} WHERE {$item["columna"]} = {$user_id}");
				}
			}
			#
			$data = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_name FROM u_miembros WHERE user_id = '.$user_id));
			$admin = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_email FROM u_miembros WHERE user_id = 1'));
			# Insertamos el aviso
			db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `u_avisos` (`user_id`, `av_subject`, `av_body`, `av_date`, `av_read`, `av_type`) VALUES (\'1\', \'Contenido eliminado\', \'Hola, le informamos que el administrador '.$this->User->nick.' ('.$this->User->uid.') ha eliminado '.($todo ? 'la cuenta' : 'varios contenidos').' de '.$data[0].'.\', \''.time().'\', \'0\', \'1\')');
			# Enviamos el email
			mail($admin[0], 'Contenido eliminado', '<html><head><title>Contenido de cierta cuenta han sido eliminados.</title></head><body><p>Hola, le informamos que el administrador '.$this->User->nick.' ('.$this->User->uid.') ha eliminado '.($todo ? 'la cuenta' : 'varios contenidos').' de '.$data[0].'</p></body></html>', 'Content-type: text/html; charset=iso-8859-15');
			# Retornamos OK
			return 'OK';
		} else return 'Credenciales incorrectas';
	}

	public function getUserRango(int $uid = 0): array {
		# CONSULTA
		$data = [
			'user' => DB::fetch("SELECT u.user_rango, r.rango_id, r.r_name, r.r_color FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = :uid", ['uid' => $uid]),
			'rangos' => $this->Admin->getAllRangos()
		];
		# Retornamos datos
		return $data;
	}

	public function setUserFirma(int $uid = 0): bool {
		$firma = $this->Core->setSecure(trim($_POST['firma'] ?? ''));
		if(empty($firma)) return false;
		return (DB::update('u_perfil', ['user_firma' => $firma], 'user_id = :uid', ['uid' => $uid]));
	}

	public function setUserInActivo() {
		# Obtenemos la ID del usuair
		$usuario = intval($_POST['uid']);
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_activo FROM u_miembros WHERE user_id = ' . $usuario));
		# Hacemos comprobaciones
		$act = (intval($data['user_activo']) === 1) ? 0 : 1;
		$txt = (intval($data['user_activo']) === 1) ? '2: Cuenta desactivada' : '1: Cuenta activada.';
		//
		return (db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_activo = '.$act.' WHERE user_id = ' . $usuario)) ? $txt : '0: Ocurri&oacute, un error';
	}

}
