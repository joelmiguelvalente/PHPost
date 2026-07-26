<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

if ( ! defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsModeracion {

	private string $myIP;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected tsMonitor $Monitor,
		protected Paginator $Paginator
	) {
		$this->myIP = $this->IP->getIPBinary();
	}

	# Obtenemos todos los moderadores
	public function getMods(): array {
		return DB::fetchAll("SELECT user_id, user_name, user_email FROM u_miembros WHERE user_rango = 2 ORDER BY user_id");
	}

	# Obtenemos las denuncias posts|fotos|mps|users
	public function getDenuncias(string $type = 'posts'): array {
		# Tipo de denuncia
		switch ($type) {
			case 'posts':
				$sentencia = 'SUM(r.d_total) AS total, p.post_id, p.post_title, p.post_status, c.c_nombre, c.c_seo, c.c_img FROM w_denuncias AS r LEFT JOIN p_posts AS p ON r.obj_id = p.post_id LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE r.d_type = :param AND p.post_status != :status';
				$param = ['param' => 'post', 'status' => 'eliminado'];
			break;
			case 'fotos':
				$sentencia = 'SUM(r.d_total) AS total,  f.foto_id, f.f_title, f.f_status, u.user_id, u.user_name FROM w_denuncias AS r LEFT JOIN f_fotos AS f ON r.obj_id = f.foto_id LEFT JOIN u_miembros AS u ON f.f_user = u.user_id  WHERE d_type = :param AND f.f_status < 2 GROUP BY r.obj_id';
				$param = ['param' => 'foto'];
			break;
			case 'users':
				$sentencia = 'SUM(d_total) AS total, u.user_name FROM w_denuncias AS r LEFT JOIN u_miembros AS u ON r.obj_id = u.user_id WHERE d_type = :param AND u.user_baneado = 0';
				$param = ['param' => 'usuario'];
			break;
			case 'mps':
				$sentencia = 'm.mp_id, m.mp_to, m.mp_from, m.mp_subject, m.mp_preview, m.mp_date FROM w_denuncias AS r LEFT JOIN u_mensajes AS m ON r.obj_id = m.mp_id WHERE d_type = :param';
				$param = ['param' => 'mensaje'];
			break;
		}
		$data = DB::fetchAll("SELECT r.obj_id, $sentencia GROUP BY r.obj_id ORDER BY total DESC, MAX(r.d_date) DESC", $param);
		return $data;
	}

	# Obtener la denuncia
	public function getDenuncia(string $type = 'posts'): array {
		// VARIABLES
		$obj = Html::escape($_GET['obj']);
		// TIPO DE DENUNCIA
		$d_type = match($type) {
			'fotos' => 'foto',
			'users' => 'usuario',
			'mps' => 'mensaje',
			default => 'post'
		};
		$query = match($type) {
			'fotos' => 'SELECT f.foto_id, f.f_title, f.f_status, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id';
			'users' => 'SELECT user_id, user_name FROM u_miembros WHERE user_id';
			'mps' => 'SELECT user_id, user_name FROM u_miembros WHERE user_id';
			default => 'SELECT p.post_id, p.post_title, p.post_status, c.c_nombre, c.c_seo, c.c_img, u.user_name FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_id';
		};
		// CARGAMOS AL ARRAY...
		$data['data'] = DB::fetch($query . ' = :obj LIMIT 1', ['obj' => $obj]);
		// DENUNCIAS
		$data['denun'] = DB::fetchAll("SELECT d.*, u.user_id, u.user_name FROM w_denuncias AS d LEFT JOIN u_miembros AS u ON d.d_user = u.user_id WHERE d.obj_id = :obj AND d.d_type = :d_type", ['obj' => $obj, 'd_type' => $d_type]);
		//
		return $data;
	}

	# Obtenemos el contenido
	public function getContenido(): array {
		$texto = Html::escape($_GET['texto']);
		$tipo = (int)($_GET['t'] ?? 0);
		$metodo = (int)($_GET['m'] ?? 0);
		if (empty($texto) || empty($texto)) {
			Container::get(Redirector::class, [$this->settings['url']])->to('/moderacion/buscador');
		}
		$met = ($metodo === 1 ? 'LIKE' : '=') . ' :texto';
		$params = ['texto' => (($metodo === 1) ? "%{$texto}%" : $texto)];

		# MURO
		$data['muro'] = DB::fetchAll('SELECT m.pub_id, m.p_user, m.p_user_pub, m.p_ip, m.p_date, m.p_body, u.user_id, u.user_name FROM u_muro AS m LEFT JOIN u_miembros AS u ON m.p_user_pub = u.user_id WHERE '.($tipo === 1 ? 'm.p_ip' : 'm.p_body').' '.$met, $params);
		# USUARIOS
		$data['usuarios'] = DB::fetchAll('SELECT user_id, user_name, user_last_ip, user_lastlogin, user_lastactive FROM u_miembros WHERE '.($tipo == 1 ? 'user_last_ip' : 'user_name').' '.$met.' ORDER BY user_lastactive DESC', $params);
		# POSTS
		$data['posts'] = DB::fetchAll('SELECT p.post_id, p.post_user, p.post_title, p.post_date, p.post_ip, u.user_name, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE ' .($tipo == 1 ? 'p.post_ip ' . $met . '' : 'p.post_title ' . $met . ' OR p.post_body '.$met), $params);
		# FOTOS
		$data['fotos'] = DB::fetchAll('SELECT f.foto_id, f.f_title, f.f_user, f.f_date, f.f_ip, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE ' .($tipo == 1 ? 'f.f_ip ' . $met . '' : 'f.f_title ' . $met .' OR f.f_description ' . $met), $params);
		# POSTS COMENTARIOS
		$data['p_comentarios'] = DB::fetchAll('SELECT u.user_id, u.user_name, c.* FROM p_comentarios AS c LEFT JOIN u_miembros AS u ON u.user_id = c.c_user WHERE ' .($tipo == 1 ? 'c.c_ip ' . $met . '' : 'c.c_user ' . $met . ' OR c.c_body ' . $met), $params);
		# FOTOS COMENTARIOS
		$data['f_comentarios'] = DB::fetchAll('SELECT u.user_id, u.user_name, f.* , c.* FROM f_comentarios AS c LEFT JOIN u_miembros AS u ON u.user_id = c.c_user LEFT JOIN f_fotos AS f ON f.foto_id = c.c_foto_id WHERE ' .($tipo == 1 ? 'c.c_ip ' . $met . '' : 'c.c_user ' . $met . ' OR c.c_body ' . $met), $params);
		# COUNTS
		$totales = [
			'm_total' => 'muro',
			'u_total' => 'usuarios',
			'p_total' => 'posts',
			'f_total' => 'fotos',
			'c_p_total' => 'p_comentarios',
			'c_f_total' => 'f_comentarios'
		];
		foreach($totales as $key => $name) {
			$data[$key] = count($data[$name]);
		}
		//
		$data['contenido'] = $texto;
		$data['metodo'] = $metodo;
		$data['tipo'] = $tipo;
		//
		return $data;
	}

	# Vista preliminar
	public function getPreview(int $pid = 0): array {
		$data = DB::fetch('SELECT post_title, post_body FROM p_posts WHERE post_id = :pid LIMIT 1', [
			'pid' => $pid
		]);
		return [
			'titulo' => $data['post_title'],
			'cuerpo' => $this->Core->parseBBCode($data['post_body'])
		];
	}

	private function isAdmodPermission(string $permission = ''): string {
		if(!$this->User->is_admod || !$this->User->permiso($permission)) {
			return '0: No continúe por aquí.';
		}
	}

	/**
	 * @name rebootPost()
	 * @access public
	 * @param int
	 * @return string
	*/
	public function rebootPost(int $pid = 0): string {
		$this->isAdmodPermission('moderacion.denuncias.posts');
		// PRIMERO COMPROBAMOS SI ESTÁ OCULTO
		$datos = DB::fetch('SELECT post_id, post_status FROM p_posts WHERE post_id = :pid LIMIT 1', [
			'pid' => $pid
		]);
		if ($datos['post_status'] === 'revision') {
			if (!DB::delete('w_historial', '`pofid` = :pid AND `type` = :type AND `action` = :action', [
				'pid' => $pid,
				'type' => 1,
				'action' => 3
			])) return '0: No se pudo restaurar el post.';
		} else {
			//BORRAMOS LA DENUNCIAS
			if (!DB::delete('w_denuncias', '`obj_id` = :pid AND `d_type` = :d_type', [
				'pid' => $pid,
				'd_type' => 'post'
			])) return '0: No se pudo restaurar el post.';
		}
		// REGRESAMOS EL POST
		if (!DB::update('p_posts', ['post_status' => 'publicado'], 'post_id = :id', ['id' => $pid])) {
			return '0: No se pudo restaurar el post.';
		}
		DB::increment('w_stats', 'stats_posts', 'stats_no = :stats_no', ['stats_no' => 1]);
		return '1: El post ha sido restaurado.';
	}

	/**
	 * @name OcultarPost()
	 * @access public
	 * @param int
	 * @return string
	*/
	public function OcultarPost(int $pid = 0, ?string $razon): string {
		$this->isAdmodPermission('moderacion.posts.ocultar');
		if (DB::exists('SELECT 1 FROM p_posts WHERE post_id = :pid AND post_status = :status', [
			'pid' => $pid,
			'status' => 'revision'
		])) {
			return '0: El post... ya está oculto.';
		}
		if (!DB::update('p_posts', ['post_status' => 'revision'], 'post_id = :id', ['id' => $pid])) {
			return '0: No se pudo ocultar el post.';
		}
		$historial = DB::insert('w_historial', [
			'pofid' => $pid,
			'action' => 3,
			'type' => 1,
			'mod' => $this->User->uid,
			'reason' => $razon,
			'date' => time(),
			'mod_ip' => $this->myIP
		]);
		if (!$historial) {
			return '0: No se pudo registrar la acción.';
		}
		DB::decrement('w_stats', 'stats_posts', 'stats_no = :stats_no', ['stats_no' => 1]);
		return '1: El post ha sido ocultado.';
	}

	/**
	 * @name rebootMps()
	 * @access public
	 * @param int
	 * @return string
	*/
	public function rebootMps(int $mid = 0): string {
		$this->isAdmodPermission('moderacion.denuncias.cancelar.mensajes');
		$rows = DB::exists('SELECT 1 FROM w_denuncias WHERE obj_id = :mid AND `d_type` = :d_type', [
			'mid' => $mid,
			'd_type' => 'mensaje'
		]);
		//BORRAMOS LA DENUNCIA
		if (!DB::delete('w_denuncias', 'obj_id = :mid AND d_type = :type', [
			'mid' => $mid,
			'type' => 'mensaje'
		])) {
			return '0: No se pudo eliminar la denuncia';
		}
		DB::update('u_mensajes', ['mp_del_to' => 0, 'mp_del_from' => 0], 'mp_id = :id', ['id' => $mid]);
		return '1: Denuncia eliminada';
	}

	/**
	 * @name rebootFoto()
	 * @access public
	 * @param int
	 * @return string
	*/
	public function rebootFoto(int $fid = 0): string {
		$this->isAdmodPermission('moderacion.denuncias.cancelar.fotos');
		$rows = DB::exists('SELECT 1 FROM w_denuncias WHERE obj_id = :fid AND `d_type` = :d_type', [
			'fid' => $fid,
			'd_type' => 'foto'
		]);
		//BORRAMOS LA DENUNCIA
		if (!DB::delete('w_denuncias', 'obj_id = :fid AND d_type = :type', [
			'fid' => $mid,
			'type' => 'foto'
		])) {
			return '0: No se pudo eliminar la denuncia';
		}
		DB::update('f_fotos', ['f_status' => 0], 'foto_id = :id', ['id' => $fid]);
		return '1: Denuncia eliminada';
	}

	/**
	 * @name deletePost($pid)
	 * @access public
	 * @param int
	 * @return string
	*/
	public function deletePost(int $pid = 0): string {
		$this->isAdmodPermission('moderacion.posts.eliminar');
		// RAZON
		$razon = Html::escape($_POST['razon']);
		$razon_desc = Html::escape($_POST['razon_desc']);
		$razon_db = ($razon !== 13) ? $razon : $razon_desc;
		if(!DB::update('p_posts', ['post_status' => 'eliminado'], 'pid = :id', ['id' => $pid])) {
			return '0: El post NO pudo ser eliminado.';
		}
		// ELIMINAR DENUNCIAS
		DB::delete('w_denuncias', '`obj_id` = :pid AND `d_type` = :d_type', [
			'pid' => $pid, 'd_type' => 'post'
		]);
		// ENVIAR AVISO
		$data = DB::fetch('SELECT p.post_user, p.post_title, p.post_body, p.post_tags, p.post_category, u.user_name, u.user_email FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_id = :pid LIMIT 1', ['pid' => $pid]);
		// RAZON
		if (is_numeric($razon_db)) {
			$tsDenuncias = require_once TS_EXTRAS . "/Denuncias.php";
			$razon_db = $tsDenuncias['posts'][$razon_db];
		}
		DB::decrement('w_stats', 'stats_posts', 'stats_no = :stats_no', ['stats_no' => 1]);
		//AGREGAMOS A BORRADORES si se ha marcado la casilla
		if ((string)($_POST['send_b'] ?? '') === 'yes') {
			DB::insert('p_posts', [
				'post_user' => $data['post_user'],
				'post_date' => time(),
				'post_title' => $data['post_title'],
				'post_body' => $data['post_body'],
				'post_tags' => $data['post_tags'],
				'post_category' => $data['post_category'],
				'post_status' => 'borrador',
				'post_causa' => $razon_db
			]);
			// AVISO
			$aviso = "Hola <b>{$data['user_name']}</b>\n\n
			Lamento contarte que tu post titulado <b>{$data['post_title']}</b> ha sido eliminado.\n\n
			Causa: <b>{$razon_db}</b>\n\n
			Te recomendamos leer el <a href=\"{$this->Core->settings['url']}/pages/protocolo/\">Protocolo</a> para evitar futuras sanciones.\n\n
			Muchas gracias por entender!";
			$status = $this->Monitor->setAviso($data['post_user'], 'Post eliminado', $aviso, 1);
			//mail($data['user_email'], 'Post eliminado', $aviso);
			$status = $this->setHistory('borrar', 'post', $pid);
			if ($status) return '1: El post ha sido eliminado.';
		}
	}

	/**
	 * @name deleteMps($pid)
	 * @access public
	 * @param int
	 * @return string
	*/
	public function deleteMps(int $mid = 0): string {
		$this->isAdmodPermission('moderacion.denuncias.aceptar_mensajes');
		// ENVIAR AVISO
		$data = DB::fetch('SELECT m.mp_from, m.mp_subject, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_id = :mid LIMIT 1', [
			'mid' => $mid
		]);
		if (!$data) {
			return '0: El mensaje NO pudo ser eliminado.';
		}
		// AVISO
		$aviso = 'Hola <b>' . $data['user_name'] . "</b>\n\n Le informo de que el mensaje privado <b>" . $data['mp_subject'] . "</b> ha sido eliminado.\n\n Te recomendamos leer el <a href=\"" . $this->Core->settings['url'] . "/pages/protocolo/\">Protocolo</a> para evitar futuras sanciones.\n\n Muchas gracias por entender!";
		$status = $this->Monitor->setAviso($data['mp_from'], 'Mensaje eliminado', $aviso, 1);
		// ELIMINAR DENUNCIAS
		DB::delete('w_denuncias', 'obj_id = :mid AND d_type = :d_type', [
			'mid' => $mid,
			'd_type' => 'mensaje'
		]);
		//LOS MPS SE ELIMINARAN DE LA LISTA DE MPS DEL USUARIO, PERO NO SE BORRARÁN.
		$del = ['mid' => $mid];
		// Si quiere elimninarlos en vez de ocultarlos use "$onlyUpdate = false;"
		$onlyUpdate = true;
		if($onlyUpdate) {
			DB::update('u_mensajes', ['mp_del_to' => 1, 'mp_del_from' => 1], 'mp_id = :mid', $del);
		} else {
			DB::delete('u_respuestas', 'mp_id = :mid', $del);
			DB::delete('u_mensajes', 'mp_id = :mid', $del);
		}
		// ELIMINAR MPS (, elimine # y comente la anterior "UPDATE" con #)
		return '1: El mensaje ha sido eliminado.';
	}

	public function deleteFoto(int $fid = 0): string {
		$this->isAdmodPermission('moderacion.fotos.eliminar');
		// RAZON
		$razon = Html::escape($_POST['razon']);
		$razon_desc = Html::escape($_POST['razon_desc']);
		$razon_db = ($razon !== 8) ? $razon : $razon_desc;
		//
		if (!DB::update('f_fotos', ['f_status' => 2], 'foto_id = :id', ['id' => $fid])) {
			return '0: La foto NO pudo ser eliminada.';
		}
		DB::decrement('w_stats', 'stats_fotos', 'stats_no = :stats_no', ['stats_no' => 1]);
		// ENVIAR AVISO
		$data = DB::fetch('SELECT f.f_user, f.f_title, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :fid LIMIT 1', [
			'fid' => $fid
		]);
		if ((int)$data['f_user'] !== $this->User->uid) {
			// RAZON
			if (is_numeric($razon_db)) {
				$tsDenuncias = require_once TS_EXTRAS . "/Denuncias.php";
				$razon_db = $tsDenuncias['fotos'][$razon_db];
			}
			// AVISO
			$aviso = "Hola <b>{$data['user_name']}</b>\n\n
			Lamento contarte que tu foto titulada <b>{$data['f_title']} </b> ha sido eliminada.\n\n
			Causa: <b>{$razon_db}</b>\n\n
			Te recomendamos leer el <a href=\"{$this->Core->settings['url']}/pages/protocolo/\">Protocolo</a> para evitar futuras sanciones.\n\n
			Muchas gracias por entender!";
			$status = $this->Monitor->setAviso($data['f_user'], 'Foto eliminada', $aviso, 1);
		}
		// ELIMINAR DENUNCIAS
		DB::delete('w_denuncias', 'obj_id = :fid AND d_type = :d_type', [
			'fid' => $fid,
			'd_type' => 'foto'
		]);
		$this->setHistory('borrar', 'foto', $fid);
		return '1: La foto ha sido eliminada.';
	}

	/**
	 * @name setSticky
	 * @access public
	 * @param $post_id
	 * @return string
	 * @info Pone sticky un post
	*/
	public function setSticky(int $post_id = 0): string {
		$this->isAdmodPermission('moderacion.posts.fijar');
		$param = ['pid' => $post_id];
		$data = DB::fetch('SELECT post_sticky FROM p_posts WHERE post_id = :pid LIMIT 1', $param);
		$isSticky = ((int)$data['post_sticky'] === 1);
		DB::update('p_posts', ['post_sticky' => ($isSticky ? 0 : 1)], 'post_id = :pid', $param);
		return '1: El post fue ' . ($isSticky ? 'quitado de la home.' : 'puesto como fijo en la web.');
	}

	/**
	 * @name setOpenClosed
	 * @access public
	 * @param $post_id
	 * @return string
	 * @info Abre o Cierra un post.
	*/
	public function setOpenClosed(int $post_id = 0): string {
		$this->isAdmodPermission('moderacion.posts.abrir_cerrar');
		//
		$param = ['pid' => $post_id];
		$data = DB::fetch('SELECT post_block_comments FROM p_posts WHERE post_id = :pid LIMIT 1', $param);
		$isBlocked = ((int)$data['post_block_comments'] === 1);
		DB::update('p_posts', ['post_block_comments' => ($isBlocked ? 0 : 1)], 'post_id = :pid', $param);
		return '1: El post fue ' . ($isBlocked ? 'abierto.' : 'cerrado.');
	}

	/**
	 * @name getSuspendidos
	 * @access public
	 * @param
	 * @return array
	 * @info OBTIENE LOS USUARIOS SUSPENDIDOS
	*/
	public function getSuspendidos(): array {
		$this->isAdmodPermission('moderacion.usuarios.ver_baneados');

		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		//FILTROS
		$order = match((string)$_GET['o']) {
			'inicio' => 's.susp_date',
			'fin' => 's.susp_termina',
			'mod' => 's.susp_mod',
			default => 's.susp_id'
		};
		$met = ((string)$_GET['m'] === 'a') ? 'ASC' : 'DESC';
		$data['bans'] = DB::fetchAll("SELECT s.*, u.user_name FROM u_suspension AS s LEFT JOIN u_miembros AS u ON s.user_id = u.user_id WHERE 1 ORDER BY {$order} {$met} LIMIT {$limit}");
		// PAGINAS
		$total = DB::value('SELECT COUNT(*) FROM u_suspension WHERE user_id > :val', ['val' => 0]);
		$data['pages'] = $this->Paginator->pageIndex("/moderacion/banusers?o={$_GET['o']}&m={$_GET['m']}", $_GET['s'] ?? 0, (int)$total, $max);
		//
		return $data;
	}
	/**
	 * @name banUser
	 * @access public
	 * @param int
	 * @return string
	 * @info PARA SUSPENDER A UN USUARIO
	*/
	public function banUser(int $user_id = 0): string {
		# LOCALES
		$b_time = (int)($_POST['b_time'] ?? 0);
		$b_cant = (int)($_POST['b_cant'] ?? 1);
		$b_causa = Html::escape($_POST['b_causa']);
		$b_times = [0, 1, 3600, 86400]; // HORA, DIA
		# NO INTENTO BANEARME?
		if ($user_id === $this->User->uid) {
			return '0: Si quieres abandonar la web, mándale un mp al admin';
		}
		# NO ES HORARIO VÁLIDO?
		if ($b_cant < 1 || !is_numeric($b_cant)) {
			return '0: Debe introducir en números una cantidad superior a 60 minutos (1)';
		}
		# COMPROBAMOS RANGOS
		$data = DB::fetch('SELECT `user_rango`, `user_baneado` FROM `u_miembros` WHERE `user_id` = :uid LIMIT 1', ['uid' => (int)$user_id]);
		if ((int)$data['user_baneado'] === 1) {
			return '0: Este usuario ya fue suspendido';
		}
		# Y SI QUIERO SUSPENDER A UN ADMIN o MOD?
		if (($this->User->is_admod < $data['user_rango'] && $this->User->is_admod > 0) ||
			($this->User->permiso('moderacion.usuarios.suspender') && $data['user_rango'] >= 2)) {
			// TIEMPO
			$ahora = time();
			$termina = ($b_cant * $b_times[$b_time]);
			$termina = ($b_time >= 2) ? ($ahora + $termina) : $termina;
			// ACTUALIZAMOS
			DB::update('u_miembros', ['user_baneado' => 1], 'user_id = :uid', ['uid' => $user_id]);
			if (DB::insert('u_suspension', [
				'user_id' => $user_id,
				'susp_causa' => $b_causa,
				'susp_date' => $ahora,
				'susp_termina' => $termina,
				'susp_mod' => $this->User->uid,
				'susp_ip' => $this->myIP
			])) {
				// ELIMINAR DENUNCIAS
				DB::delete('w_denuncias', '`obj_id` = :uid AND `d_type` = :d_type', ['uid' => (int)$user_id, 'd_type' => 'usuario']);
				// RESTAR USUARIO EN ESTADÍSTICAS
				DB::decrement('w_stats', 'stats_miembros', 'stats_no = :stats_no', ['stats_no' => 1]);
				// RETORNAR
				if ($b_time < 2) {
					$rdate = ($b_time == 0) ? 'Indefinidamente' : 'Permanentemente';
				} else $rdate = '</b>hasta el <b>' . date("d/m/Y H:i:s", $termina);
				//
				return '1: Usuario suspendido <b>' . $rdate . '</b>';
			}
			return '0: El usuario no pudo ser suspendido';
		} else return '0: No puedes suspender a usuarios de tu mismo rango o superior al tuyo.';
	}

	/**
	 * @name rebootUser
	 * @access public
	 * @param int
	 * @return string
	 * @info ELIMINA LAS DENUNCIAS DEL USUARIO O LE QUITA UNA SUSPENSION
	*/
	public function rebootUser(int $uid = 0, string $type = 'unban'): string {
		$this->isAdmodPermission('moderacion.usuarios.desbanear');
		$param = ['uid' => $uid];
		# PRIMERO BORRAMOS LA DENUNCIAS
		DB::delete('w_denuncias', 'obj_id = :uid AND d_type = :d_type', [
			...$param,
			'd_type' => 'usuario'
		]);
		// HAY QUE QUITAR LA SUSPENSION?
		if ($type === 'unban') {
			$data = DB::fetch('SELECT susp_mod FROM u_suspension WHERE user_id = :uid', $param);
			if (empty($data)) return '0: El usuario no está suspendido.';
			//
			if (!$this->User->is_admod || (int)$data['susp_mod'] !== $this->User->uid) {
				return '0: Sólo puedes quitar la suspensión a los usuarios que tú suspendiste.';
			}
			DB::delete('u_suspension', '`user_id` = :uid', $param);
			DB::update('u_miembros', ['user_baneado' => '0'], 'user_id = :uid', $param);
			DB::increment('w_stats', 'stats_miembros', 'stats_no = :stats_no', ['stats_no' => 1]);
			return '1: El usuario fue reactivado y ahora podrá seguir activo en la web.';
		}
		//
		return '1: Las denuncias fueron eliminadas.';
	}

	/**
	 * Registra una acción en el historial de moderación.
	 *
	 * @param string $action  'borrar' | 'editar'
	 * @param string $type    'post' | 'foto'
	 * @param int|array $data ID del objeto (borrar) o array con datos del post (editar)
	 * @return bool
	 */
	public function setHistory(string $action, string $type, int|array $data): bool {
		return match ($type) {
			'post' => $this->setHistoryPost($action, $data),
			'foto' => $this->setHistoryFoto($action, $data),
			default => false,
		};
	}

	private function setHistoryPost(string $action, int|array $data): bool {
		return match ($action) {
			'borrar' => $this->handlePostBorrar((int) $data),
			'editar' => $this->handlePostEditar($data),
			default => false,
		};
	}

	private function handlePostBorrar(int $pid): bool {
		$razon = Html::escape($_POST['razon']);
		$razon_desc = Html::escape($_POST['razon_desc']);
		$razon_db = ($razon != 13) ? $razon : $razon_desc;

		$post = DB::fetch('SELECT post_id, post_user FROM p_posts WHERE post_id = :pid LIMIT 1', ['pid' => $pid]);
		if (!$post || $post['post_user'] === $this->User->uid) {
			return false;
		}

		$this->insertHistory((int) $post['post_id'], '2', '1', $razon_db);
		return true;
	}

	private function handlePostEditar(array $data): bool {
		$aviso = 'Hola <b>' . $this->User->getUserName($data['autor']) . "</b>\n\n Te informo que tu post <b>" . $data['title'] . "</b> ha sido editado por <a href=\"#\" class=\"hovercard\" uid=\"" . $this->User->uid . "\">" . $this->User->nick . "</a>\n\n Causa: <b>" . $data['razon'] . "</b>\n\n \n\n Te recomendamos leer el <a href=\"" . $this->Core->settings['url'] . "/pages/protocolo/\">protocolo</a> para evitar futuras sanciones.\n\n Muchas gracias por entender!";

		$this->Monitor->setAviso($data['autor'], 'Post editado', $aviso, 2);
		$this->insertHistory((int) $data['post_id'], '1', '1', $data['razon']);
		return true;
	}

	private function setHistoryFoto(string $action, int $fid): bool {
		if ($action !== 'borrar') {
			return false;
		}

		$razon = Html::escape($_POST['razon']);
		$razon_desc = Html::escape($_POST['razon_desc']);
		$razon_db = ($razon != 8) ? $razon : $razon_desc;

		$foto = DB::fetch('SELECT foto_id FROM f_fotos WHERE foto_id = :fid LIMIT 1', ['fid' => $fid]);
		if (!$foto) {
			return false;
		}

		$this->insertHistory((int) $foto['foto_id'], '2', '2', Html::escape($razon_db));
		return true;
	}

	private function insertHistory(int $pofid, string $action, string $type, string $reason): void {
		DB::insert('w_historial', [
			'pofid' => $pofid,
			'action' => $action,
			'type' => $type,
			'mod' => $this->User->uid,
			'reason' => $reason,
			'date' => time(),
			'mod_ip' => $this->myIP,
		]);
	}

	private function getReazon(array &$data, array $rows, string $type = 'posts'): void {
		// DENUNCIAS
		$tsDenuncias = require_once TS_EXTRAS . "/Denuncias.php";
		//
		foreach ($rows as $row) {
			$row['mod_name'] = $this->User->getUserName($row['mod']);
			$row['reason'] = is_numeric($row['reason']) ? $tsDenuncias[$type][$row['reason']] : Html::escape($row['reason']);
			$data['datos'][] = $row;
		}
	}

	public function getPospelera(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		// PAGINAS
		$total = DB::value('SELECT COUNT(*) FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category  WHERE h.type = 1 AND h.action = 2');

		$data['pages'] = $this->Paginator->pageIndex("/moderacion/pospelera?", $_GET['s'] ?? 0, (int)$total, $max);
		//
		$rows = DB::fetchAll('SELECT u.user_id, u.user_name, h.*, p.post_id, p.post_title, c.c_seo, c.c_nombre FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE h.type = 1 AND h.action = 2 AND p.post_status = :status LIMIT ' . $limit, ['status' => 'eliminado']);
		// DENUNCIAS
		$this->getReazon($data, $rows, 'posts');
		//
		return $data;
	}

	public function getFopelera(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		// PAGINAS
		$total = DB::value('SELECT COUNT(*) FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_historial AS h ON h.pofid = f.foto_id WHERE h.type = 2 AND h.action = 2 AND f.f_status = 2');
		$data['pages'] = $this->Paginator->pageIndex("/moderacion/fopelera?", $_GET['s'] ?? 0, (int)$total, $max);
		//
		$rows = DB::fetchAll('SELECT u.user_id, u.user_name, h.*, f.foto_id, f.f_title, f.f_user FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_historial AS h ON h.pofid = f.foto_id WHERE h.type = 2 AND h.action = 2 AND f.f_status = 2 LIMIT ' . $limit);
		// DENUNCIAS
		$this->getReazon($data, $rows, 'fotos');
		//
		return $data;
	}

	public function getComentariosD(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		// PAGINAS
		$total = DB::value('SELECT COUNT(*) FROM p_comentarios AS c LEFT JOIN u_miembros AS u ON u.user_id = c.c_user WHERE c.c_status = 1');
		$data['pages'] = $this->Paginator->pageIndex("/moderacion/comentarios?", $_GET['s'] ?? 0, (int)$total, $max);
		//
		$data['datos'] = DB::fetchAll('SELECT u.user_id, u.user_name, c.cid, c.c_user, c.c_post_id, c.c_date, c.c_body, c.c_ip, p.post_id, p.post_title, cat.c_seo, cat.c_nombre FROM p_comentarios AS c LEFT JOIN p_posts AS p ON c.c_post_id = p.post_id LEFT JOIN p_categorias AS cat ON cat.cid = p.post_category  LEFT JOIN u_miembros AS u ON u.user_id = c.c_user WHERE c.c_status = 1 ORDER BY c.c_date DESC LIMIT ' . $limit);
		//
		return $data;
	}

	public function getPostsD(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		// PAGINAS
		$total = DB::value("SELECT COUNT(*) FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user WHERE p.post_status = :status", ['status' => 'revision']);
		$data['pages'] = $this->Paginator->pageIndex("/moderacion/revposts?", $_GET['s'] ?? 0, (int)$total, $max);
		//
		$data['datos'] = DB::fetchAll('SELECT u.user_id, u.user_name, h.*, p.post_id, p.post_title, c.c_seo, c.c_nombre FROM p_posts AS p LEFT JOIN w_historial AS h ON h.pofid = p.post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN u_miembros AS u ON u.user_id = h.mod  WHERE h.type = 1 AND h.action = 3 AND p.post_status = :status LIMIT ' . $limit, ['status' => 'revision']);
		//
		return $data;
	}

	/**
	 * @name getHistory()
	 * @access public
	 * @param
	 * @return array
	*/
	public function getHistory(string|int $type): array {
		$query = 'SELECT u.user_id, u.user_name, h.*, p.post_id, p.post_title FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id WHERE h.type = 1';
		if($type === 'fotos') {
			$query = 'SELECT u.user_id, u.user_name, h.*, f.foto_id, f.f_title, f.f_user FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_historial AS h ON h.pofid = f.foto_id WHERE h.type = 2';
		}
		$rows = DB::fetchAll($query .' ORDER BY h.id DESC LIMIT 20');
		$data = [];
		// DENUNCIAS
		$this->getReazon($data, $rows, 'posts');
		return $data;
	}
}
