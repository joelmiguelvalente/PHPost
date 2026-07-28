<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsMedal {

	private int $max = 15;

	private string $myIP;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected Paginator $Paginator,
		protected IP $IP
	) {
		$this->myIP = $this->IP->getIPBinary();
	}

	private function getPagination(string $sql = '', string $params = ''): string {
		// PAGINAS
		[$total] = DB::fetchRow($sql);
		return $this->Paginator->pageIndex("/admin/medals?{$params}",
			(int)($_GET['s'] ?? 0),
			(int)$total,
			$this->max
		);
	}

	/**
	  * @name adGetMedals()
	  * @access public
	  * @uses Cargamos las medallas para la administracion
	  * @param
	  * @return array
	  */
	public function adGetMedals(): array {
		$limit = $this->Paginator->setPageLimit($this->max, true);
		$datos['medallas'] = DB::fetchAll('SELECT u.user_id, u.user_name, m.* FROM w_medallas AS m LEFT JOIN u_miembros AS u ON m.m_autor = u.user_id ORDER BY medal_id DESC LIMIT ' . $limit);
		// PAGINAS
		$datos['pages'] = $this->getPagination("SELECT COUNT(*) FROM w_medallas WHERE medal_id > 0");
		return $datos;
	}

	/**
	  * @name adGetAssign()
	  * @access public
	  * @uses Cargamos las medallas asignadas
	  * @param
	  * @return array
	  */
	public function adGetAssign(): array {
		$limit = $this->Paginator->setPageLimit($this->max, true);
		$datos['asignaciones'] = DB::fetchAll("SELECT u.user_id, u.user_name, a.*, p.post_id, p.post_title, c.c_nombre, c.c_seo, f.foto_id, f.f_title, w.* FROM w_medallas_assign AS a LEFT JOIN u_miembros AS u ON u.user_id = a.medal_for LEFT JOIN p_posts AS p ON p.post_id = a.medal_for LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN f_fotos AS f ON f.foto_id = a.medal_for LEFT JOIN w_medallas AS w ON w.medal_id = a.medal_id ORDER BY a.medal_date DESC LIMIT {$limit}");
		// PAGINAS
		$datos['pages'] = $this->getPagination("SELECT COUNT(*) FROM w_medallas_assign WHERE id > 0", "act=showassign");
		return $datos;
	}

	/**
	  * @name adGetMedal()
	  * @access public
	  * @uses Cargamos una medalla para su edición
	  * @param
	  * @return array
	  */
	public function adGetMedal(): array {
		$mid = (int)$_GET['mid'];
		$medal = DB::fetch("SELECT * FROM w_medallas WHERE medal_id = :mid LIMIT 1", ['mid' => $mid]);
		return $medal;
	}

	private function dataMedals(): string|array {
		$medalla = [
			'm_title' => $this->Core->parseBadWords(trim($_POST['m_title'] ?? '')),
			'm_description' => $this->Core->parseBadWords(trim($_POST['m_description'] ?? '')),
			'm_image' => trim($_POST['m_image'] ?? ''),
			'm_type' => (int)($_POST['m_type'] ?? 0),
			'm_cant' => (int)($_POST['m_cant'] ?? 0),
			'm_cond_user' => (int)($_POST['m_cond_user'] ?? 0),
			'm_cond_user_rango' => (int)($_POST['m_cond_user_rango'] ?? 0),
			'm_cond_post' => (int)($_POST['m_cond_post'] ?? 0),
			'm_cond_foto' => (int)($_POST['m_cond_foto'] ?? 0),
		];
		if(empty($medalla['m_title']) || empty($medalla['m_description'])) {
			return 'Debe introducir título y descripción';
		}

		if(!is_numeric($medalla['m_type']) &&
			!is_numeric($medalla['m_cond_user']) &&
			!is_numeric($medalla['m_cond_user_rango']) &&
			!is_numeric($medalla['m_cond_post']) &&
			!is_numeric($medalla['m_cond_foto'])
		) {
			return 'Introduzca valores numéricos';
		}
		return $medalla;
	}

	private function checkMedalExists(array $medalla = [], int $mid = 0): bool {
		//COMPROBAMOS QUE NO EXISTA
		$params = ['m_cant' => $medalla['m_cant']];

		if ($medalla['m_type'] === 1) {
			$conditions = 'm_type = 1 AND m_cond_user = :m_cond_user AND m_cond_user_rango = :m_cond_user_rango';
			$params['m_cond_user'] = $medalla['m_cond_user'];
			$params['m_cond_user_rango'] = $medalla['m_cond_user_rango'];
		} elseif ($medalla['m_type'] === 2) {
			$conditions = 'm_type = 2 AND m_cond_post = :m_cond_post';
			$params['m_cond_post'] = $medalla['m_cond_post'];
		} else {
			$conditions = 'm_type = 3 AND m_cond_post = :m_cond_foto';
			$params['m_cond_foto'] = $medalla['m_cond_foto'];
		}

		if ($mid !== 0) {
			$conditions .= ' AND medal_id != :mid';
			$params['mid'] = $mid;
		}

		return !DB::exists("SELECT medal_id FROM w_medallas WHERE {$conditions} AND m_cant = :m_cant", $params);
	}

	/**
	  * @name editMedal()
	  * @access public
	  * @uses Editamos la medalla
	  * @param
	  * @return array
	  */
	public function editMedal(): string|bool {
		$mid = (int)($_GET['mid'] ?? 0);
		// DATOS
		$medalla = $this->dataMedals();
		//COMPROBAMOS QUE NO EXISTA
		$continue = $this->checkMedalExists($medalla, $mid);
		// ACTUALIZAR
		if(!$continue) {
			return 'Ya existe una medalla con esas características';
		}

		if(!DB::update('w_medallas', $medalla, 'medal_id = :id', ['id' => $mid])) {
			return 'Hubo un error al editar la medalla.';
		}
		return true;
	}

	/**
	 * @name adNewMedal()
	 * @access public
	 * @uses Creamos nueva medalla
	 * @param
	 * @return void
	 */
	public function adNewMedal(): string|bool {
		$medalla = $this->dataMedals();
		//COMPROBAMOS QUE NO EXISTA
		$continue = $this->checkMedalExists($medalla);
		// INSERTAR
		if(!$continue) {
			return '0: Ya existe una medalla con esas características';
		}
		$time = time();
		if(!DB::insert('w_medallas', [
			'm_autor' => $this->User->uid,
			'm_title' => $medalla['m_title'],
			'm_description' => $medalla['m_description'],
			'm_image' => $medalla['m_image'],
			'm_cant' => $medalla['m_cant'],
			'm_type' => $medalla['m_type'],
			'm_cond_user' => $medalla['m_cond_user'],
			'm_cond_user_rango' => $medalla['m_cond_user_rango'],
			'm_cond_post' => $medalla['m_cond_post'],
			'm_cond_foto' => $medalla['m_cond_foto'],
			'm_date' => $time,
		])) {
			return '0: No se pudo insertar la medalla';
		}
		return true;
	}

	private function checkAssingUser(string $usuario, int $medalla, int $uid): string|bool {
		$time = time();
		if($uid <= 0) {
			return '0: El usuario no existe';
		}
		$insertId = DB::insert('w_medallas_assign', [
			'medal_id' => $medalla,
			'medal_for' => $uid,
			'medal_date' => $time,
			'medal_ip' => $this->myIP,
		]);
		if (!$insertId) {
			// Detectar duplicate entry
			if (DB::lastError()['errno'] === 1062) {
				return '0: El usuario ya tiene esa medalla';
			}
			return '0: Ocurrió un error al asignar la medalla';
		}
		if(!DB::insert('u_monitor', [
			'user_id' => $uid,
			'obj_uno' => $medalla,
			'not_type' => 15,
			'not_date' => $time,
		])) {
			return '0: Ocurrió un error al notificar al usuario';
		}
		return true;
	}

	private function checkAssingPost(int $post, int $medalla): string|bool {
		$time = time();
		$data = DB::fetch("SELECT post_user FROM p_posts WHERE post_id = :post LIMIT 1", ['post' => $post]);
		if (!$data) {
			return '0: El post no existe';
		}
		$insertId = DB::insert('w_medallas_assign', [
			'medal_id' => $medalla,
			'medal_for' => $post,
			'medal_date' => $time,
			'medal_ip' => $this->myIP,
		]);
		if (!$insertId) {
			if (DB::lastError()['errno'] === 1062) {
				return '0: El post ya tiene esa medalla';
			}
			return '0: Ocurrió un error al asignar la medalla';
		}
		if(!DB::insert('u_monitor', [
			'user_id' => $data['post_user'],
			'obj_uno' => $medalla,
			'obj_dos' => $post,
			'not_type' => 16,
			'not_date' => $time,
		])){
			return '0: Ocurrió un error al notificar al usuario';
		}
		return true;
	}

	private function checkAssingFoto(int $foto, int $medalla): string|bool {
		$time = time();
		$data = DB::fetch("SELECT f_user FROM f_fotos WHERE foto_id = :foto LIMIT 1", ['foto' => $foto]);
		if (!$data) {
			return '0: La foto no existe';
		}
		$insertId = DB::insert('w_medallas_assign', [
			'medal_id' => $medalla,
			'medal_for' => $foto,
			'medal_date' => $time,
			'medal_ip' => $this->myIP,
		]);
		if (!$insertId) {
			if (DB::lastError()['errno'] === 1062) {
				return '0: La foto ya tiene esa medalla';
			}
			return '0: Ocurrió un error al asignar la medalla';
		}
		if(!DB::insert('u_monitor', [
			'user_id' => $data['f_user'],
			'obj_uno' => $medalla,
			'obj_dos' => $foto,
			'not_type' => 17,
			'not_date' => $time,
		])) {
			return '0: Ocurrió un error al notificar al usuario';
		}
		return true;
	}

	/**
	 * @name AsignarMedalla()
	 * @access public
	 * @uses Damos una medalla a un usuario
	 * @param
	 * @return void
	 */
	public function AsignarMedalla(): string {
		// DATOS
		$medalla = (int)($_POST['mid'] ?? 0);
		$post = (int)($_POST['pid'] ?? 0);
		$foto = (int)($_POST['fid'] ?? 0);
		$usuario = Html::escape((string)($_POST['m_usuario'] ?? ''));
		$user_id = $this->User->getUserID($usuario);

		if($medalla <= 0 && !($post === 0 || $foto === 0 || empty($usuario))) {
			return '0: Debe especificar un único destino';
		}
		$m_type = match(true) {
			!empty($usuario) => 1,
			$post > 0 => 2,
			$foto > 0 => 3,
			default => 1
		};
		if(!DB::exists("SELECT medal_id FROM w_medallas WHERE medal_id = :medalla AND m_type = :m_type LIMIT 1", ['medalla' => $medalla, 'm_type' => $m_type])) {
			return '0: La medalla no puede ser asignada porque no existe o no corresponde a este tipo de asignación.';
		}
		if(!filter_var($this->myIP, FILTER_VALIDATE_IP)) {
			return '0: Su IP no se pudo validar';
		}
		if(!empty($usuario)) {
			$continuar = $this->checkAssingUser($usuario, $medalla, $user_id);
		} elseif(empty($usuario) && $post > 0) {
			$continuar = $this->checkAssingPost($post, $medalla);
		} elseif(empty($usuario) && $foto > 0) {
			$continuar = $this->checkAssingFoto($foto, $medalla);
		} else {
			return '0: No queda claro lo que quiere';
		}
		if ($continuar !== true) {
			return '0: Hubo problemas, chacho';
		}
		if(!DB::query("UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = :medalla", ['medalla' => $medalla])) {
			return '0: La medalla se asignó, pero hubo un problema y el contador no se alteró';
		}
		return '1: Medalla asignada';
	}

	 /**
	  * @name delMedalla()
	  * @access public
	  * @uses Eliminamos una medalla
	  * @return string
	  */
	public function DelMedalla(): string {
		$medalla = (int)($_POST['medal_id'] ?? 0);
		if(!DB::delete('w_medallas', 'medal_id = :medalla', ['medalla' => $medalla])){
			return '0: Hubo un problema al eliminar la medalla';
		}
		if(!DB::delete('w_medallas_assign', 'medal_id = :medalla', ['medalla' => $medalla])) {
			return '0: Hubo un problema al eliminar la asignación de la medalla';
		}
		return '1: La medalla se ha eliminado, usuario/post/foto ha dejado de tenerla.';
	}

	/**
	  * @name delAssign()
	  * @access public
	  * @uses Eliminamos la medalla asignada a un usuario/post/foto
	  * @param
	  * @return text
	  */
	public function DelAssign(): string {
		$asignacion = (int)($_POST['aid'] ?? 0);
		$medalla = (int)($_POST['mid'] ?? 0);
		if(!DB::exists("SELECT id FROM w_medallas_assign WHERE id = :asignacion AND medal_id = :medalla LIMIT 1", ['asignacion' => $asignacion, 'medalla' => $medalla])) {
			return '0: No se ha encontrado esa asignación';
		}
		if(!DB::delete('w_medallas_assign', 'id = :asignacion', ['asignacion' => $asignacion])) {
			return '0: No se eliminó la asignación, pero ahora sabemos que existe.';
		}
		if(DB::query("UPDATE w_medallas SET m_total = m_total - 1 WHERE medal_id = :medalla", ['medalla' => $medalla])) {
			return '0: Se eliminó la asignación, pero no se descontó de las estadísticas.';
		}
		return '1: Asignación eliminada';
	}
}
