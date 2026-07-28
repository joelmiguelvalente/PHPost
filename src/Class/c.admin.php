<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsAdmin {

	# Cantidad de objeto a mostrar
	CONST MAX_SHOW = 20;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected Email $Email,
		protected Paginator $Paginator,
		protected AdminHelper $AdminHelper
	) {
	}

	/**
	 * Obtenemos a todos los administradores
	*/
	public function getAdmins(): array {
		return DB::fetchAll("SELECT user_id, user_name FROM u_miembros WHERE user_rango = 1 ORDER BY user_id");
	}

	/**
	 * Obtenemos fundación y acutalización
	*/
	public function getInst(): array {
		$data = DB::fetch("SELECT stats_time_foundation as foundation, stats_time_upgrade as upgrade FROM w_stats WHERE stats_no = :stats", ['stats' => 1]);
		return $data;
	}

	/**
	 * Obtenemos las versiones
	*/
	public function getVersions(): array {
		$data['script'] = Config::app('app.version');
		// PHP
		$data['php'] = [
			'version' => PHP_VERSION,
			'sapi' => PHP_SAPI,
			'memory_limit' => ini_get('memory_limit'),
			'upload_max_filesize' => ini_get('upload_max_filesize'),
			'display_errors' => ini_get('display_errors'),
			'timezone' => date_default_timezone_get(),
		];
		// Database
		$row = DB::fetch('SELECT VERSION() AS v');
		$data['database'] = [
			'engine' => 'mysql',
			'version' => $row['v'] ?? null,
		];
		// Server
		$data['server'] = [
			'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
			'os' => PHP_OS_FAMILY,
		];
		// Extensions
		$extensiones = [];
		$all = [
			...Config::app('php.extensions.required'),
			...Config::app('php.extensions.optional')
		];
		foreach($all as $extension) {
			if($extension === 'gd') continue;
			$extensiones[$extension] = extension_loaded($extension);
		}
		$data['extensions'] = [
			'gd' => extension_loaded('gd') ? [
				'enabled' => true,
				'version' => gd_info()['GD Version'] ?? null,
			] : ['enabled' => false],
			...$extensiones
		];
		return $data;
	}

	/**
	 * @access public
	 * @return bool
	*/
	public function saveConfig(string $table = 'w_configuracion', string $id = 'phpost_id'): bool {
		return (DB::update($table, $_POST, "$id = :id", ['id' => 1]) === 0);
	}

	/**
	 * ------------------------------
	 * RANGOS
	 * getAllRangos() :: Obtenemos todos los rangos
	 * setUserRango() :: Cambiamos de rangos a usuarios
	 * ------------------------------
	*/
	public function getAllRangos(): array {
		# RANGOS DISPONIBLES
		return DB::fetchAll('SELECT rango_id, r_name, r_color FROM u_rangos');
	}

	public function setUserRango(int $userId = 0): bool|string {
		# SOLO EL PRIMER ADMIN PUEDE PONER A OTROS ADMINS
		$new_rango = (int)($_POST['new_rango'] ?? 0);
		if ($userId === $this->User->uid) return 'No puedes cambiarte el rango a ti mismo';
		elseif ($this->User->uid !== 1 && $new_rango === 1) return 'Solo el primer Administrador puede crear más administradores principales';
		else {
			return (!DB::update('u_miembros', ['user_rango' => $new_rango], "user_id = :uid", ['uid' => $userId]));
		}
	}

	/**
	 * ------------------------------
	 * SESIONES
	 * getSessions() :: Obtenemos todas las sesiones
	 * delSession() :: Eliminamos la sesion por "session_id"
	 * ------------------------------
	*/
	public function getSessions(): array {
		$limit = $this->Paginator->setPageLimit(self::MAX_SHOW, true);
		# Datos
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, s.* FROM u_sessions AS s LEFT JOIN u_miembros AS u ON s.session_user_id = u.user_id ORDER BY s.session_time DESC LIMIT {$limit}");
		# Paginamos
		$total = DB::numRows("SELECT COUNT(*) FROM u_sessions");
		$data['pages'] = $this->Paginator->pageIndex("/admin/sesiones?", $_GET['s'] ?? 0, (int)$total, self::MAX_SHOW);
		# Retornamos datos
		return $data;
	}

	public function delSession(): string {
		# Obtenemos la session_id
		$session = Html::escape($_POST['session_id']);
		$param = ['session' => $session];
		if (DB::exists("SELECT session_id FROM u_sessions WHERE session_id = :session LIMIT 1", $param)) {
			if (DB::delete("u_sessions", "session_id = :session", $param)) {
				return '1: Eliminado';
			}
		} else return '0: No existe esa sesión';
	}

	/**
	 * ------------------------------
	 * NICKS
	 * getChangeNicks() :: Obtenemos todos los nicks / Cambios realizados
	 * ChangeNick_o_no() :: Aprobar/Desaprobar cambio
	 * ------------------------------
	*/
	public function getChangeNicks(string $hecho = ''): array {
		# Limite
		$limit = $this->Paginator->setPageLimit(self::MAX_SHOW, true);
		# Datos
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, n.* FROM u_nicks AS n LEFT JOIN u_miembros AS u ON n.user_id = u.user_id WHERE estado = :status ORDER BY n.time DESC LIMIT {$limit}", ['status' => $hecho]);
		# Paginacion
		$total = DB::numRows("SELECT COUNT(*) FROM u_nicks WHERE estado = :status", ['status' => $hecho]);
		$data['pages'] = $this->Paginator->pageIndex("/admin/nicks?", $_GET['s'] ?? 0, (int)$total, self::MAX_SHOW);
		# Retornamos datos
		return $data;
	}

	public function ChangeNick_o_no(): string {
		global $tsMonitor;
		# ID del nick
		$nid = (int)($_POST['nid'] ?? 0);
		# Datos
		$user = DB::fetch("SELECT user_id, user_email, name_1, name_2 FROM u_nicks WHERE id = $nid LIMIT 1") ?? [];
		[
			'user_id' => $uid,
			'user_email' => $email,
			'name_1' => $name1,
			'name_2' => $name2
		] = $user;
		$title = $this->Core->settings['titulo'];
		# Aprobamos
		if (isset($_POST['accion']) && trim($_POST['accion']) === 'accepted') {
			DB::update('u_miembros', ['user_name' => $name2], 'user_id = :uid', ['uid' => $uid]);
			DB::decrement('u_miembros', 'user_name_changes', 'user_id = :uid', ['uid' => $uid]);
			//
			DB::update('u_nicks', ['estado' => 'accepted'], 'id = :id', ['id' => $nid]);
			# Enviamos un aviso
			$aviso = "Hola <strong>$name1</strong>,\n\n Le informo que desde este momento su nombre de acceso será <strong>$name2</strong> . Hasta pronto.";
			$tsMonitor->setAviso($uid, 'Cambio realizado', $aviso, 4);
			//ENVIAMOS CORREO
			$subject = "$name1, su petición de cambio ha sido aceptada";
			$body = "Hola $name1:\nLe enviamos este email para informarle que su petición de cambio de nick ha sido aceptada.<br>Desde este momento, podrá acceder en $title con el nombre de usuario $name2. <br /><hr>El staff de <strong>$title</strong>";
		# Denegamos
		} elseif (isset($_POST['accion']) && trim($_POST['accion']) === 'rejected') {
			DB::decrement('u_miembros', 'user_name_changes', 'user_id = :uid', ['uid' => $uid]);
			//
			DB::update('u_nicks', ['estado' => 'rejected'], 'id = :id', ['id' => $nid]);
			# Enviamos un aviso
			$aviso = "Hola <strong>$name1</strong>,\n\n Lamento informarle que su petición de cambio de nick a <strong>$name2</strong> , ha sido denegada.";
			$tsMonitor->setAviso($uid, 'Cambio realizado', $aviso, 3);
			//ENVIAMOS CORREO
			$subject = "$name1, su petición de cambio ha sido denegada";
			$body = "Hola $name1:\nLe enviamos este email para informarle que su petición de cambio de nick ha sido denegada.\n<hr>El staff de <strong>$title</strong>'";
		} else return '0: Mijo, ve de paseo';

		$this->Email->send($email, 'confirmar', $body) OR die('0: Hubo un error al intentar procesar lo solicitado');

		return "1: Hemos enviado un correo a <strong>$email</strong> con la decisión tomada. También le hemos enviado un aviso al usuario.";
	}

	public function getAdmin(string $type = 'posts'): ?array {
		return match($type) {
			'posts' => $this->getAdminPosts(),
			'fotos' => $this->getAdminFotos(),
			default => null
		};
	}

	private function getAdminPosts(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		$order = trim($_GET['order'] ?? '');
		$asc = trim($_GET['modo'] ?? '');
		$orden = match($order) {
			'estado' => 'p.post_status',
			'ip' => 'p.post_ip',
			default => 'p.post_id'
		};
		$upper = strtoupper($asc);
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, c.c_nombre, c.c_seo, c.c_img, p.* FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_id > 0 ORDER BY $orden $upper LIMIT $limit");

		// PAGINAS
		$total = DB::numRows('SELECT COUNT(*) FROM p_posts WHERE post_id > 0');
		$data['pages'] = $this->Paginator->pageIndex("/admin/posts?order=$order&modo=$asc", (int)($_GET['s'] ?? 0), (int)$total, (int)$max);
		//
		return $data;
	}

	public function getAdminFotos(): array {
		$max = 15; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		//
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, f.* FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id > 0 ORDER BY f.foto_id DESC LIMIT $limit");
		// PAGINAS
		$total = DB::numRows("SELECT COUNT(*) FROM f_fotos WHERE foto_id > 0");
		$data['pages'] = $this->Paginator->pageIndex("/admin/fotos?", (int)($_GET['s'] ?? 0), (int)$total, (int)$max);
		//
		return $data;
	}

	public function DelFoto(): string {
		$foto = (int)($_POST['foto_id'] ?? 0);
		if (!DB::exists("SELECT 1 FROM f_fotos WHERE foto_id = $foto")) {
			return '0: La foto no existe';
		}
		if (!DB::delete('f_fotos', 'foto_id = :id', ['id' => $foto])) {
			return '0: La foto no se pudo eliminar';
		}
		return '1: Foto eliminada';
	}

	public function setOpenClosedFoto(): string {
		$fid = (int)($_POST['fid'] ?? 0);
		$data = DB::fetch("SELECT f_closed FROM f_fotos WHERE foto_id = :id", ['id' => $fid]);
		// COMPROBAMOS
		$active = ((int)$data['f_closed'] === 1) ? 0 : 1;
		if(!DB::update('f_fotos', ['f_closed' => $active], 'foto_id = :id', ['id' => $fid])) {
			return '0: Ocurri&oacute, un error';
		}
		return ($active === 1) ? '2: Comentarios abiertos' : '1: Comentarios cerrados.';
	}

	public function setShowHideFoto(): string {
		$fid = (int)($_POST['fid'] ?? 0);
		$data = DB::fetch("SELECT f_status FROM f_fotos WHERE foto_id = :id", ['id' => $fid]);
		// COMPROBAMOS
		$active = ((int)$data['f_status'] === 1) ? 0 : 1;
		if(!DB::update('f_fotos', ['f_status' => $active], 'foto_id = :id', ['id' => $fid])) {
			return '0: Ocurri&oacute, un error';
		}
		return ($active === 1) ? '2: Foto rehabilitada' : '1: Foto deshabilitada.';
	}

}
