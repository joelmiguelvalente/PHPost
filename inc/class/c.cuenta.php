<?php

/**
 * @name c.cuenta.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once dirname(__DIR__, 1) . '/utils/IP.php';
require_once dirname(__DIR__, 1) . '/utils/PasswordHandler.php';

class tsCuenta {

	protected tsCore $Core;
	protected tsUser $User;
	protected PasswordHandler $PasswordHandler;

	# Redes sociales disponibles
	public array $redes = [
		'facebook'	=> 'Facebook', 
		'twitter' 	=> 'Twitter', 
		'instagram' => 'Instagram',
		'youtube' 	=> 'Youtube',
		'twitch' 	=> 'Twitch'
	];

	public function __construct(tsCore $Core, tsUser $User) {
	   $PasswordHandler = new PasswordHandler;
		$this->Core = $Core;
		$this->User = $User;
		$this->PasswordHandler = $PasswordHandler;
	}

	/**
	 * @name loadPerfil()
	 * @access public
	 * @uses Cargamos el perfil de un usuario
	 * @param int
	 * @return array
	 */
	public function loadPerfil(int $userId = 0) {
		if(empty($userId)) $userId = (int)$this->User->uid;
		//
		$perfilInfo = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT p.*, u.user_registro, u.user_lastactive FROM u_perfil AS p LEFT JOIN u_miembros AS u ON p.user_id = u.user_id WHERE p.user_id = $userId LIMIT 1"));
		// FECHA DE NACIMIENTO
		$fecha = empty($perfilInfo['user_dia']) ? date('d-m-Y') : sprintf('%02d-%02d-%04d', $perfilInfo['user_dia'], $perfilInfo['user_mes'], $perfilInfo['user_ano']);
		$perfilInfo['nacimiento'] = date("Y-m-d", strtotime($fecha));
		// CAMBIOS
		$perfilInfo = $this->unData($perfilInfo);
		$perfilInfo = $this->sanitizeProfileData($perfilInfo);
		//
		return $perfilInfo;
	}

	private function decodeJson($value): array {
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
	private function unData($data): array {
		// Redes disponibles
		$data['redes'] = $this->redes;
		// Social links
		$data['p_socials'] = $this->decodeJson($data['p_socials'] ?? []);
		// Normalizar sociales según redes definidas
		$data['p_socials'] = array_intersect_key($data['p_socials'], $this->redes);
		return $data;
	}

	/**
	 * @name sanitizeProfileData
	 * @access private
	 * @param array
	 * @return array
	*/
	private function sanitizeProfileData(array $data): array {
	   foreach (['p_nombre', 'p_mensaje'] as $field) {
		  $data[$field] = $this->Core->setSecure(
			 $this->Core->parseBadWords($data[$field] ?? ''), true
		  );
	   }
	   $data['user_pais'] = empty($data['user_pais']) ? 'XX' : $data['user_pais'];
	   return $data;
	}

	/**
	 * @access public
	 * @param int
	 * @param bool
	 * @return bool
	 */
	public function isFollowed(int $userId, bool $user = true): bool {
		$sql = ($user) ?
		"f_id = $userId AND f_user = {$this->User->uid}" : 
		"f_id = {$this->User->uid} AND f_user = $userId";
		
		return db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 
			"SELECT 1 FROM u_follows WHERE $sql AND f_type = 1 LIMIT 1"
		)) === 1;
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
			'following' => $this->isFollowed($userId, true) || $this->User->is_admod,
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
		$visitas = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.for = $userId AND v.type = 1 AND user > 0 ORDER BY v.date DESC LIMIT 8"));
		//
		$data['visitas'] = $visitas;
		$data['visitas_total'] = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(u.user_id) AS a FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.for = $userId AND v.type = 1"))[0];
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
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, u.user_registro, u.user_lastactive, u.user_activo, u.user_baneado, p.user_sexo, p.user_pais, p.p_nombre, p.p_avatar, p.p_mensaje, p.p_sitio, p.p_socials, p.p_privacidad, p.p_mensajes_privados, p.p_publicar_muro, p.p_muro_visitas FROM u_miembros AS u, u_perfil AS p WHERE u.user_id = $userId AND p.user_id = $userId"));
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
		
		require_once __DIR__ . '/c.visitas.php';
		$Visitas = new tsVisitas($this->Core, $this->User);
		$visitado = $Visitas->setVisitaCuenta($userId);
		
		// REAL STATS
		$data['stats'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_rango, u.user_puntos, u.user_posts, u.user_comentarios, u.user_seguidores, u.user_cache, r.r_name, r.r_color FROM u_miembros AS u LEFT JOIN u_rangos AS r ON  u.user_rango = r.rango_id WHERE u.user_id = $userId"));
		
		if((int)$data['stats']['user_cache'] < time() - ((int)$this->Core->settings['c_stats_cache'] * 60)) {
			$query1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(post_id) FROM p_posts WHERE post_user = $userId AND post_status = 0"));
			$query2 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(follow_id) FROM u_follows WHERE f_id = $userId AND f_type = 1"));
			$query3 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(cid) FROM p_comentarios WHERE c_user = $userId AND c_status = 0"));
		
			$data['stats']['user_posts'] = $query1[0];
			$data['stats']['user_seguidores'] = $query2[0];
			$data['stats']['user_comentarios'] = $query3[0];
			$update = $this->Core->buildSqlSet([
				'posts' => $query1[0],
				'comentarios' => $query3[0],
				'seguidores' => $query2[0],
				'cache' => time()
			], 'user_');
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET $update WHERE user_id = $userId");
		}
		$query4 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_user = $userId AND f_status = 0"));
		$data['stats']['user_fotos'] = $query4[0];
		
		// BLOQUEADO
		$data['block'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM `u_bloqueos` WHERE b_user = {$this->User->uid} AND b_auser = $userId LIMIT 1"));
		//
		return $data;
	}

	private function loadGeneralFollow(array &$data, int $userId, bool $follow = true) {
		$max = 21;
		$sql = $follow ? 'f.f_user = u.user_id WHERE f.f_id' : 'f.f_id = u.user_id WHERE f.f_user';
		$key = $follow ? 'segs' : 'sigd';
		// SEGUIDORES
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT f.follow_id, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON $sql = $userId AND f.f_type = 1 AND u.user_activo = 1 AND u.user_baneado = 0 ORDER BY f.f_date DESC LIMIT $max");
		$result = result_array($query);
		$data['segs']['data'] = $result;
		$data['segs']['total'] = count($result ?? 0);
	}

	/*
		loadGeneral($userId)
	*/
	public function loadGeneral(int $userId = 0) {
		$max = 21;
		// MEDALLAS
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = $userId AND m.m_type = 1 ORDER BY a.medal_date DESC LIMIT $max");
		$data['medallas'] = result_array($query);
		$data['m_total'] = count($data['medallas'] ?? 0);
		
		// SEGUIDORES
		$this->loadGeneralFollow($data, $userId, true);
		$this->loadGeneralFollow($data, $userId, false);
		
		// ULTIMAS FOTOS
		if(isset($_GET['pid']) && !empty($_GET['pid'])) {
			$data['fotos'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT foto_id, f_title, f_url FROM f_fotos WHERE f_user = $userId ORDER BY RAND() DESC LIMIT 6"));
			$data['fotos_total'] = count($data['fotos']);			
		}
		//
		return $data;
	}

	/**
	 * @access public
	 * @param int
	 * @return array
	 */
	public function loadPosts(int $userId): array {
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT p.post_id, p.post_title, p.post_puntos, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_user = $userId ORDER BY p.post_date DESC LIMIT 18");
		$data['posts'] = result_array($query);
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
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = $userId AND m.m_type = 1 ORDER BY a.medal_date DESC");
		$data['medallas'] = result_array($query);
		$data['total'] = count($data['medallas'] ?? 0);
		return $data;
	}

	private function guardarCuenta(): string {
		$year = (int)date('Y');
		$nac = explode('-', $_POST['nacimiento']);
	   // Normalizar entrada
	   $input = [
		  'email'  => $this->Core->setSecure($_POST['email'] ?? '', true),
		  'pais'   => $this->Core->setSecure($_POST['pais'] ?? ''),
		  'estado' => $this->Core->setSecure($_POST['estado'] ?? ''),
		  'sexo'   => trim($_POST['sexo'] ?? 'none'),
		  'dia'    => (int)($nac[2] ?? 0),
		  'mes'    => (int)($nac[1] ?? 0),
		  'ano'    => (int)($nac[0] ?? 0),
		  'firma'  => $this->Core->setSecure($this->Core->parseBadWords($_POST['firma'] ?? ''), true),
	   ];
	   // Datos actuales
		$current = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_dia, user_mes, user_ano, user_pais, user_estado, user_sexo, user_firma FROM u_perfil WHERE user_id = {$this->User->uid} LIMIT 1"));
		$response = null;
		// Validaciones
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			$response = '0: El formato de email ingresado no es válido.';
			$input['email'] = $this->User->info['user_email'];
		} elseif (
		   !checkdate($input['mes'], $input['dia'], $input['ano']) ||
		   $input['ano'] < ($year - 100) || $input['ano'] > $year
		) {
		   $response = '0: La fecha de nacimiento no es válida.';
		   foreach (['dia','mes','ano'] as $k) {
			  $input[$k] = (int)$current["user_$k"];
		   }
		} elseif (!in_array($input['sexo'], ['none', 'female', 'male'], true)) {
			$response = '0: Especifica un género sexual válido.';
			$input['sexo'] = $current['user_sexo'];
		} elseif ($input['pais'] === '') {
		   $response = '0: Por favor, especifica tu país.';
		   $input['pais'] = $current['user_pais'];
		} elseif ($input['estado'] === '') {
		   $response = '0: Por favor, especifica tu estado.';
		   $input['estado'] = $current['user_estado'];
		} elseif (mb_strlen($input['firma']) > 300) {
		   $response = '0: La firma no puede superar los 300 caracteres.';
		   $input['firma'] = $current['user_firma'];
		} elseif ($input['email'] !== $this->User->info['user_email']) {
			$exists = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM u_miembros WHERE user_email = '{$input['email']}' LIMIT 1"));
			if ($exists) {
				$response = '0: Este email ya existe, ingresa uno distinto.';
				$input['email'] = $this->User->info['user_email'];
			} else {
				$response = "0: Los cambios fueron aceptados. La nueva dirección de correo debe ser verificada. {$this->Core->settings['titulo']} enviará un email con instrucciones.";
			}
		}
		// Persistencia
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_email = '{$input['email']}' WHERE user_id = {$this->User->uid}");
		unset($input['email']);
		$updates = $this->Core->buildSqlSet($input, 'user_');
		if (db_exec([__FILE__, __LINE__], 'query', "UPDATE u_perfil SET {$updates} WHERE user_id = {$this->User->uid}")) {
			return $response ?? '1: Los cambios fueron aplicados.';
		}
		show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'Base de datos');
	}

	private function guardarPerfil(): string {
	   // Normalizar sitio
	   $sitio = trim($_POST['sitio'] ?? '');
	   if ($sitio !== '' && !str_starts_with($sitio, 'http')) {
		  $sitio = 'http://' . $sitio;
	   }
	   if ($sitio !== '' && !filter_var($sitio, FILTER_VALIDATE_URL)) {
		  return '0: El sitio web introducido no es correcto.';
	   }
	   // Redes sociales
	   $socials = [];
	   if (!empty($_POST['red']) && is_array($_POST['red'])) {
		  foreach ($_POST['red'] as $key => $value) {
			 $socials[$key] = $this->Core->setSecure($this->Core->parseBadWords((string)$value), true);
		  }
	   }
	   // Datos a persistir
	   $perfilData = [
		  'nombre' => $this->Core->setSecure($this->Core->parseBadWords($_POST['nombre'] ?? ''), true),
		  'mensaje' => $this->Core->setSecure($this->Core->parseBadWords($_POST['mensaje'] ?? ''), true),
		  'sitio' => $this->Core->setSecure($this->Core->parseBadWords($sitio), true),
		  'socials' => json_encode($socials, JSON_UNESCAPED_UNICODE),
	   ];
	   // Update
	   $updates = $this->Core->buildSqlSet($perfilData, 'p_');
		if (db_exec([__FILE__, __LINE__], 'query', "UPDATE u_perfil SET {$updates} WHERE user_id = {$this->User->uid}")) {
		  return '1: Los cambios fueron aplicados.';
	   }
	   show_error('Error al ejecutar la consulta.', 'Base de datos');
	}

	private function guardarContasena(): string {
	   $currentPassword  = trim($_POST['password'] ?? '');
	   $newPassword      = trim($_POST['newPassword'] ?? '');
	   $confirmPassword  = trim($_POST['confirmPassword'] ?? '');

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
	   if (!$this->PasswordHandler->verify($currentPassword, $this->User->info['user_password'])) {
	      return '0: Tu contraseña actual no es correcta.';
	   }
	   // Evitar reutilizar la misma contraseña
	   if ($this->PasswordHandler->verify($newPassword, $this->User->info['user_password'])) {
	      return '0: No puedes usar la misma contraseña que la actual.';
	   }
	   if (!$this->PasswordHandler->isStrong($newPassword)) {
    		return '0: La contraseña debe contener mayúsculas, números y caracteres especiales.';
		}
	   $newHash = $this->PasswordHandler->create($newPassword);
	   if (!db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_password = '$newHash' WHERE user_id = {$this->User->uid}")) {
	      return '0: Lo sentimos, ocurrió un error al actualizar la contraseña.';
	   }
	   return '1: Contraseña actualizada correctamente.';
	}

	private function guardarNick(): string {
	   $IP = new IP;

	   $nuevoNick = $this->Core->setSecure(trim($_POST['new_nick'] ?? ''));
	   $password  = trim($_POST['password'] ?? '');
	   $email     = trim($_POST['email'] ?? $this->User->info['user_email']);
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
	   if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM w_blacklist WHERE type = 4 AND value = '$nuevoNick' LIMIT 1"))) {
	      return '0: El nick no está permitido.';
	   }
	   // Nick en uso
	   if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM u_miembros WHERE user_name = '$nuevoNick' LIMIT 1"))) {
	      return '0: El nombre ya está en uso.';
	   }
	   // Solicitud pendiente
	   $pending = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT id, time FROM u_nicks WHERE user_id = {$this->User->uid} AND estado = 0 LIMIT 1"));
	   if (!empty($pending['id'])) {
	      return '0: Ya tienes una petición de cambio de nick en curso.';
	   }
	   // Validar contraseña actual (modelo nuevo)
	   if (!$this->PasswordHandler->verify($password, $this->User->info['user_password'])) {
	      return '0: Tu contraseña actual no es correcta.';
	   }
	   // Cooldown (1 año)
	   if (!empty($pending['time']) && (time() - (int)$pending['time']) < 31536000) {
	      return '0: Aún no puedes solicitar otro cambio de nick.';
	   }
	   $myIP = $IP->executeIP();
	   if (db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_nicks (user_id, user_email, name_1, name_2, time, ip) VALUES ({$this->User->uid}, '$email', '{$this->User->nick}', '$nuevoNick', $time, '$myIP')")) {
	      return '1: Proceso iniciado. Recibirás una respuesta por correo cuando se evalúe el cambio.';
	   }
	   return '0: Ocurrió un error al iniciar el proceso.';
	}

	/**
	 * @access public
	 * @param int
	 * @return array
	 */
	public function savePerfil() {
		$save = $_POST['pagina'] ?? '';
		// GUARDAR...
		switch($save){
			case '':
				return $this->guardarCuenta();
			break;
			case 'perfil':
				return $this->guardarPerfil();
			break;
			// NEW PASSWORD
			case 'clave':
				return $this->guardarContasena();
			break;
			case 'config':
				$perfilData['privacidad'] = trim($_POST['privacidad']);
				$perfilData['publicar_muro'] = trim($_POST['publicar_muro'] ?? 'nobody');
				$perfilData['mensajes_privados'] = trim($_POST['mensajes_privados'] ?? 'nobody');
				$perfilData['muro_visitas'] = trim($_POST['muro_visitas'] ?? 'nobody');
				$updates = $this->Core->buildSqlSet($perfilData, 'p_');
				return (db_exec([__FILE__, __LINE__], "query", "UPDATE u_perfil SET {$updates} WHERE user_id = " . $this->User->uid)) ? '1: Los cambios fueron aceptados y ser&aacute;n aplicados.' : die(show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'Base de datos'));
			break;
			case 'nick':
				return $this->guardarNick();
		 	break;
		}
	}
	
	/**
	 * @access public
	 * @return string
	 */
	public function desactivarCuenta(): string {
		if(isset($_POST['validar']) && (string)$_POST['validar'] === 'true') {
			if(db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_activo = 0 WHERE user_id = {$this->User->uid}")) {
				return '1: Tu cuenta ha sido desactivada';
			}
		}
		return '0: No se pudo desactivar';
	}

	public function cambiarBloqueo(): string {
	   $targetUserId = (int)($_POST['user'] ?? 0);
	   $bloquear     = !empty($_POST['bloquear']);
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
	   if ($bloquear) {
	   	// ¿Ya está bloqueado?
	      $exists = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT 1 FROM u_bloqueos WHERE b_user = {$this->User->uid} AND b_auser = $targetUserId LIMIT 1"));
	      if ($exists) {
	         return '0: Ya has bloqueado a este usuario.';
	      }
	      if (db_exec([__FILE__, __LINE__], 'query', "INSERT IGNORE INTO u_bloqueos (b_user, b_auser)  VALUES ({$this->User->uid}, $targetUserId)")) {
	         return '1: El usuario fue bloqueado satisfactoriamente.';
	      }
	      return '0: No se pudo bloquear al usuario.';
	   }
	   // Desbloquear
	   if (db_exec([__FILE__, __LINE__], 'query', "DELETE FROM u_bloqueos WHERE b_user = {$this->User->uid} AND b_auser = $targetUserId")) {
	      return '1: El usuario fue desbloqueado satisfactoriamente.';
	   }
	   return '0: No se pudo desbloquear al usuario.';
	}
	
	/*
		loadBloqueos()
	*/
	function loadBloqueos() {
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT b.*, u.user_name FROM u_miembros AS u LEFT JOIN u_bloqueos AS b ON u.user_id = b.b_auser WHERE b.b_user = \''.(int)$this->User->uid.'\'');
		$data = result_array($query);
		
		//
		return $data;
	}
}
