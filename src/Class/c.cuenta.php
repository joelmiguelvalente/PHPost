<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsCuenta {

	# Redes sociales disponibles
	public array $redes = [
		'facebook'	=> 'Facebook', 
		'twitter' 	=> 'Twitter', 
		'instagram' => 'Instagram',
		'youtube' 	=> 'Youtube',
		'twitch' 	=> 'Twitch'
	];

	private string $myIP;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected Password $Password,
		protected UserHelper $UserHelper,
		protected IP $IP
	) {
		$this->myIP = $this->IP->getIPBinary();
	}

	/**
	 * @name loadPerfil()
	 * @access public
	 * @uses Cargamos el perfil de un usuario
	 * @param int
	 * @return array
	 */
	public function loadPerfil(int $userId = 0): array {
		if(empty($userId)) $userId = (int)$this->User->uid;
		//
		$perfilInfo = DB::fetch("SELECT p.*, u.user_registro, u.user_lastactive FROM u_perfil AS p LEFT JOIN u_miembros AS u ON p.user_id = u.user_id WHERE p.user_id = :uid LIMIT 1", ['uid' => $userId]);
		// FECHA DE NACIMIENTO
		$fecha = empty($perfilInfo['user_dia']) ? date('d-m-Y') : sprintf('%02d-%02d-%04d', $perfilInfo['user_dia'], $perfilInfo['user_mes'], $perfilInfo['user_ano']);
		$perfilInfo['nacimiento'] = date("Y-m-d", strtotime($fecha));
		// CAMBIOS
		$perfilInfo = $this->unData($perfilInfo);
		$perfilInfo = $this->sanitizeProfileData($perfilInfo);
		//
		return $perfilInfo;
	}

	private function decodeJson(mixed $value): array {
		if (empty($value) || !is_string($value)) {
			return [];
		}
		$decoded = json_decode($value, true);
		return is_array($decoded) ? $decoded : [];
	}

	/**
	 * @name unData
	 * @access private
	 * @param array
	 * @return array
	*/
	private function unData(array $data): array {
		// Social links
		$socials = $this->decodeJson($data['p_socials'] ?? []);
		return [
			// Redes disponibles
			'redes' => $this->redes,
			// Normalizar sociales según redes definidas
			'p_socials' => array_intersect_key($socials, $this->redes)
		];
	}

	/**
	 * @name sanitizeProfileData
	 * @access private
	 * @param array
	 * @return array
	*/
	private function sanitizeProfileData(array $data): array {
	   foreach (['p_nombre', 'p_mensaje'] as $field) {
		  	$data[$field] = Html::escape($data[$field] ?? '');
	   }
	   $data['user_pais'] = Html::escape($data['user_pais'] ?? 'XX');
	   return $data;
	}

	/**
	 * @access public
	 * @param int
	 * @param bool
	 * @return bool
	 */
	public function isFollowed(int $userId, bool $user = true): bool {
		$params = ['fid' => $userId, 'fuser' => $this->User->uid];
		if(!$user) {
			$params = ['fid' => $this->User->uid, 'fuser' => $userId];
		}
		return DB::exists("SELECT 1 FROM u_follows WHERE f_id = :fid AND f_user = :fuser AND f_type = 1 LIMIT 1", $params);
	}

	/**
	 * @name canViewHits
	 * @access private
	 * @param string
	 * @param int
	 * @return bool
	*/
	private function canViewHits(string $hits, int $userId): bool {
		return match ($hits) {
			'nobody' => false,
			'following' => $this->isFollowed($userId) || $this->User->is_admod,
			'followers' => $this->isFollowed($userId, false) || $this->User->is_admod,
			'registered' => $this->User->is_member,
			'everyone' => true,
			default => false,
		};
	}

	/**
	 * @name loadVisits
	 * @access private
	 * @param int
	 * @return array
	*/
	private function loadVisits(int $userId): array {
		$param = ['uid' => $userId];
		$visitas = DB::fetchAll("SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.target_id = :uid AND v.type = 1 AND user > 0 ORDER BY v.date DESC LIMIT 8", $param);
		//
		$data['visitas'] = $visitas;
		$data['visitas_total'] = DB::value("SELECT COUNT(u.user_id) FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.target_id = :uid AND v.type = 1", $param);
		return $data;
	}

	/**
	 * @name loadHeadInfo
	 * @access public
	 * @param int
	 * @return array
	*/
	public function loadHeadInfo(int $userId = 0): array {
		// INFORMACION GENERAL
		$param = ['uid' => $userId];
		$data = DB::fetch("SELECT u.user_id, u.user_name, u.user_registro, u.user_lastactive, u.user_activo, u.user_baneado, p.user_sexo, p.user_pais, p.p_nombre, p.p_avatar, p.p_mensaje, p.p_sitio, p.p_socials, p.p_privacidad, p.p_mensajes_privados, p.p_publicar_muro, p.p_muro_visitas FROM u_miembros AS u, u_perfil AS p WHERE u.user_id = :uid AND p.user_id = :uid", $param);
		//
		$data = $this->sanitizeProfileData($data);
		if(!empty($data['p_socials'])) {
			$data['p_socials'] = json_decode($data['p_socials'], true);
			foreach ($this->redes as $name => $valor) $data['p_socials'][$name];
		} else {
			$data['p_socials'] = [];
		}
		
		$data['can_hits'] = $this->canViewHits($data['p_muro_visitas'], $userId);
	   	if ($data['can_hits']) {
		  	$data += $this->loadVisits($userId);
	   	}

		$Visitas = Container::get(tsVisitas::class);
		$visitado = $Visitas->updateViews((int)$userId);
		
		// REAL STATS
		$data['stats'] = DB::fetch("SELECT u.user_id, u.user_rango, u.user_puntos, u.user_posts, u.user_comentarios, u.user_seguidores, u.user_cache, r.r_name, r.r_color FROM u_miembros AS u LEFT JOIN u_rangos AS r ON  u.user_rango = r.rango_id WHERE u.user_id = :uid", $param);
		
		if((int)$data['stats']['user_cache'] < time() - ((int)$this->Core->settings['c_stats_cache'] * 60)) {
			$query1 = DB::value("SELECT COUNT(post_id) FROM p_posts WHERE post_user = :uid AND post_status = 'publicado'", $param);
			$query2 = DB::value("SELECT COUNT(follow_id) FROM u_follows WHERE f_id = :uid AND f_type = 1", $param);
			$query3 = DB::value("SELECT COUNT(cid) FROM p_comentarios WHERE c_user = :uid AND c_status = 0", $param);
		
			$data['stats']['user_posts'] = $query1;
			$data['stats']['user_seguidores'] = $query2;
			$data['stats']['user_comentarios'] = $query3;
			$update = [
				'user_posts' => $query1,
				'user_comentarios' => $query3,
				'user_seguidores' => $query2,
				'user_cache' => time()
			];
			DB::update('u_miembros', $update, 'user_id = :uid', $param);
		}
		$data['stats']['user_fotos'] = DB::value("SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_user = :uid AND f_status = 0", $param);
		
		// BLOQUEADO
		$data['block'] = $this->UserHelper->isBlocked($this->User->uid, (int)$userId);
		//
		return $data;
	}

	private function loadGeneralFollow(array &$data, int $userId, bool $follow = true): void {
		$max = 21;
		$sql = $follow ? 'f.f_user = u.user_id WHERE f.f_id' : 'f.f_id = u.user_id WHERE f.f_user';
		$key = $follow ? 'segs' : 'sigd';
		// SEGUIDORES
		$data[$key]['data'] = DB::fetchAll("SELECT f.follow_id, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON $sql = :uid AND f.f_type = 1 AND u.user_activo = 1 AND u.user_baneado = 0 ORDER BY f.f_date DESC LIMIT $max", ['uid' => $userId]);
		$data[$key]['total'] = count($data[$key]['data']);
	}

	/*
		loadGeneral($userId)
	*/
	public function loadGeneral(int $userId = 0): array {
		$max = 21;
		// MEDALLAS
		$data['medallas'] = DB::fetchAll("SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = :uid AND m.m_type = 1 ORDER BY a.medal_date DESC LIMIT $max", ['uid' => $userId]);
		$data['m_total'] = count($data['medallas']);
		
		// SEGUIDORES
		$this->loadGeneralFollow($data, $userId, true);
		$this->loadGeneralFollow($data, $userId, false);
		
		// ULTIMAS FOTOS
		$data['fotos'] = DB::fetchAll("SELECT foto_id, f_title, f_url FROM f_fotos WHERE f_user = :uid ORDER BY RAND() DESC LIMIT 6", ['uid' => $userId]);
		$data['fotos_total'] = count($data['fotos']);
		
		//
		return $data;
	}

	/**
	 * @access public
	 * @param int
	 * @return array
	 */
	public function loadPosts(int $userId): array {
		$data['posts'] = DB::fetchAll("SELECT p.post_id, p.post_title, p.post_puntos, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND p.post_user = :uid ORDER BY p.post_date DESC LIMIT 18", ['uid' => $userId, 'status' => 'publicado']);
		$data['total'] = count($data['posts'] ?? 0);
		// USUARIO
		$data['username'] = $this->User->getUserName($userId);
		return $data;
	}

	/**
	 * @access public
	 * @param int
	 * @return array
	 */
	public function loadMedallas(int $userId): array {
		$data['medallas'] = DB::fetchAll("SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = :uid AND m.m_type = 1 ORDER BY a.medal_date DESC", ['uid' => $userId]);
		$data['total'] = count($data['medallas']);
		return $data;
	}

	private function guardarCuenta(): string {
		$year = (int)date('Y');
		$nac = explode('-', Request::post('nacimiento'));
		$firma = $this->Core->parseBadWords(Request::post('firma'));
	   	// Normalizar entrada
	   	$input = [
			'user_email'  => Html::escape(Request::post('email')),
			'user_pais'   => Html::escape(Request::post('pais')),
			'user_estado' => Html::escape(Request::post('estado')),
			'user_sexo'   => trim(Request::post('sexo', 'none')),
			'user_dia'    => (int)($nac[2] ?? 0),
			'user_mes'    => (int)($nac[1] ?? 0),
			'user_ano'    => (int)($nac[0] ?? 0),
			'user_firma'  => Html::escape($firma),
	   ];
	   // Datos actuales
		$current = DB::fetch("SELECT user_dia, user_mes, user_ano, user_pais, user_estado, user_sexo, user_firma FROM u_perfil WHERE user_id = :uid LIMIT 1", ['uid' => $this->User->uid]);
		$response = null;
		// Validaciones
		if (!filter_var($input['user_email'], FILTER_VALIDATE_EMAIL)) {
			$response = '0: El formato de email ingresado no es válido.';
			$input['user_email'] = $this->User->info['user_email'];
		} elseif (
		   !checkdate($input['user_mes'], $input['user_dia'], $input['user_ano']) ||
		   $input['user_ano'] < ($year - 100) || $input['user_ano'] > $year
		) {
		   $response = '0: La fecha de nacimiento no es válida.';
		   foreach (['dia','mes','ano'] as $k) {
			  $input[$k] = (int)$current["user_$k"];
		   }
		} elseif (!in_array($input['user_sexo'], ['none', 'female', 'male'], true)) {
			$response = '0: Especifica un género sexual válido.';
			$input['user_sexo'] = $current['user_sexo'];
		} elseif ($input['user_pais'] === '') {
		   $response = '0: Por favor, especifica tu país.';
		   $input['user_pais'] = $current['user_pais'];
		} elseif ($input['user_estado'] === '') {
		   $response = '0: Por favor, especifica tu estado.';
		   $input['user_estado'] = $current['user_estado'];
		} elseif (mb_strlen($input['user_firma']) > 300) {
		   $response = '0: La firma no puede superar los 300 caracteres.';
		   $input['user_firma'] = $current['user_firma'];
		} elseif ($input['user_email'] !== $this->User->info['user_email']) {
			$exists = DB::exists("SELECT 1 FROM u_miembros WHERE user_email = :email LIMIT 1", ['email' => $input['user_email']]);
			if ($exists) {
				$response = '0: Este email ya existe, ingresa uno distinto.';
				$input['user_email'] = $this->User->info['user_email'];
			} else {
				$response = "0: Los cambios fueron aceptados. La nueva dirección de correo debe ser verificada. {$this->Core->settings['titulo']} enviará un email con instrucciones.";
			}
		}
		// Persistencia
		DB::update('u_miembros', ['user_email' => $input['user_email']], 'user_id = :id', ['id' => $this->User->uid]);
		unset($input['user_email']);
		if (DB::update('u_perfil', $input, 'user_id = :id', ['id' => $this->User->uid])) {
			return $response ?? '1: Los cambios fueron aplicados.';
		}
	   show_error('Error al ejecutar la consulta.', 'Base de datos');
	}

	private function guardarPerfil(): string {
	   	// Normalizar sitio
	   	$sitio = trim(Request::post('sitio'));
	   	if ($sitio !== '' && !str_starts_with($sitio, 'http')) {
			$sitio = 'http://' . $sitio;
	   	}
	   	if ($sitio !== '' && !filter_var($sitio, FILTER_VALIDATE_URL)) {
		  	return '0: El sitio web introducido no es correcto.';
	   	}
	   	// Redes sociales
	   	$socials = [];
	   	if (!empty(Request::post('red')) && is_array(Request::post('red'))) {
		  	foreach (Request::post('red') as $key => $value) {
			 	$socials[$key] = Html::escape((string)$value);
		  	}
	   	}
	   	// Datos a persistir
	   	foreach(['nombre', 'mensaje', 'sitio'] as $name) {
	   		$key = ($name === 'sitio') ? $sitio : Request::post($name);
	   		$perfilData["p_{$name}"] = Html::escape($key);
	   	}
	   	$perfilData['p_socials'] = json_encode($socials, JSON_UNESCAPED_UNICODE);
	   	// Update
		if (DB::update('u_perfil', $perfilData, 'user_id = :id', ['id' => $this->User->uid])) {
		  	return '1: Los cambios fueron aplicados.';
	   	}
	   	show_error('Error al ejecutar la consulta.', 'Base de datos');
	}

	private function guardarContasena(): string {
		$currentPassword  = trim(Request::post('password'));
		$newPassword      = trim(Request::post('newPassword'));
		$confirmPassword  = trim(Request::post('confirmPassword'));
		// Validaciones básicas
		if (in_array('', [$currentPassword, $newPassword, $confirmPassword], true)) {
			return '0: Debes completar todos los campos.';
		}
		if (strlen($newPassword) < 6) {
			return '0: La nueva contraseña no es válida.';
		}
		if ($newPassword !== $confirmPassword) {
			return '0: La nueva contraseña y su confirmación no coinciden.';
		}
		// Verificar contraseña actual
		if (!$this->Password->verify($currentPassword, $this->User->info['user_password'])) {
			return '0: Tu contraseña actual no es correcta.';
		}
		// Evitar reutilizar la misma contraseña
		if ($this->Password->verify($newPassword, $this->User->info['user_password'])) {
			return '0: No puedes usar la misma contraseña que la actual.';
		}
		if (!$this->Password->isStrong($newPassword)) {
    		return '0: La contraseña debe contener mayúsculas, números y caracteres especiales.';
		}
		$newHash = $this->Password->create($newPassword, $this->User->info['user_name']);
		if (DB::update('u_miembros', ['user_password' => $newHash], 'user_id = :id', ['id' => $this->User->uid])) {
			return '1: Contraseña actualizada correctamente.';
		}
		return '0: Lo sentimos, ocurrió un error al actualizar la contraseña.';
	}

	private function guardarNick(): string {
	   $nuevoNick = Html::escape(trim(Request::post('new_nick')));
	   $password  = trim(Request::post('password'));
	   $email     = trim(Request::post('email', $this->User->info['user_email']));
	   $time      = time();
	   if ($nuevoNick === '') {
	      return '0: El nick no puede estar vacío.';
	   }
	   if (strlen($nuevoNick) < 4 || strlen($nuevoNick) > 20) {
	      return '0: El nick debe tener entre 4 y 20 caracteres.';
	   }
	   if (!preg_match('/^[A-Za-z0-9]+$/', $nuevoNick)) {
	      return '0: El nick debe ser alfanumérico.';
	   }
	   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	      return '0: El formato de email ingresado no es válido.';
	   }
	   // Blacklist
	   if (DB::exists("SELECT 1 FROM w_blacklist WHERE type = 4 AND value = :nick LIMIT 1", ['nick' => $nuevoNick])) {
	      return '0: El nick no está permitido.';
	   }
	   // Nick en uso
	   if (DB::exists("SELECT 1 FROM u_miembros WHERE user_name = :nick LIMIT 1", ['nick' => $nuevoNick])) {
	      return '0: El nombre ya está en uso.';
	   }
	   // Solicitud pendiente
	   $pending = DB::fetch("SELECT id, time FROM u_nicks WHERE user_id = :uid AND estado = 0 LIMIT 1", ['uid' => $this->User->uid]);
	   if (!empty($pending['id'])) {
	      return '0: Ya tienes una petición de cambio de nick en curso.';
	   }
	   // Validar contraseña actual (modelo nuevo)
	   if (!$this->Password->verify($password, $this->User->info['user_password'])) {
	      return '0: Tu contraseña actual no es correcta.';
	   }
	   // Cooldown (1 año)
	   if (!empty($pending['time']) && (time() - (int)$pending['time']) < 31536000) {
	      return '0: Aún no puedes solicitar otro cambio de nick.';
	   }

	   if (DB::insert('u_nicks', [
			'user_id' => $this->User->uid,
			'user_email' => $email,
			'name_1' => $this->User->nick,
			'name_2' => $nuevoNick,
			'time' => $time,
			'ip' => $this->myIP
   	])) {
	      return '1: Proceso iniciado. Recibirás una respuesta por correo cuando se evalúe el cambio.';
	   }
	   return '0: Ocurrió un error al iniciar el proceso.';
	}

	private function guardarConfiguracion(): string {
		foreach(['privacidad', 'publicar_muro', 'mensajes_privados', 'muro_visitas'] as $key) {
			$content = ($key === 'privacidad') ? '' : 'nobody';
			$perfilData['p_' . $key] = trim(Request::post($key, $content));
		}
		
		if(DB::update('u_perfil', $perfilData, 'user_id = :id', ['id' => $this->User->uid])) {
			return '1: Los cambios fueron aceptados y serán aplicados.';
		}
		show_error('Error al ejecutar la consulta.', 'Base de datos');
	}

	/**
	 * @access public
	 * @param int
	 * @return array
	 */
	public function savePerfil(): string {
		return match((string)($_POST['pagina'] ?? '')) {
			'' => $this->guardarCuenta(),
			'perfil' => $this->guardarPerfil(),
			'clave' => $this->guardarContasena(),
			'config' => $this->guardarConfiguracion(),
			'nick' => $this->guardarNick(),
			default => '0: No existe esta funcionalidad.'
		};
	}
	
	/**
	 * @access public
	 * @return string
	 */
	public function desactivarCuenta(): string {
		if(!isset($_POST['validar']) && (string)$_POST['validar'] !== 'true') {
			return '0: No se pudo desactivar';
		}
		DB::update('u_miembros', ['user_activo' => 0], 'user_id = :id', ['id' => $this->User->uid]);
		return '1: Tu cuenta ha sido desactivada';
	}

	public function cambiarBloqueo(): string {
		$targetUserId = (int)Request::post('user', 0);
		$bloquear     = (string)Request::post('bloqueado') === 'true';
		if ($targetUserId <= 0) {
			return '0: Usuario inválido.';
		}
		if ($targetUserId === (int)$this->User->uid) {
			return '0: No puedes bloquearte a ti mismo.';
		}
		// ¿Existe el usuario?
		if (!$this->User->getUserName($targetUserId)) {
			return '0: El usuario seleccionado no existe.';
		}
	   	$param = ['uid' => $this->User->uid, 'target' => $targetUserId];
	   	if ($bloquear) {
	   		// ¿Ya está bloqueado?
	    	$exists = DB::exists("SELECT 1 FROM u_bloqueos WHERE b_user = :uid AND b_auser = :target LIMIT 1", $param);
	    	if ($exists) {
	    	   return '0: Ya has bloqueado a este usuario.';
	    	}
	    	$insert = DB::raw("INSERT IGNORE INTO u_bloqueos (b_user, b_auser, b_date) VALUES (:uid, :target, :time)", [...$param, 'time' => time()]);
	    	return $insert ? '1: El usuario fue bloqueado satisfactoriamente.' : '0: No se pudo bloquear al usuario.';
	   	}
	   	// Desbloquear
	   	if (DB::delete('u_bloqueos', 'b_user = :uid AND b_auser = :target', $param)) {
	    	return '1: El usuario fue desbloqueado satisfactoriamente.';
	   	}
	   	return '0: No se pudo desbloquear al usuario.';
	}
	
	/*
		loadBloqueos()
	*/
	public function loadBloqueos(): array {
		return DB::fetchAll("SELECT b.*, u.user_name FROM u_miembros AS u LEFT JOIN u_bloqueos AS b ON u.user_id = b.b_auser WHERE b.b_user = :uid", ['uid' => $this->User->uid]);
	}

	public function cambiarTema(): string {
		$path = trim(Request::post('skin', 'default'));
		$themes = Container::get(Themes::class)->getUserThemeUse($this->User->uid);
		if($path === $themes) {
			return '0: Ya lo tienes en uso';
		}
		if(!DB::update('u_miembros_sets', ['user_theme' => $path], 'user_id = :uid', ['uid' => $this->User->uid])) {
			return '0: No se pudo cambiar el theme';
		}
		$_SESSION['theme_path'] = $path;
		return '1: Cambiado exitosamente';
	}
}
