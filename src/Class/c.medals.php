<?php

/**
 * @name src/Class/c.medals.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class tsMedal {
   
   protected Paginator $Paginator;

   private int $max = 15;
   private string $myIP;

   public function __construct(
   	protected tsCore $Core, 
   	protected tsUser $User
   ) {
      $this->Paginator = new Paginator;
      $this->myIP = (new IP)->getIP();
   }

   private function getPagination(string $sql = '', string $params = '') {
		// PAGINAS
		list ($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', $sql));
		return $this->Paginator->pageIndex(
			"{$this->Core->settings['url']}/admin/medals?{$params}", 
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
      $datos['medallas'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_id, u.user_name, m.* FROM w_medallas AS m LEFT JOIN u_miembros AS u ON m.m_autor = u.user_id ORDER BY medal_id DESC LIMIT '.$limit));
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
		$limit = $this->Core->setPageLimit($this->max, true);
      $datos['asignaciones'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, a.*, p.post_id, p.post_title, c.c_nombre, c.c_seo, f.foto_id, f.f_title, w.* FROM w_medallas_assign AS a LEFT JOIN u_miembros AS u ON u.user_id = a.medal_for LEFT JOIN p_posts AS p ON p.post_id = a.medal_for LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN f_fotos AS f ON f.foto_id = a.medal_for LEFT JOIN w_medallas AS w ON w.medal_id = a.medal_id ORDER BY a.medal_date DESC LIMIT $limit"));
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
      $medal = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM w_medallas WHERE medal_id = {$mid} LIMIT 1"));
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
			return 'Debe introducir t&iacute;tulo y descripci&oacute;n';
		}

		if(!is_numeric($medalla['m_type']) && 
			!is_numeric($medalla['m_cond_user']) && 
			!is_numeric($medalla['m_cond_user_rango']) && 
			!is_numeric($medalla['m_cond_post']) && 
			!is_numeric($medalla['m_cond_foto'])
		) {
			return 'Introduzca valores num&eacute;ricos';
		}
		return $medalla;
	}
		
	private function checkMedalExists(array $medalla = [], int $mid = 0): bool {
		//COMPROBAMOS QUE NO EXISTA
		$sql = match($medalla['m_type']) {
			1 => "m_type = 1 AND m_cond_user = {$medalla['m_cond_user']} AND m_cond_user_rango = {$medalla['m_cond_user_rango']}",
			2 => "m_type = 2 AND m_cond_post = {$medalla['m_cond_post']}",
			3 => "m_type = 3 AND m_cond_post = {$medalla['m_cond_foto']}"
		};
		$sql .= ($mid === 0) ? '' : " AND medal_id != $mid";
		$data = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT medal_id FROM w_medallas WHERE $sql AND m_cant = {$medalla['m_cant']}"));
		return $data === 0;
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
      	return 'Ya existe una medalla con esas caracter&iacute;sticas';
      }
      $columnas = $this->Core->buildSqlSet($medalla);
		if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE w_medallas SET $columnas WHERE medal_id = $mid")) {
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
   public function adNewMedal() {
		$medalla = $this->dataMedals();
		//COMPROBAMOS QUE NO EXISTA
		$continue = $this->checkMedalExists($medalla);
		// INSERTAR
      if(!$continue) {
      	return '0: Ya existe una medalla con esas caracter&iacute;sticas';
      }
      $time = time();
		if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `w_medallas` (`m_autor`, `m_title`, `m_description`, `m_image`, `m_cant`, `m_type`, `m_cond_user`, `m_cond_user_rango`, `m_cond_post`, `m_cond_foto`, `m_date`) VALUES ({$this->User->uid}, '{$medalla['m_title']}', '{$medalla['m_description']}', '{$medalla['m_image']}', {$medalla['m_cant']}, {$medalla['m_type']}, {$medalla['m_cond_user']}, {$medalla['m_cond_user_rango']}, {$medalla['m_cond_post']}, {$medalla['m_cond_foto']}, $time)")) {
			return '0: No se pudo insertar la medalla';
		}
      return true;
	}

	private function checkAssingUser(string $usuario, int $medalla, int $uid): string|bool {
		$time = time();
		if($uid <= 0) {
			return '0: El usuario no existe';
		}
		$insert = db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES ($medalla, $uid, $time, '{$this->myIP}')");
		if (!$insert) {
      	// Detectar duplicate entry
      	if (db_exec('errno') === 1062) {
      	   return '0: El usuario ya tiene esa medalla';
      	}
      	return '0: Ocurrió un error al asignar la medalla';
    	}
		if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_monitor (user_id, obj_uno, not_type, not_date) VALUES ($uid, $medalla, 15, $time)")) {
			return '0: Ocurrió un error al notificar al usuario';
		}
		return true;
	}

	private function checkAssingPost(int $post, int $medalla): string|bool {
		$time = time();
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT post_user FROM p_posts WHERE post_id = $post LIMIT 1");
		if(!db_exec('num_rows', $query)) {
			return '0: El post no existe';
		}
		$data = db_exec('fetch_assoc', $query);
		$insert = db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES ($medalla, $post, $time, '{$this->myIP}')");
		if(!$insert) {
			if (db_exec('errno') === 1062) {
            return '0: El post ya tiene esa medalla';
        	}
			return '0: Ocurri&oacute; un error al asignar la medalla';
		}
      if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES ({$data['post_user']}, $medalla, $post, 16, $time)")){
			return '0: Ocurri&oacute; un error al notificar al usuario';
		}
		return true;
	}

	private function checkAssingFoto(int $foto, int $medalla): string|bool {
		$time = time();
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT f_user FROM f_fotos WHERE foto_id = $foto LIMIT 1");
		if(!db_exec('num_rows', $query)) {
			return '0: La foto no existe';
		}
		$data = db_exec('fetch_assoc', $query);
		$insert = db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `w_medallas_assign` (`medal_id`, `medal_for`, `medal_date`, `medal_ip`) VALUES ($medalla, $foto, $time, '{$this->myIP}')");
		if(!$insert) {
			if (db_exec('errno') === 1062) {
            return '0: La foto ya tiene esa medalla';
        	}
			return '0: Ocurri&oacute; un error al asignar la medalla';
		}
     	if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES ({$data['f_user']}, $medalla, $foto, 17, $time)")) {
     		return '0: Ocurri&oacute; un error al notificar al usuario';
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
      $usuario = $this->Core->setSecure((string)($_POST['m_usuario'] ?? ''));
		$user_id = $this->User->getUserID($usuario);
		
		if($medalla <= 0 && !($post === 0 || $foto === 0 || empty($usuario))) {
		   return '0: Debe especificar un &uacute;nico destino';
		}
		$m_type = match(true) {
			!empty($usuario) => 1,
			$post > 0 => 2,
			$foto > 0 => 3,
			default => 1
		};
		if(!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT medal_id FROM w_medallas WHERE medal_id = $medalla AND m_type = $m_type LIMIT 1"))) {
			return '0: La medalla no puede ser asignada porque no existe o no corresponde a este tipo de asignaci&oacute;n.';
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
		if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = $medalla")) {
			return '0: La medalla se asign&oacute;, pero hubo un problema y el contador no se alter&oacute;';
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
      if(!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_medallas WHERE medal_id = $medalla")){
      	return '0: Hubo un problema al eliminar la medalla';
      }
		if(!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_medallas_assign WHERE medal_id = $medalla")) {
			return '0: Hubo un problema al eliminar la asginaci&oacute;n de la medalla';
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
	   if(!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM w_medallas_assign WHERE id = $asignacion AND medal_id = $medalla LIMIT 1"))) {
	   	return '0: No se ha encontrado esa asignaci&oacute;n';
	   }
		if(!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_medallas_assign WHERE id = $asignacion")) {
			return '0: No se elimin&oacute; la asignaci&oacute;n, pero ahora sabemos que existe.';
		}
		if(db_exec([__FILE__, __LINE__], 'query', "UPDATE w_medallas SET m_total = m_total - 1 WHERE medal_id = $medalla")) {
			return '0: Se elimin&oacute; la asignaci&oacute;n, pero no se descont&oacute; de las estad&iiacute;sticas.';
		}
		return '1: Asignaci&oacute;n eliminada';
   }
}
