<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsUser {

	private $session;

	public $info = [];

	public $permisos;

	public int $uid = 0;

	public int $is_admod = 0;

	public int $is_banned = 0;

	public int $is_member = 0;

	public string $avatar = '';

	public string $nick = 'Anonymous';

	public function __construct(
		protected tsCore $Core,
		protected IP $IP,
		protected tsSession $Session
	) {
		$this->IP = $IP;
		/* CARGAR SESSION */
		$this->session = Container::get(tsSession::class);
		$this->setSession();
		# Esta logueado, actualiza puntos por día
		if ($this->is_member) {
			$this->actualizarPuntos();
		}
	}

	/**
	 * @access private
	 * @return void
	 */
	private function setSession(): void {
		if ($this->session === null) {
			throw new RuntimeException('Session no inicializada');
		}
		// Si no existe una sessión la creamos
		if (!$this->session->read()) {
			$this->session->create();

		// si existe la actualizamos...
		} else {
			// Actualizamos sesión
			$this->session->update();
			// Cargamos información
			$this->loadUser();
		}
	}

	/**
	 * @access public
	 * @return array
	 */
	public function getAllRangos(): array {
		return DB::fetchAll("SELECT rango_id, r_name FROM u_rangos ORDER BY rango_id");
	}

	/**
	 * @access public
	 * @return bool
	 */
	public function actualizarPuntos(): bool {
		$ultimaRecarga = (int)$this->info['user_nextpuntos'];
		$keepPoints = (int)$this->Core->settings['c_keep_points'] === 0;
		$points = (int)$this->permiso('global.limites.puntos_por_dia');
		$tiempoActual = time();

		// Si ya pasó el tiempo de recarga
		if ($ultimaRecarga < $tiempoActual) {
			// Calcular la próxima recarga: mañana a medianoche
			$sigRecarga = strtotime('tomorrow', $tiempoActual);
			if ($keepPoints) {
				// Reiniciar puntos a lo que da el permiso
				$nuevosPuntos = $points;
			} else {
				// Sumar puntos al valor actual (recuperar desde la base de datos)
				$nuevosPuntos = DB::select('u_miembros', 'user_puntosxdar', ['user_id' => $this->uid]);
				$nuevosPuntos = (int)($nuevosPuntos[0]['user_puntosxdar'] ?? 0) + $points;
			}
			// Actualizar base de datos
			$data = [
				'user_puntosxdar' => $nuevosPuntos,
				'user_nextpuntos' => $sigRecarga
			];
			$param = ['uid' => $this->uid];
			DB::update('u_miembros', $data, 'user_id = :uid', $param);
			return true;
		}
		return false;
	}

	private function getPermissions(): void {
		// PERMISOS SEGUN RANGO
		$this->info['rango'] = DB::fetch('SELECT r_name, r_color, r_image, r_allows FROM u_rangos WHERE rango_id = :rid LIMIT 1', ['rid' => $this->info['user_rango']]);
		$raw = $this->info['rango']['r_allows'] ?? '';
		$stored = json_decode($raw, true);
		$this->permisos = array_merge(Permissions::definitions(), is_array($stored) ? $stored : []);
		/* ES MIEMBRO */
		$this->is_member = 1;
		$this->is_admod = match (true) {
			$this->permiso('admin.superadministrador') => 1, // administrador
			$this->permiso('admin.supermoderador') => 2, // moderador
			default => 0,
		};
	}

	/**
	 * @name loadUser
	 * @access public
	 * @param bool
	 * @return mixed
	 */
	public function loadUser(bool $login = false): ?bool {
		// Cargar datos
		$this->info = DB::fetch("SELECT u.*, s.* FROM u_sessions s, u_miembros u WHERE s.session_id = :sid AND u.user_id = s.session_user_id", ['sid' => $this->session->ID]);
		// Existe el usuario?
		if (!isset($this->info['user_id'])) {
			return false;
		}
		// PERMISOS SEGUN RANGO
		$this->getPermissions();
		// NOMBRE
		$this->nick = $this->info['user_name'];
		$this->uid = (int) $this->info['user_id'];
		$this->is_banned = (int)$this->info['user_baneado'];
		$_SESSION['theme_path'] = !isset($_SESSION['theme_path']) ? 'default' : $_SESSION['theme_path'];
		// Avatar
		$this->avatar = Container::get(Avatar::class)->get((int) $this->uid);
		$time = time();
		// ULTIMA ACCION
		$data = ['user_lastactive' => $time];
		# Si ha iniciado sesión cargamos estos datos.
		if ($login) {
			// Último inicio & Registro IP
			$data += [
				'user_lastlogin' => $this->session->time_now,
				'user_last_ip' => $this->session->ip_binary
			];
		}
		DB::update('u_miembros', $data, 'user_id = :uid', ['uid' => $this->uid]);
		return true;
	}

	public function permiso(string $path, mixed $default = null): mixed {
		$meta = Permissions::resolve($path);
		if (!$meta) {
			return $default;
		}
		$code = $meta['code'];
		$value = $this->permisos[$code] ?? $default;
		return $meta['type'] === 'bool' ? (bool) $value : (int) $value;
	}

	public function canModerate(): bool {
		return $this->is_admod ||
		$this->permiso('moderacion.panel.acceso') ||
		$this->permiso('moderacion.usuarios.suspender') ||
		$this->permiso('moderacion.usuarios.desbanear') ||
		$this->permiso('moderacion.posts.fijar') ||
		$this->permiso('moderacion.posts.abrir_cerrar') ||
		$this->permiso('moderacion.posts.eliminar') ||
		$this->permiso('moderacion.posts.ocultar') ||
		$this->permiso('moderacion.posts.editar_comentarios') ||
		$this->permiso('moderacion.posts.revision') ||
		$this->permiso('moderacion.posts.eliminar_comentarios');
	}

	private function DarMedalla(int $uid): void {
		DB::begin();
		try {
			$param = ['uid' => $uid];
			$q1 = DB::value("SELECT COUNT(wm.medal_id) FROM w_medallas AS wm LEFT JOIN w_medallas_assign AS wma ON wm.medal_id = wma.medal_id WHERE wm.m_type = 1 AND wma.medal_for = :uid", $param) ?: 0;
			$q2 = DB::value("SELECT COUNT(follow_id) FROM u_follows WHERE f_id = :uid AND f_type = 1", $param) ?: 0;
			$q3 = DB::value("SELECT COUNT(follow_id) FROM u_follows WHERE f_user = :uid AND f_type = 1", $param) ?: 0;
			$q4 = DB::value("SELECT COUNT(cid) FROM p_comentarios WHERE c_user = :uid AND c_status = 0", $param) ?: 0;
			$q5 = DB::value("SELECT COUNT(cid) FROM f_comentarios WHERE c_user = :uid", $param) ?: 0;
			$q6 = DB::value("SELECT COUNT(foto_id) FROM f_fotos WHERE f_status = 0 AND f_user = :uid", $param) ?: 0;
			$q7 = DB::value("SELECT COUNT(post_id) FROM p_posts WHERE post_user = :uid AND post_status = 'publicado'", $param) ?: 0;
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		$medalla = new AsignarMedalla(1, $uid);
		$medalla->setOwnerUser($uid)
		->setRango($this->info['user_rango'] ?? null)
		->setNotificationType(15)
		->addMetric(1, (int) $this->info['user_puntos'])->addMetric(2, $q2)
		->addMetric(3, $q3)->addMetric(4, $q4)->addMetric(5, $q5)->addMetric(6, $q7)
		->addMetric(7, $q6)->addMetric(8, $q1)->ejecutar();
	}

	/**
	 * @access private
	 * @return bool
	 */
	private function isLocked(int $userId): bool {
		$row = DB::fetch("SELECT locked_until FROM u_lockout WHERE user_id = :uid", ['uid' => $userId]);
		if (!$row || empty($row['locked_until'])) {
			return false;
		}
		// locked_until es timestamp, no necesita strtotime
		return (int) $row['locked_until'] > time();
	}

	/**
	 * @access private
	 * @return void
	 */
	private function logLoginAttempt(?int $userId, string $identifier, bool $success): void {
		$ip = $this->IP->getIPBinary();
		$agent = Html::escape((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

		DB::raw("INSERT INTO u_login_attempts (user_id, identifier, ip, user_agent, success, created_at) VALUES (:uid, :identifier, :ip, :agent, :success, :created)", [
			'uid' => $userId,
			'identifier' => $identifier,
			'ip' => $ip,
			'agent' => $agent,
			'success' => $success ? 1 : 0,
			'created' => time()
		]);
	}

	/**
	 * @access private
	 * @return void
	 */
	private function evaluateLockout(int $userId): void {
		if (!$userId) {
			return;
		}

		$last10min = time() - (10 * 60);
		$lockDuration = 30 * 60;

		$row = DB::fetchRow("SELECT COUNT(*) FROM u_login_attempts WHERE user_id = :uid AND success = 0 AND created_at > :interval AND created_at > (SELECT COALESCE(MAX(locked_until), 0) FROM u_lockout WHERE user_id = :uid)", [
			'uid' => $userId,
			'interval' => $last10min
		]);

		if ((int)$row[0] >= 5) {
			$lockedUntil = time() + $lockDuration;
			DB::raw("INSERT INTO u_lockout (user_id, locked_until) VALUES (:uid, :until) ON DUPLICATE KEY UPDATE locked_until = GREATEST(locked_until, VALUES(locked_until))", [
				'uid' => $userId,
				'until' => $lockedUntil
			]);
		}
	}

	/**
	 * @access private
	 * @return void
	 */
	private function clearLockout(int $userId): void {
		DB::delete('u_lockout', 'user_id = :uid', ['uid' => $userId]);
	}

	/**
	 * @access private
	 * @return int
	 */
	private function getRemainingLockMinutes(int $userId): int {
	    $row = DB::fetch("SELECT locked_until FROM u_lockout WHERE user_id = :uid LIMIT 1", [
	    	'uid' => $userId
	    ]);
	    if (!$row || empty($row['locked_until'])) {
	        return 0;
	    }
	    $remaining = (int) $row['locked_until'] - time();
	    return max(1, (int) ceil($remaining / 60));
	}

	/**
	 * @access public
	 * @return string
	 */
	public function loginUser(string $username = '', string $password = '', bool $remember = false, ?string $redirectTo = null): string {
	    [$username, $password, $remember, $redirectTo] = array_pad(func_get_args(), 4, null);

	    $identifier = mb_strtolower(trim((string) $username));
	    $filter = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

		$user = DB::fetch("SELECT user_id, user_name, user_password, user_activo FROM u_miembros WHERE LOWER(user_$filter) = :safe LIMIT 1", [
			'safe' => $identifier
		]);
		// Si el usuario no existe, registrar intento y salir
	    if (!$user) {
	        $this->logLoginAttempt(null, $identifier, false);
	        return '0: Credenciales inválidas.';
	    }
	    $userId = (int) $user['user_id'];
		// Verificar bloqueo ANTES de validar contraseña
	    if ($this->isLocked($userId)) {
	        $minutes = $this->getRemainingLockMinutes($userId);
	        return "4: Demasiados intentos fallidos. Vuelve a intentar en {$minutes} minutos.";
	    }
	    // Limpiar bloqueo si existe (por si expiró)
    	$this->clearLockout($userId);
		// Verificar contraseña (sin revelar estado)
		$Handler = Container::get(Password::class);
		$success = $Handler->verify($password, $user['user_password']);
		// Registrar intento SIEMPRE
    	$this->logLoginAttempt($userId, $identifier, $success);
		// Si falló → evaluar bloqueo
		if (!$success) {
			$this->evaluateLockout((int) $user['user_id']);
			return '0: Credenciales inválidas.';
		}
		// Usuario inactivo (solo después de credenciales válidas)
		if ((int) $user['user_activo'] === 0) {
			return '3: Debes activar tu cuenta.';
		}
		// Login exitoso
		if ($this->session->update($userId, $remember, true)) {
			// Verificamos si tiene que actualizar
			if($Handler->needsRehash($user['user_password'])) {
				$newhash = $Handler->create($password, $user['user_name']);
				DB::update('u_miembros', ['user_password' => $newhash], 'user_id = :id', [
					'id' => $userId
				]);
			}
			$this->loadUser(true);
			if ($redirectTo !== null) {
				$this->Core->redirectTo($redirectTo);
			}
			return '1: Bien, estás ingresando...';
		}
		return '0: Error al crear la sesión.';
	}

	/**
	 * @name logoutUser
	 * @access public
	 * @param int
	 * @param string
	 * @return bool|void
	 */
	public function logoutUser(int $userID = 0, string $redirectTo = ''): mixed {
		/* BORRAR SESSION */
		$this->session = Container::get(tsSession::class);
		$this->session->read();
		$this->session->destroy();
		$this->session = null;
		/* LIMPIAR VARIABLES */
		$this->info = '';
		$this->is_member = 0;
		# UPDATE
		$lastActive = (int) (time() - (((int) $this->Core->settings['c_last_active'] * 60) * 3));
		DB::update('u_miembros', ['user_lastactive' => $lastActive], 'user_id = :uid', ['uid' => $userID]);
		/* REDERIGIR */
		if ($redirectTo !== NULL) {
			$this->Core->redirectTo($redirectTo);
		}
		return true;
	}

	/**
	 * @name userActivate
	 * @access public
	 * @param int
	 * @param string
	 * @return bool
	 */
	public function userActivate(int $userID, string $pin): bool {
		$userID = (int) $userID;
		// Buscamos si activo o no su cuenta
		$row = DB::fetch("SELECT id, code_hash, expire_at FROM w_activate WHERE user_id = :uid AND type = :type AND used = 0 LIMIT 1", ['uid' => $userID, 'type' => 'activation']);
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
		DB::update('u_miembros', ['user_activo' => 1], 'user_id = :uid', ['uid' => $userID]);
		// Marcamos código como usado
		DB::update('w_activate', ['used' => 1], 'id = :id', ['id' => $row['id']]);
		return true;
	}

	/**
	 * @name getUserBanned
	 * @access public
	 * @return bool|array
	 */
	public function getUserBanned(): bool | array {
		$uid = (int) $this->uid;
		$data = DB::fetch("SELECT * FROM u_suspension WHERE user_id = :uid LIMIT 1", ['uid' => $uid]);
		if (empty($data)) {
			return false;
		}
		$now = time();
		$endsAt = (int) $data['susp_termina'];
		// Suspensión expirada
		if ($endsAt > 0 && $endsAt < $now) {
			DB::update('u_miembros', ['user_baneado' => 0], 'user_id = :uid', ['uid' => $uid]);
			DB::delete('u_suspension', 'user_id = :uid', ['uid' => $uid]);
			return false;
		}
		return $data;
	}

	private function fetchUserField(string $selectField, string $whereField, string | int $value): array {
		$value = is_int($value) ? (int) $value : Html::escape($value);
		return DB::fetch("SELECT $selectField FROM u_miembros WHERE $whereField = :value LIMIT 1", ['value' => $value]) ?: [];
	}

	/**
	 * @name getUserID
	 * @access public
	 * @param string
	 * @return int
	 */
	public function getUserID(string $username = ''): int {
		$row = $this->fetchUserField('user_id', 'user_name', $username);
		return (int) ($row['user_id'] ?? 0);
	}

	/**
	 * @name getUserName
	 * @access public
	 * @param int
	 * @return string
	 */
	public function getUserName(int $userId = 0): string {
		$row = $this->fetchUserField('user_name', 'user_id', $userId);
		return (string) ($row['user_name'] ?? '');
	}

	/**
	 * @name iFollow
	 * @access public
	 * @param int
	 * @return bool
	 */
	public function iFollow(int $userID = 0): bool {
		return DB::exists("SELECT 1 FROM u_follows WHERE f_id = :fid AND f_user = :fuser AND f_type = 1 LIMIT 1", ['fid' => $userID, 'fuser' => $this->uid]);
	}

	private function getUserStatus(int $lastActive, int $onlineLimit, int $inactiveLimit): array {
		return match (true) {
			$lastActive > $onlineLimit 	=> ['t' => 'Online',   'css' => 'online'],
			$lastActive > $inactiveLimit=> ['t' => 'Inactivo', 'css' => 'inactive'],
			default 					=> ['t' => 'Offline',  'css' => 'offline'],
		};
	}

	public function userIsBan(int $uid): bool {
		$data = DB::fetch("SELECT user_baneado FROM u_miembros WHERE user_id = :uid LIMIT 1", ['uid' => $uid]);
		# NO PODEMOS ENVIAR A UN USUARIO BANEADO
		return (int)$data['user_baneado'] === 1;
	}

	/**
	 * @access public
	 * @name setLevel
	 * @param int
	 * @param bool
	 * @return array|bool
	 */
	public function setLevel(int $tsLevel = 0, bool $message = false): array|bool {
		// Los mensajes
		$setMessages = [
			1 => 'Esta página solo es vista por los visitantes.',
			2 => 'Para poder ver esta página debes iniciar sesión.',
			3 => 'Estas en un área restringida solo para moderadores.',
			4 => 'Estas intentando algo no permitido.'
		];
		// Definimos los accesos!
		$conditions = [
			0 => true, // CUALQUIERA
			1 => $this->is_member === 0, // SOLO VISITANTES
			2 => $this->is_member === 1, // SOLO MIEMBROS
			3 => $this->is_admod || $this->permiso('moderacion.panel.acceso'), // SOLO MODERADORES
			4 => $this->is_admod === 1 // SOLO ADMIN
		];

		$tsLevel = $tsLevel ?? 0;
		if($message && !$conditions[$tsLevel]) {
			// Manejo de mensajes de error
			return [
				'titulo' => 'Error',
				'mensaje' => $setMessages[$tsLevel] ?? 'Error desconocido.'
			];
		}
		elseif (isset($conditions[$tsLevel]) && $conditions[$tsLevel]) return true;
	}

	/**
	 * @access public
	 * @name antiFlood
	 * @param bool   $print Finaliza la ejecución si se excede el límite
	 * @param string $type  Tipo de acción (post, comment, vote, etc)
	 * @param string $msg   Mensaje personalizado
	 * @return bool|string
	 */
	public function antiFlood(bool $print = true, string $type = 'post', string $msg = ''): bool|string {
		if (!isset($_SESSION['flood'])) {
			$_SESSION['flood'] = [];
		}
		$now   = time();
		$msg   = $msg ?: 'No puedes realizar tantas acciones en tan poco tiempo.';
		$limit = (int) ($this->permiso('limites.antiflood') ?? 0);
		// Primera vez para este tipo
		if (!isset($_SESSION['flood'][$type])) {
			$_SESSION['flood'][$type] = $now;
			return true;
		}
		$elapsed = $now - $_SESSION['flood'][$type];
		if ($elapsed < $limit) {
			$remaining = $limit - $elapsed;
			$finalMsg  = "0: {$msg} Inténtalo en {$remaining} segundos.";
			if ($print) {
				exit($finalMsg);
			}
			return $finalMsg;
		}
		// Actualizamos timestamp
		$_SESSION['flood'][$type] = $now;
		return true;
	}

	public function getUserBlacklist(): void {
		$IPBAN = $this->IP->executeIP();
		if (!filter_var($IPBAN, FILTER_VALIDATE_IP)) {
			exit('Su ip no se pudo validar.');
		}
		$exists = DB::exists("SELECT 1 FROM w_blacklist WHERE type = 1 AND value = :value LIMIT 1", ['value' => $IPBAN]);
		if ($exists) {
			die('Tu IP fue bloqueada por el administrador/moderador.');
		}
	}

	/**
	 * @name getUsuarios
	 * @access public
	 * @return array
	 */
	public function getUsuarios(): array {
		$filters = [];
		$params = [];
		$paramIndex = 0;
		// --- TIEMPOS ---
		$lastActive = (int) $this->Core->settings['c_last_active'] * 60;
		$now = time();
		$onlineLimit = $now - $lastActive;
		$inactiveLimit = $now - ($lastActive * 2);
		// --- FILTROS ---
		if (($_GET['online'] ?? null) === 'true') {
			$filters[] = "u.user_lastactive > :lastactive";
			$params['lastactive'] = $onlineLimit;
		}
		if (($_GET['avatar'] ?? null) === 'true') {
			$filters[] = "p.p_avatar = :avatar";
			$params['avatar'] = 1;
		}
		if (!empty($_GET['sexo'])) {
			$sexo = Html::escape(trim($_GET['sexo']));
			$filters[] = "p.user_sexo = :sexo";
			$params['sexo'] = $sexo;
		}
		if (!empty($_GET['pais'])) {
			$pais = Html::escape($_GET['pais']);
			$filters[] = "p.user_pais = :pais";
			$params['pais'] = $pais;
		}
		if (!empty($_GET['rango'])) {
			$rango = (int) $_GET['rango'];
			$filters[] = "u.user_rango = :rango";
			$params['rango'] = $rango;
		}
		// --- WHERE BASE ---
		$where = "u.user_activo = 1 AND u.user_baneado = 0";
		if ($filters) {
			$where .= ' AND ' . implode(' AND ', $filters);
		}
		// --- TOTAL ---
		$totalSql = "SELECT COUNT(*) FROM u_miembros u LEFT JOIN u_perfil p ON u.user_id = p.user_id WHERE " . $where;
		$total = (int) DB::value($totalSql, $params);
		$pages = Container::get(Paginator::class)->getPagination($total, 12);
		// --- DATA ---
		$dataSql = "SELECT u.user_id, u.user_name, p.user_pais, p.user_sexo, p.p_avatar, p.p_mensaje, u.user_rango, u.user_puntos, u.user_comentarios, u.user_posts, u.user_lastactive, u.user_baneado, r.r_name, r.r_color, r.r_image FROM u_miembros u LEFT JOIN u_perfil p ON u.user_id = p.user_id LEFT JOIN u_rangos r ON r.rango_id = u.user_rango WHERE " . $where . " ORDER BY u.user_id DESC LIMIT {$pages['limit']}";
		// Añadir los mismos parámetros para la consulta de datos
		$dataRows = DB::fetchAll($dataSql, $params);

		$data = [];
		foreach ($dataRows as $row) {
			$row['status'] = $this->getUserStatus((int) $row['user_lastactive'], $onlineLimit, $inactiveLimit);
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
			'data' => $data,
			'pages' => $pages,
			'total' => $totalActual,
		];
	}

}
