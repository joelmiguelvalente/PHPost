<?php

/**
 * @name c.posts.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsPosts {
	
	protected tsCore $Core;
	protected tsUser $User;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
	}

	private function canSeeInactiveUsers(): bool {
		return ($this->User->is_admod && (int)$this->Core->settings['c_see_mod'] === 1);
	}

	private function activeUserSqlCondition(): string {
		return $this->canSeeInactiveUsers() ? '' : "AND u.user_activo = 1 AND u.user_baneado = 0";
	}

	private function getNavigationAction(): string {
		return trim($_GET['action'] ?? '');
	}

	private function getPostId(): int {
		return (int) ($_GET['id'] ?? 0);
	}

	private function redirectToPosts(): void {
		$this->Core->redirectTo($this->Core->settings['url'] . '/posts/');
	}

	private function redirectToPost(array $post): void {
		$title = $this->Core->setSEO($post['post_title']);
		$url = sprintf('%s/posts/%s/%d/%s.html', $this->Core->settings['url'], $post['c_seo'], $post['post_id'], $title);
		$this->Core->redirectTo($url);
	}

	/**
	 * @access public
	 * @return void
	 */
	public function navigatePost(): void {
		$action = $this->getNavigationAction();
		$sql = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 {$this->activeUserSqlCondition()}";
		//
		if($action === 'fortuitae') {
			$sql .= " ORDER BY RAND() DESC LIMIT 1";
		} else {
			$postId = $this->getPostId();
			$isPrev = ($action === 'prev');
			$operator = $isPrev ? '<' : '>';
			$orderBy  = $isPrev ? 'DESC' : 'ASC';
			$sql .= " AND p.post_id {$operator} {$postId} ORDER BY p.post_id {$orderBy} LIMIT 1";
		}
		$query = db_exec([__FILE__, __LINE__], 'query', $sql);
		if (!db_exec('num_rows', $query)) {
			$this->redirectToPosts();
			return;
		}
		$this->redirectToPost(db_exec('fetch_assoc', $query));
	}
	
	
	/*
		getPost()
	*/
	function getPost(){
		global $tsCore, $tsUser;
		//
		$post_id = intval($_GET['post_id']);
		if(empty($post_id)) return array('deleted','Oops! Este post no existe o fue eliminado.');
		// DAR MEDALLA
		$this->DarMedalla($post_id);
		// DATOS DEL POST
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c.* ,m.*, u.user_id FROM `p_posts` AS c LEFT JOIN `u_miembros` AS u ON c.post_user = u.user_id LEFT JOIN `u_perfil` AS m ON c.post_user = m.user_id  WHERE `post_id` = \''.(int)$post_id.'\' '.($tsUser->is_admod && $tsCore->settings['c_see_mod'] == 1 ? '' : 'AND u.user_activo = \'1\' && u.user_baneado = \'0\'').' LIMIT 1');
		//		
		$postData = db_exec('fetch_assoc', $query);
		
		//
		if(empty($postData['post_id'])) {
			$tsDraft = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT b_title FROM p_borradores WHERE b_post_id = \''.(int)$post_id.'\' LIMIT 1'));
			if(!empty($tsDraft['b_title'])) return array('deleted','Oops! Este post no existe o fue eliminado.');
			else return array('deleted','Oops! El post fue eliminado!');
		}
		elseif($postData['post_status'] == 1 && (!$tsUser->is_admod && $tsUser->permisos['moacp'] == false)) return array('denunciado','Oops! El Post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias.');
		elseif($postData['post_status'] == 2 && (!$tsUser->is_admod && $tsUser->permisos['morp'] == false)) return array('deleted','Oops! El post fue eliminado!');
		elseif($postData['post_status'] == 3 && (!$tsUser->is_admod && $tsUser->permisos['mocp'] == false)) return array('denunciado','Oops! El Post se encuentra en revisi&oacute;n, a la espera de su publicaci&oacute;n.');
		elseif(!empty($postData['post_private']) && empty($tsUser->is_member)) return array('privado', $postData['post_title']);
  
		//ESTADÍSTICAS
		if($postData['post_cache'] < time()-($tsCore->settings['c_stats_cache']*60)){        
		$q1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(u.user_name) AS c FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = \''.(int)$post_id.'\' && c.c_status = \'0\' && u.user_activo = \'1\' && u.user_baneado = \'0\''));
		$q2 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(u.user_name) AS s FROM u_miembros AS u LEFT JOIN u_follows AS f ON u.user_id = f.f_user WHERE f.f_type = \'2\' && f.f_id = \''.(int)$post_id.'\' && u.user_activo = \'1\' && u.user_baneado = \'0\''));
		$q3 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(follow_id) AS m FROM u_follows WHERE f_type = \'3\' && f_id = \''.(int)$post_id.'\''));
		$q4 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = \''.(int)$post_id.'\''));
		
		// NÚMERO DE COMENTARIOS
		$postData['post_comments'] = $q1[0];
		// NÚMERO DE SEGUIDORES
		$postData['post_seguidores'] = $q2[0];
		// NÚMERO DE SEGUIDORES
		$postData['post_shared'] = $q3[0];
		// NÚMERO DE FAVORITOS
		$postData['post_favoritos'] = $q4[0];
		
		//ACTUALIZAMOS LAS ESTADÍSTICAS
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_comments = \''.$q1[0].'\', post_seguidores = \''.$q2[0].'\', post_shared = \''.$q3[0].'\', post_favoritos = \''.$q4[0].'\', post_cache = \''.time().'\' WHERE post_id = \''.(int)$post_id.'\'');
		}
		
		// BLOQUEADO
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT bid FROM u_bloqueos WHERE b_user = \''.(int)$postData['post_user'].'\' AND b_auser = \''.$tsUser->uid.'\' LIMIT 1');
		$postData['block'] = db_exec('num_rows', $query);
		
		// FOLLOWS
		if($postData['post_seguidores'] > 0){
			$q1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(follow_id) AS f FROM u_follows WHERE f_id = \''.(int)$postData['post_id'].'\' AND f_user = \''.$tsUser->uid.'\' AND f_type = \'2\''));
			$postData['follow'] = $q1[0];	
		}
		//VISITANTES RECIENTES
		if($postData['post_visitantes']){
		$postData['visitas'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.for = \''.(int)$postData['post_id'].'\' && v.type = \'2\' && v.user > 0 ORDER BY v.date DESC LIMIT 10'));
		}
		//PUNTOS
		if($postData['post_user'] == $tsUser->uid || $tsUser->is_admod){
		$postData['puntos'] = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT p.*, u.user_id, u.user_name FROM p_votos AS p LEFT JOIN u_miembros AS u ON p.tuser = u.user_id WHERE p.tid = \''.(int)$postData['post_id'].'\' && p.type = \'1\' ORDER BY p.cant DESC'));
		}
		// CATEGORIAS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c.c_nombre, c.c_seo FROM p_categorias AS c  WHERE c.cid = \''.$postData['post_category'].'\'');
		$postData['categoria'] = db_exec('fetch_assoc', $query);
		
		// BBCode
		$postData['post_body'] = $tsCore->parseBadWords($postData['post_smileys'] === 0  ? $tsCore->parseBBCode($postData['post_body']) : $tsCore->parseBBCode($postData['post_body'], 'firma'), true);
		$postData['user_firma'] = $tsCore->parseBadWords($tsCore->parseBBCode($postData['user_firma'], 'firma'),true);
		// MEDALLAS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = \''.(int)$postData['post_id'].'\' AND m.m_type = \'2\' ORDER BY a.medal_date');
		$postData['medallas'] = result_array($query);
		$postData['m_total'] = empty($postData['medallas']) ? 0 : count($postData['medallas']);
		
		// TAGS
		$postData['post_tags'] = explode(",",$postData['post_tags']);
		$postData['n_tags'] = count($postData['post_tags']) - 1;
	   // FECHA
		$postData['post_date'] = strftime("%d.%m.%Y a las %H:%M hs",$postData['post_date']);
		// NUEVA VISITA : FUNCION SIMPLE
		$visitado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT id FROM `w_visitas` WHERE `for` = \''.(int)$post_id.'\' && `type` = \'2\' && '.($tsUser->is_member ? '(`user` = \''.$tsUser->uid.'\' OR `ip` LIKE \''.$_SERVER['REMOTE_ADDR'].'\')' : '`ip` LIKE \''.$_SERVER['REMOTE_ADDR'].'\'').' LIMIT 1'));
		if($tsUser->is_member && $visitado == 0) {
			db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO w_visitas (`user`, `for`, `type`, `date`, `ip`) VALUES (\''.$tsUser->uid.'\', \''.(int)$post_id.'\', \'2\', \''.time().'\', \''.$_SERVER['REMOTE_ADDR'].'\')');
			db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_hits = post_hits + 1 WHERE post_id = \''.(int)$post_id.'\' AND post_user != \''.$tsUser->uid.'\'');
		}else{
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE `w_visitas` SET `date` = \''.time().'\', ip = \''.$tsCore->getIP().'\' WHERE `for` = \''.(int)$post_id.'\' && `type` = \'2\' && `user` = \''.$tsUser->uid.'\' LIMIT 1');
		}
		if($tsCore->settings['c_hits_guest'] == 1 && !$tsUser->is_member && !$visitado) {
			db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO w_visitas (`user`, `for`, `type`, `date`, `ip`) VALUES (\''.$tsUser->uid.'\', \''.(int)$post_id.'\', \'2\', \''.time().'\', \''.$_SERVER['REMOTE_ADDR'].'\')');
			db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_hits = post_hits + 1 WHERE post_id = \''.(int)$post_id.'\'');
		}
		// AGREGAMOS A VISITADOS... PORTAL
		if($tsCore->settings['c_allow_portal']){
			$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT last_posts_visited FROM u_portal WHERE user_id = \''.$tsUser->uid.'\' LIMIT 1');
			$data = db_exec('fetch_assoc', $query);
			
			$visited = unserialize($data['last_posts_visited']);
			if(!is_array($visited)) $visited = array();
			$total = count($visited);
			if($total > 10){
				array_splice($visited, 0, 1); // HACK
			}
			//
			if(!in_array($postData['post_id'],$visited))
				array_push($visited,$postData['post_id']);
			//
			$visited = serialize($visited);
			db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_portal SET last_posts_visited = \''.$visited.'\' WHERE user_id = \''.$tsUser->uid.'\'');
		}
		//
		return $postData;
	}
	/*
		getSideData($array)
	*/
	function getAutor($user_id){
	   global $tsUser, $tsCore;
		// DATOS DEL AUTOR
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_id, u.user_name, u.user_rango, u.user_puntos, u.user_lastactive, u.user_last_ip, u.user_activo, u.user_baneado, p.user_pais, p.user_sexo, p.user_firma FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id WHERE u.user_id = \''.(int)$user_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		$data['user_seguidores'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT follow_id FROM u_follows WHERE f_id = \''.(int)$user_id.'\' && f_type = \'1\''));
		$data['user_comentarios'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT cid FROM p_comentarios WHERE c_user = \''.(int)$user_id.'\' && c_status = \'0\''));
		$data['user_posts'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT post_id FROM p_posts WHERE post_user = \''.(int)$user_id.'\' && post_status = \'0\''));
		
		// RANGOS DE ESTE USUARIO
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT r_name, r_color, r_image FROM u_rangos WHERE rango_id = \''.$data['user_rango'].'\' LIMIT 1');
		$data['rango'] = db_exec('fetch_assoc', $query);
		
		// STATUS
		$is_online = (time() - ($tsCore->settings['c_last_active'] * 60));
		$is_inactive = (time() - (($tsCore->settings['c_last_active'] * 60) * 2)); // DOBLE DEL ONLINE
		if($data['user_lastactive'] > $is_online) $data['status'] = array('t' => 'Usuario Online', 'css' => 'online');
		elseif($data['user_lastactive'] > $is_inactive) $data['status'] = array('t' => 'Usuario Inactivo', 'css' => 'inactive');
		else $data['status'] = array('t' => 'Usuario Offline', 'css' => 'offline');
		// PAIS
		include(TS_EXTRA."datos.php"); // Fix 10/06/2013
		$data['pais'] = array('icon' => strtolower($data['user_pais']),'name' => $tsPaises[$data['user_pais']]);
		// FOLLOWS
		if($data['user_seguidores'] > 0){
			$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT follow_id FROM u_follows WHERE f_id = \''.(int)$user_id.'\' AND f_user = \''.$tsUser->uid.'\' AND f_type = \'1\'');
			$data['follow'] = db_exec('num_rows', $query);
			
		}
		// RETURN
		return $data;
	}
	
	/*
		lalala
	*/
	public function getPunteador(): array {
		$allowPoints = (int) $this->Core->settings['c_allow_points'];

		$rango = match (true) {
			$allowPoints > 0  => $allowPoints,
			$allowPoints === -1 => (int) $this->User->info['user_puntosxdar'],
			default => (int) $this->User->permisos['gopfp'],
		};

		return ['rango' => $rango];
	}

	/**
	 * @access public
	 * @return string
	*/
	public function deletePost(): string {
		$postId = (int) ($_POST['postid'] ?? 0);
		$userId = (int) $this->User->uid;
		if ($postId <= 0) {
			return '0: ID inválido.';
		}

		$post = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_user FROM p_posts WHERE post_id = {$postId}"));
		if (!$post) {
			return '0: El post no existe.';
		}

		$isOwner = ($post['post_user'] === $userId);
		$isAdmin = ($this->User->is_admod === 1);
		if (!$isOwner && !$isAdmin) {
			return '0: No tenés permisos para eliminar este post.';
		}
		db_exec([__FILE__, __LINE__], 'query', 'START TRANSACTION');
		// Soft delete
		if (!db_exec([__FILE__, __LINE__], 'query', "UPDATE p_posts SET post_draft = 2 WHERE post_id = {$postId}")) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando el post.';
		}
		// Eliminar comentarios (si aplica)
		if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM p_comentarios WHERE c_post_id = {$postId}")) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando comentarios.';
		}
		// Actualizar estadísticas
		db_exec([__FILE__, __LINE__], 'query', "UPDATE w_stats SET stats_posts = stats_posts - 1 WHERE stats_no = 1");
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_posts = user_posts - 1 WHERE user_id = {$post['post_user']}");
		db_exec([__FILE__, __LINE__], 'query', 'COMMIT');
		return '1: El post fue eliminado correctamente.';
	}
	
	/**
	 * @access public 
	 * @return string
	*/
	public function deleteAdminPost(): string {
		$postId = (int)($_POST['postid'] ?? 0);
		if ($postId <= 0) {
			return '0: ID de post inválido.';
		}
		// No es el administrador
		if($this->User->is_admod !== 1) {
			return '0: Permisos insuficientes.';
		}
		// Post Eliminado
		$exists = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id FROM p_posts WHERE post_id = {$postId} AND post_status = 2"));
		if(!$exists) {
			return '0: El post ya se encuentra eliminado';
		}
		db_exec([__FILE__, __LINE__], 'query', 'START TRANSACTION');
		if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM p_posts WHERE post_id = {$postId}")) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando el post.';
		}
		if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM p_comentarios WHERE c_post_id = {$postId}")) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando los comentarios del post.';
		}
		db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_stats` SET `stats_posts` = stats_posts - 1 WHERE `stats_no` = 1");
		db_exec([__FILE__, __LINE__], 'query', 'COMMIT');
		return "1: El post se ha eliminado correctamente.";		
	}

	/**
	 * @access privae 
	 * @param string|array
	 * @return string
	*/
	private function normalizeTags(array|string $tags): string {
		if (is_array($tags)) {
			$tags = implode(', ', $tags);
		}
		$tags = str_replace('-', ', ', $tags);
		$tags = trim($tags);
		return $tags;
	}

	/**
	 * @access public 
	 * @param string|array
	 * @return array
	*/
	public function getPostsRelatedByTags(string|array $tags): array {
		$searchTerms = $this->normalizeTags($tags);
		if ($searchTerms === '') {
			return [];
		}
		$sql = "SELECT DISTINCT p.post_id, p.post_title, p.post_category, p.post_private, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE MATCH (p.post_tags) AGAINST ('$tags' IN BOOLEAN MODE) AND p.post_status = 0 AND p.post_sticky = 0 ORDER BY RAND() LIMIT 10";
		//
		return result_array(db_exec([__FILE__, __LINE__], 'query', $sql));
	}
	
	/*
		votarPost()
	*/
	function votarPost(){
		global $tsCore, $tsUser, $tsMonitor, $tsActividad;
		#GLOBALES
		
		if($tsUser->is_admod || $tsUser->permisos['godp']){
		
		// Comprobamos que sean números válidos.
		if(!ctype_digit($_POST['puntos'])) { return '0: S&oacute;lo puedes votar con n&uacute;meros.'; }
		//Comprobamos si otro usuario ha votado un post con esta ip
		$_SERVER['REMOTE_ADDR'] = $_SERVER['X_FORWARDED_FOR'] ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) { return '0: Su ip no se pudo validar.'; }
		if($tsUser->is_admod != 1){
		if(db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_id FROM u_miembros WHERE user_last_ip =  \''.$_SERVER['REMOTE_ADDR'].'\' AND user_id != \''.$tsUser->uid.'\'')) || db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT session_id FROM u_sessions WHERE session_ip =  \''.$tsCore->setSecure($_SERVER['REMOTE_ADDR']).'\' AND session_user_id != \''.$tsUser->uid.'\''))) return '0: Has usado otra cuenta anteriormente, deber&aacute;s contactar con la administraci&oacute;n.';
		}
		$post_id = intval($_POST['postid']);
		$puntos = intval($_POST['puntos']);
		$puntos = abs($puntos); // Numérico negativo se convierte a numérico positivo		
		// SUMAR PUNTOS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_user FROM p_posts WHERE post_id = \''.(int)$post_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		// ES MI POST?
		$is_mypost = ($data['post_user'] == $tsUser->uid) ? true : false;
		// NO ES MI POST, PUEDO VOTAR
		if(!$is_mypost){
			// YA LO VOTE?
			$votado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT tid FROM p_votos WHERE tid = \''.(int)$post_id.'\' AND tuser = \''.$tsUser->uid.'\' AND type = \'1\' LIMIT 1'));
			if(empty($votado)){
			
				// COMPROBAMOS LOS PUNTOS QUE PODEMOS DAR
		if($tsCore->settings['c_allow_points'] > 0) {
		$max_points = $tsCore->settings['c_allow_points'];
		}elseif($tsCore->settings['c_allow_points'] == '-1') { //TRUCO, podrás dar todos los puntos que tengas disponibles
		$max_points = $tsUser->info['user_puntosxdar']; 
		}elseif($tsCore->settings['c_allow_points'] == '-2') { //TRUCO, podrás dar todos los puntos que quieras (sin abusar ¬¬), se restarán igual, si tienes puesto mantener puntos, estarás debiendo puntos durante una temporada.
		$max_points = 999999999;
		}else{
		$max_points = $tsUser->permisos['gopfp'];
		}
				// TENGO SUFICIENTES PUNTOS
				if($tsUser->info['user_puntosxdar'] >= $puntos){
				if($puntos > 0) { // Votar sin dar puntos? No, gracias.				
				if($puntos <= $max_points) { // seroo churra XD ._. No alteraciones de javascript para sumar más de lo que se permite (? LOL ¬¬
					// SUMAR PUNTOS AL POST
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_puntos = post_puntos + '.(int)$puntos.' WHERE post_id = \''.(int)$post_id.'\'');
					// SUMAR PUNTOS AL DUEÑO DEL POST
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_puntos = user_puntos + \''.(int)$puntos.'\' WHERE user_id = \''.(int)$data['post_user'].'\'');
					// RESTAR PUNTOS AL VOTANTE
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_puntosxdar = user_puntosxdar - \''.(int)$puntos.'\' WHERE user_id = \''.$tsUser->uid.'\'');
					// INSERTAR EN TABLA
					db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO p_votos (tid, tuser, cant, type, date) VALUES (\''.(int)$post_id.'\', \''.$tsUser->uid.'\', \''.(int)$puntos.'\', \'1\', \''.time().'\')');
					// AGREGAR AL MONITOR
					$tsMonitor->setNotificacion(3, (int)$data['post_user'], $tsUser->uid, $post_id, $puntos);
					// ACTIVIDAD
					$tsActividad->setActividad(3, (int)$post_id, (int)$puntos);
					// SUBIR DE RANGO
					$this->subirRango($data['post_user'], $post_id);
					//
					return '1: Puntos agregados!';					                  
				}else return '0: Voto no v&aacute;lido. No puedes dar '.$puntos.' puntos, s&oacute;lo se permiten '.$max_points .' <img src="http://i.imgur.com/doCpk.gif">';													
			   } else return '0: Voto no v&aacute;lido. No puedes no dar puntos.';
			  } else return '0: Voto no v&aacute;lido. No puedes dar '.$puntos.' puntos, s&oacute;lo te quedan '.$tsUser->info['user_puntosxdar'].'.';
			} return '0: No es posible votar a un mismo post m&aacute;s de una vez.';
		  } else return '0: No puedes votar tu propio post.';			
		} else return '0: No tienes permiso para hacer esto.';			
		
	}	

	/*
		subirRango()
	*/
	function subirRango($user_id, $post_id = false){
		global $tsCore, $tsUser;
		// CONSULTA
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_puntos, u.user_rango, r.r_type FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = \''.$user_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		// SI TIEN RANGO ESPECIAL NO ACTUALIZAMOS....
		if(empty($data['r_type']) && $data['user_rango'] != 3) return true;
		// SI SOLO SE PUEDE SUBIR POR UN POST
		if(!empty($post_id) && $tsCore->settings['c_newr_type'] == 0) {
			$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_puntos FROM p_posts WHERE post_id = \''.(int)$post_id.'\' LIMIT 1');
			$puntos = db_exec('fetch_assoc', $query);
			
			// MODIFICAMOS
			$data['user_puntos'] = $puntos['post_puntos'];
		}
		//
		$puntos_actual = $data['user_puntos'];
		$posts = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(post_id) AS p FROM p_posts WHERE post_user = \''.(int)$user_id.'\' && post_status = \'0\''));
		$fotos = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_user = \''.(int)$user_id.'\' && f_status = \'0\''));
		$comentarios = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_user = \''.(int)$user_id.'\' && c_status = \'0\''));
		
		// RANGOS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT rango_id, r_cant, r_type FROM u_rangos WHERE r_type > \'0\' ORDER BY r_cant');
		
		//
		while($rango = db_exec('fetch_assoc', $query)) 
		{
			// SUBIR USUARIO
			if(!empty($rango['r_cant']) && $rango['r_type'] == 1 && $rango['r_cant'] <= $puntos_actual){
				$newRango = $rango['rango_id'];
			}elseif(!empty($rango['r_cant']) && $rango['r_type'] == 2 && $rango['r_cant'] <= $posts[0]){
				$newRango = $rango['rango_id'];
			}elseif(!empty($rango['r_cant']) && $rango['r_type'] == 3 && $rango['r_cant'] <= $fotos[0]){
				$newRango = $rango['rango_id'];
			}elseif(!empty($rango['r_cant']) && $rango['r_type'] == 4 && $rango['r_cant'] <= $comentarios[0]){
				$newRango = $rango['rango_id'];
			}
		}
		//HAY NUEVO RANGO?
		if(!empty($newRango) && $newRango != $data['user_rango']){
			//
			if(db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_rango = \''.$newRango.'\' WHERE user_id = \''.$user_id.'\' LIMIT 1')) return true;
		}
	}
	
	/*
		DarMedalla()
	*/
	function DarMedalla($post_id){
		//
		$data = db_exec('fetch_assoc', $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_id, post_user, post_puntos, post_hits FROM p_posts WHERE post_id = \''.(int)$post_id.'\' LIMIT 1'));
		
		#···#
		$q1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(follow_id) AS se FROM u_follows WHERE f_id = \''.(int)$post_id.'\' && f_type = \'2\''));
		$q2 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_post_id = \''.(int)$post_id.'\' && c_status = \'0\''));
		$q3 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = \''.(int)$post_id.'\''));
		$q4 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(did) AS d FROM w_denuncias WHERE obj_id = \''.(int)$post_id.'\' && d_type = \'1\''));
		$q5 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(wm.medal_id) AS m FROM w_medallas AS wm LEFT JOIN w_medallas_assign AS wma ON wm.medal_id = wma.medal_id WHERE wm.m_type = \'2\' AND wma.medal_for = \''.(int)$post_id.'\''));
		$q6 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(follow_id) AS sh FROM u_follows WHERE f_id = \''.(int)$post_id.'\' && f_type = \'3\''));
		// MEDALLAS
		$datamedal = result_array($query = db_exec([__FILE__, __LINE__], 'query', 'SELECT medal_id, m_cant, m_cond_post FROM w_medallas WHERE m_type = \'2\' ORDER BY m_cant DESC'));
		
		//		
		foreach($datamedal as $medalla){
			// DarMedalla
			if($medalla['m_cond_post'] == 1 && !empty($data['post_puntos']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['post_puntos']){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 2 && !empty($q1[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q1[0]){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 3 && !empty($q2[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q2[0]){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 4 && !empty($q3[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q3[0]){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 5 && !empty($q4[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q4[0]){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 6 && !empty($data['post_hits']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['post_hits']){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 7 && !empty($q5[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q5[0]){
				$newmedalla = $medalla['medal_id'];
			}elseif($medalla['m_cond_post'] == 8 && !empty($q6[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q6[0]){
				$newmedalla = $medalla['medal_id'];
			}
		//SI HAY NUEVA MEDALLA, HACEMOS LAS CONSULTAS
		if(!empty($newmedalla)){
		if(!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT id FROM w_medallas_assign WHERE medal_id = \''.(int)$newmedalla.'\' AND medal_for = \''.(int)$post_id.'\''))){
		db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `w_medallas_assign` (`medal_id`, `medal_for`, `medal_date`, `medal_ip`) VALUES (\''.(int)$newmedalla.'\', \''.(int)$post_id.'\', \''.time().'\', \''.$_SERVER['REMOTE_ADDR'].'\')');
		db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES (\''.(int)$data['post_user'].'\', \''.(int)$newmedalla.'\', \''.(int)$post_id.'\', \'16\', \''.time().'\')'); 
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = \''.(int)$newmedalla.'\'');}
		}
	  }	
	}
	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*\
								BUSCADOR
	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	/*
		getQuery()
	*/
	function getQuery() {
		global $tsCore, $tsUser;
		//
		$q = $tsCore->setSecure($_GET['q']);
		$c = intval($_GET['cat']);
		$a = $tsCore->setSecure($_GET['autor']);
		$e = $_GET['e'];
		// ESTABLECER FILTROS
		if($c > 0) $where_cat = 'AND p.post_category = \''.(int)$c.'\'';
		if($e == 'tags') $search_on = 'p.post_tags';
		else $search_on = 'p.post_title';
		// BUSQUEDA
		$w_search = 'AND MATCH('.$search_on.') AGAINST(\''.$q.'\' IN BOOLEAN MODE)';
		// SELECCIONAR USUARIO
		if(!empty($a)){
				// OBTENEMOS ID
				$aid = $tsUser->getUserID($a);
				// BUSCAR LOS POST DEL USUARIO SIN CRITERIO DE BUSQUEDA
				if(empty($q) && $aid > 0) $w_search = 'AND p.post_user = \''.(int)$aid.'\'';
				// BUSCAMOS CON CRITERIO PERO SOLO LOS DE UN USUARIO
				elseif($aid >= 1) $w_autor = 'AND p.post_user = \''.(int)$aid.'\'';
				//
		}
		// PAGINAS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(p.post_id) AS total FROM p_posts AS p WHERE p.post_status = \'0\' '.$where_cat.' '.$w_autor.' '.$w_search.' ORDER BY p.post_date');
		$total = db_exec('fetch_assoc', $query);
		$total = $total['total'];
		
		$data['pages'] = $tsCore->getPagination($total, 12);
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_comments, p.post_favoritos, p.post_puntos, u.user_name, c.c_seo, c.c_nombre, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' '.$where_cat.' '.$w_autor.' '.$w_search.' ORDER BY p.post_date DESC LIMIT '.$data['pages']['limit']);
		$data['data'] = result_array($query);
		
		// ACTUALES
		$total = explode(',',$data['pages']['limit']);
		$data['total'] = ($total[0]) + count($data['data']);
		//
		return $data;
		}
	
}