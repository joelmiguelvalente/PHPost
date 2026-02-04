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

require_once __DIR__ . '/c.visitas.php';
require_once TS_HELPERS . '/UserHelper.php';

class tsPosts {
	
	protected tsCore $Core;
	protected tsUser $User;
	protected tsVisitas $Visitas;
	protected UserHelper $UserHelper;

	public int $postId;
	private int $cacheTTL;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		$this->Visitas = new tsVisitas($this->Core, $this->User);
		$this->UserHelper = new UserHelper($this->Core);
		// Variables normales
		$this->postId = $this->getPostId();
		$this->cacheTTL = (int)$this->Core->settings['c_stats_cache'] * 60;
	}

	/**
	 * @access private
	 * @return int
	 */
	private function getPostId(): int {
		return filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'postid', FILTER_VALIDATE_INT) ?? 0;
	}

	/**
	 * @access private
	 * @return bool
	 */
	private function canSeeInactiveUsers(): bool {
		return ($this->User->is_admod && (int)$this->Core->settings['c_see_mod'] === 1);
	}

	/**
	 * @access private
	 * @param string
	 * @return bool
	 */
	private function canPermsUsers(string $perm): bool {
		return (!$this->User->is_admod && $this->User->permisos[$perm] === false);
	}

	/**
	 * @access private
	 * @return string
	 */
	private function activeUserSqlCondition(): string {
		return $this->canSeeInactiveUsers() ? '' : "AND u.user_activo = 1 AND u.user_baneado = 0";
	}

	/**
	 * @access private
	 * @return string
	 */
	private function getNavigationAction(): string {
		return trim((string)$_GET['action'] ?? '');
	}

	/**
	 * @access private
	 * @return boid
	 */
	private function redirectToPosts(): void {
		$this->Core->redirectTo($this->Core->settings['url'] . '/posts/');
	}

	/**
	 * @access private
	 * @param array
	 * @return void
	 */
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
		if($action === 'random') {
			$sql .= " ORDER BY RAND() DESC LIMIT 1";
		} else {
			$isPrev = ($action === 'prev');
			$operator = $isPrev ? '<' : '>';
			$orderBy  = $isPrev ? 'DESC' : 'ASC';
			$sql .= " AND p.post_id {$operator} {$this->postId} ORDER BY p.post_id {$orderBy} LIMIT 1";
		}
		$query = db_exec([__FILE__, __LINE__], 'query', $sql);
		if (!db_exec('num_rows', $query)) {
			$this->redirectToPosts();
			return;
		}
		$this->redirectToPost(db_exec('fetch_assoc', $query));
	}

	/**
	 * @access private
	 * @return array
	 */
	private function getDataPost(): array {
		$group = "{$this->postId} {$this->activeUserSqlCondition()}";
		// DATOS DEL POST
		return db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT p.* ,m.*, u.user_id FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN u_perfil AS m ON p.post_user = m.user_id WHERE post_id = {$group} LIMIT 1"));
	}

	/**
	 * @access private
	 * @param array
	 * @return array
	 */
	private function postStatus(array $postData): array {
		if((int)$postData['post_id'] === 0) {
			$tsDraft = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_draft FROM p_borradores WHERE post_id = {$postData['post_id']} LIMIT 1"));
			$msg = !isset($tsDraft['post_draft']) ? 'fue eliminado!' : 'no existe o fue eliminado.';
			return ['deleted', "Oops! Este post $msg"];
		}
		if((int)$postData['post_status'] === 1 && $this->canPermsUsers('moacp')) {
			return ['denunciado','Oops! El Post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias.'];
		}
		if((int)$postData['post_status'] === 2 && $this->canPermsUsers('morp')) {
			return ['deleted','Oops! El post fue eliminado!'];
		}
		if((int)$postData['post_status'] === 3 && $this->canPermsUsers('mocp')) {
			return ['denunciado','Oops! El Post se encuentra en revisi&oacute;n, a la espera de su publicaci&oacute;n.'];
		}
		if(!(int)$postData['post_private'] === 0 && !$this->User->is_member) {
			return ['privado', (string)$postData['post_title']];
		}
		return [];
	}

	/**
	 * @access private
	 * @param int
	 * @return bool|int
	 */
	private function checkedStatsPosts(int $cache): bool|int {
		return ($cache + $this->cacheTTL) < time();
	}

	/**
	 * @access private
	 * @param array
	 * @return array
	 */
	private function refreshPostStats(array &$postData) {
		$pid = (int)$this->postId;
		$data = [];
		if($this->checkedStatsPosts((int)$postData['post_cache'])) {
			$queries = [
				'comments' => "SELECT COUNT(u.user_name) AS c FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = $pid AND c.c_status = 0 {$this->activeUserSqlCondition()}",
				'seguidores' => "SELECT COUNT(u.user_name) AS s FROM u_miembros AS u LEFT JOIN u_follows AS f ON u.user_id = f.f_user WHERE f.f_type = 2 AND f.f_id = $pid {$this->activeUserSqlCondition()}",
				'shared' => "SELECT COUNT(follow_id) AS m FROM u_follows WHERE f_type = 3 AND f_id = $pid",
				'favoritos' => "SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = $pid"
			];
			foreach($queries as $who => $sql) {
	      	$row = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', $sql));
	      	$data['post_' . $who] = (int)($row[0] ?? 0);
			}
			$data['post_cache'] = time();
			$set = $this->Core->buildSqlSet($data);
			//ACTUALIZAMOS LAS ESTADÍSTICAS
			db_exec([__FILE__, __LINE__], 'query', "UPDATE p_posts SET $set WHERE post_id = $pid");
			$postData += $data;
		}
	}

	/**
	 * @access private
	 * @param int(2)
	 * @return int
	 */
	private function isFollow(int $fid, ?int $type = 1): int {
		$sql = "SELECT COUNT(follow_id) AS f FROM u_follows WHERE f_id = $fid AND f_user = {$this->User->uid} AND f_type = $type";
		return db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', $sql))[0];
	}

	/**
	 * @access public
	 * @return array
	 */
	public function getPost(): array  {
		if($this->postId === 0) {
			return ['deleted','Oops! Este post no existe o fue eliminado.'];
		}
		// DAR MEDALLA
		$this->DarMedalla($this->postId);
		// OBTENER DATOS DEL POST
		$postData = $this->getDataPost();
		$this->postStatus($postData);
		// ESTADÍSTICAS
		$this->refreshPostStats($postData);
		// USUARIO BLOQUEADO?
		$postData['block'] = $this->UserHelper->isBlocked((int)$postData['post_user'], $this->User->uid);
		// FOLLOWS
		if((int)$postData['post_seguidores'] > 0){
			$postData['follow'] = $this->isFollow($this->postId, 2);
		}
		// VISITANTES RECIENTES
		if((int)$postData['post_visitantes'] === 1) {
			$postData['visitas'] = $this->Visitas->getLastViews($this->postId, 2);
		}
		//PUNTOS
		if((int)$postData['post_user'] === $this->User->uid || $this->User->is_admod){
			$postData['puntos'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT p.*, u.user_id, u.user_name FROM p_votos AS p LEFT JOIN u_miembros AS u ON p.tuser = u.user_id WHERE p.tid = {$this->postId} AND p.type = 1 ORDER BY p.cant DESC"));
		}
		// CATEGORIAS
		$postData['categoria'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT c.c_nombre, c.c_seo FROM p_categorias AS c WHERE c.cid = {$postData['post_category']}"));
		// BBCode
		$typeBody = ((int)$postData['post_smileys'] === 0) ? 'normal' : 'firma';
		$postData['post_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($postData['post_body'], $typeBody), true);
		// FIRMA
		$postData['user_firma'] = $this->Core->parseBadWords($this->Core->parseBBCode($postData['user_firma'], 'firma'),true);
		// MEDALLAS
		$postData['medallas'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = {$this->postId} AND m.m_type = 2 ORDER BY a.medal_date"));
		$postData['m_total'] = count($postData['medallas'] ?? 0);
		// TAGS
		$postData['post_tags'] = explode(",",$postData['post_tags']);
		$postData['n_tags'] = count($postData['post_tags']) - 1;
		// NUEVA VISITA : FUNCION SIMPLE
		$visitado = $this->Visitas->updateViews($this->postId, 2);
		$this->Visitas->updateViewsGuest((int)$visitado, $this->postId, 2);
		// AGREGAMOS A VISITADOS... PORTAL
		$this->Visitas->addViewPortal($this->postId);
		//
		return $postData;
	}

	/**
	 * @access public
	 * @param int
	 * @return array
	 */
	public function getAutor(int $userId = 0): array {
		// DATOS DEL AUTOR
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, u.user_rango, u.user_puntos, u.user_lastactive, u.user_last_ip, u.user_activo, u.user_baneado, p.user_pais, p.user_sexo, p.user_firma FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id WHERE u.user_id = $userId LIMIT 1"));
		// EXTRA DEL USUARIO
		$data['user_seguidores'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_id = $userId AND f_type = 1"));
		$data['user_comentarios'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM p_comentarios WHERE c_user = $userId AND c_status = 0"));
		$data['user_posts'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id FROM p_posts WHERE post_user = $userId AND post_status = 0"));
		// RANGOS DE ESTE USUARIO
		$data['rango'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT r_name, r_color, r_image FROM u_rangos WHERE rango_id = {$data['user_rango']} LIMIT 1"));
		// STATUS
		$data['status'] = $this->UserHelper->getStatusCode((int)$data['user_lastactive'], (int)$data['user_baneado']);
		// PAIS
		$tsPaises = require_once dirname(__DIR__, 1) . "/extras/Paises.php";
		$data['pais'] = [
			'icon' => strtolower($data['user_pais'] ?? 'XX'),
			'name' => $tsPaises[$data['user_pais'] ?? 'XX']
		];
		// FOLLOWS
		if((int)$data['user_seguidores'] > 0){
			$postData['follow'] = $this->isFollow((int)$userId, 1);
		}
		// RETURN
		return $data;
	}

	/**
	 * @access public
	 * @return array
	 */
	public function getPunteador(): array {
		$mode = (int) $this->Core->settings['c_allow_points'];
		$rango = match ($mode ) {
			0  => (int) ($this->User->info['user_puntosxdar'] ?? 0),
			-1 => $mode,
			default => (int)($this->User->permisos['gopfp'] ?? 0),
		};
		return ['rango' => $rango];
	}

	/**
	 * @access public
	 * @return string
	*/
	public function deletePost(): string {
		$userId = (int) $this->User->uid;
		if ($this->postId <= 0) {
			return '0: ID inválido.';
		}
		$post = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_user, post_draft FROM p_posts WHERE post_id = {$this->postId}"));
		if (!$post) {
			return '0: El post no existe.';
		}
		if ((int)$post['post_draft'] === 2) {
			return '0: El post ya está eliminado.';
		}
		if ($post['post_user'] !== $userId && $this->User->is_admod !== 1) {
			return '0: No tenés permisos para eliminar este post.';
		}
		db_exec([__FILE__, __LINE__], 'query', 'START TRANSACTION');
		$continue = db_exec([__FILE__, __LINE__], 'query', "UPDATE p_posts SET post_draft = 2 WHERE post_id = {$this->postId}");
		// Soft delete
		if (!$continue) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando el post.';
		}
		// Soft delete de comentarios (recomendado)
		db_exec([__FILE__, __LINE__], 'query', "UPDATE p_comentarios SET c_status = 2 WHERE c_post_id = {$this->postId}");
		db_exec([__FILE__, __LINE__], 'query', "UPDATE w_stats SET stats_posts = GREATEST(stats_posts - 1, 0) WHERE stats_no = 1");
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_posts = GREATEST(user_posts - 1, 0) WHERE user_id = {$post['post_user']}");
		db_exec([__FILE__, __LINE__], 'query', 'COMMIT');
		return '1: El post fue eliminado correctamente.';
	}
	
	/**
	 * @access public 
	 * @return string
	*/
	public function deleteAdminPost(): string {
		if ($this->postId <= 0) {
			return '0: ID de post inválido.';
		}
		// No es el administrador
		if($this->User->is_admod !== 1) {
			return '0: Permisos insuficientes.';
		}
		// Post Eliminado
		$exists = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id FROM p_posts WHERE post_id = {$this->postId} AND post_status = 2"));
		if(!$exists) {
			return '0: El post ya se encuentra eliminado';
		}
		db_exec([__FILE__, __LINE__], 'query', 'START TRANSACTION');
		if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM p_posts WHERE post_id = {$this->postId}")) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando el post.';
		}
		if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM p_comentarios WHERE c_post_id = {$this->postId}")) {
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
		$sql = "SELECT DISTINCT p.post_id, p.post_title, p.post_category, p.post_private, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE MATCH (p.post_tags) AGAINST ('$searchTerms' IN BOOLEAN MODE) AND p.post_status = 0 AND p.post_sticky = 0 ORDER BY RAND() LIMIT 10";
		//
		return result_array(db_exec([__FILE__, __LINE__], 'query', $sql));
	}
	
	/*
		votarPost()
	*/
	function votarPost(){
		global $tsCore, $tsUser, $tsMonitor, $tsActividad;
		#GLOBALES
		
		if($this->User->is_admod || $this->User->permisos['godp']){
		
		// Comprobamos que sean números válidos.
		if(!ctype_digit($_POST['puntos'])) { return '0: S&oacute;lo puedes votar con n&uacute;meros.'; }
		//Comprobamos si otro usuario ha votado un post con esta ip
		$_SERVER['REMOTE_ADDR'] = $_SERVER['X_FORWARDED_FOR'] ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) { return '0: Su ip no se pudo validar.'; }
		if($this->User->is_admod != 1){
		if(db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT user_id FROM u_miembros WHERE user_last_ip =  \''.$_SERVER['REMOTE_ADDR'].'\' AND user_id != \''.$this->User->uid.'\'')) || db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT session_id FROM u_sessions WHERE session_ip =  \''.$this->Core->setSecure($_SERVER['REMOTE_ADDR']).'\' AND session_user_id != \''.$this->User->uid.'\''))) return '0: Has usado otra cuenta anteriormente, deber&aacute;s contactar con la administraci&oacute;n.';
		}
		$postId = intval($_POST['postid']);
		$puntos = intval($_POST['puntos']);
		$puntos = abs($puntos); // Numérico negativo se convierte a numérico positivo		
		// SUMAR PUNTOS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_user FROM p_posts WHERE post_id = \''.(int)$this->postId.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		// ES MI POST?
		$is_mypost = ($data['post_user'] == $this->User->uid) ? true : false;
		// NO ES MI POST, PUEDO VOTAR
		if(!$is_mypost){
			// YA LO VOTE?
			$votado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT tid FROM p_votos WHERE tid = \''.(int)$this->postId.'\' AND tuser = \''.$this->User->uid.'\' AND type = \'1\' LIMIT 1'));
			if(empty($votado)){
			
				// COMPROBAMOS LOS PUNTOS QUE PODEMOS DAR
		if($this->Core->settings['c_allow_points'] > 0) {
		$max_points = $this->Core->settings['c_allow_points'];
		}elseif($this->Core->settings['c_allow_points'] == '-1') { //TRUCO, podrás dar todos los puntos que tengas disponibles
		$max_points = $this->User->info['user_puntosxdar']; 
		}elseif($this->Core->settings['c_allow_points'] == '-2') { //TRUCO, podrás dar todos los puntos que quieras (sin abusar ¬¬), se restarán igual, si tienes puesto mantener puntos, estarás debiendo puntos durante una temporada.
		$max_points = 999999999;
		}else{
		$max_points = $this->User->permisos['gopfp'];
		}
				// TENGO SUFICIENTES PUNTOS
				if($this->User->info['user_puntosxdar'] >= $puntos){
				if($puntos > 0) { // Votar sin dar puntos? No, gracias.				
				if($puntos <= $max_points) { // seroo churra XD ._. No alteraciones de javascript para sumar más de lo que se permite (? LOL ¬¬
					// SUMAR PUNTOS AL POST
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_puntos = post_puntos + '.(int)$puntos.' WHERE post_id = \''.(int)$this->postId.'\'');
					// SUMAR PUNTOS AL DUEÑO DEL POST
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_puntos = user_puntos + \''.(int)$puntos.'\' WHERE user_id = \''.(int)$data['post_user'].'\'');
					// RESTAR PUNTOS AL VOTANTE
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_puntosxdar = user_puntosxdar - \''.(int)$puntos.'\' WHERE user_id = \''.$this->User->uid.'\'');
					// INSERTAR EN TABLA
					db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO p_votos (tid, tuser, cant, type, date) VALUES (\''.(int)$this->postId.'\', \''.$this->User->uid.'\', \''.(int)$puntos.'\', \'1\', \''.time().'\')');
					// AGREGAR AL MONITOR
					$tsMonitor->setNotificacion(3, (int)$data['post_user'], $this->User->uid, $this->postId, $puntos);
					// ACTIVIDAD
					$tsActividad->setActividad(3, (int)$this->postId, (int)$puntos);
					// SUBIR DE RANGO
					$this->subirRango($data['post_user'], $this->postId);
					//
					return '1: Puntos agregados!';					                  
				}else return '0: Voto no v&aacute;lido. No puedes dar '.$puntos.' puntos, s&oacute;lo se permiten '.$max_points .' <img src="http://i.imgur.com/doCpk.gif">';													
			   } else return '0: Voto no v&aacute;lido. No puedes no dar puntos.';
			  } else return '0: Voto no v&aacute;lido. No puedes dar '.$puntos.' puntos, s&oacute;lo te quedan '.$this->User->info['user_puntosxdar'].'.';
			} return '0: No es posible votar a un mismo post m&aacute;s de una vez.';
		  } else return '0: No puedes votar tu propio post.';			
		} else return '0: No tienes permiso para hacer esto.';			
		
	}	

	/*
		subirRango()
	*/
	function subirRango($user_id, $postId = false){
		global $tsCore, $tsUser;
		// CONSULTA
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_puntos, u.user_rango, r.r_type FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = \''.$user_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		// SI TIEN RANGO ESPECIAL NO ACTUALIZAMOS....
		if(empty($data['r_type']) && $data['user_rango'] != 3) return true;
		// SI SOLO SE PUEDE SUBIR POR UN POST
		if(!empty($this->postId) && $this->Core->settings['c_newr_type'] == 0) {
			$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_puntos FROM p_posts WHERE post_id = \''.(int)$this->postId.'\' LIMIT 1');
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
	function DarMedalla($postId){
		//
		$data = db_exec('fetch_assoc', $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_id, post_user, post_puntos, post_hits FROM p_posts WHERE post_id = \''.(int)$this->postId.'\' LIMIT 1'));
		
		#···#
		$q1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(follow_id) AS se FROM u_follows WHERE f_id = \''.(int)$this->postId.'\' && f_type = \'2\''));
		$q2 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_post_id = \''.(int)$this->postId.'\' && c_status = \'0\''));
		$q3 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = \''.(int)$this->postId.'\''));
		$q4 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(did) AS d FROM w_denuncias WHERE obj_id = \''.(int)$this->postId.'\' && d_type = \'1\''));
		$q5 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(wm.medal_id) AS m FROM w_medallas AS wm LEFT JOIN w_medallas_assign AS wma ON wm.medal_id = wma.medal_id WHERE wm.m_type = \'2\' AND wma.medal_for = \''.(int)$this->postId.'\''));
		$q6 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(follow_id) AS sh FROM u_follows WHERE f_id = \''.(int)$this->postId.'\' && f_type = \'3\''));
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
		if(!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT id FROM w_medallas_assign WHERE medal_id = \''.(int)$newmedalla.'\' AND medal_for = \''.(int)$this->postId.'\''))){
		db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `w_medallas_assign` (`medal_id`, `medal_for`, `medal_date`, `medal_ip`) VALUES (\''.(int)$newmedalla.'\', \''.(int)$this->postId.'\', \''.time().'\', \''.$_SERVER['REMOTE_ADDR'].'\')');
		db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES (\''.(int)$data['post_user'].'\', \''.(int)$newmedalla.'\', \''.(int)$this->postId.'\', \'16\', \''.time().'\')'); 
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = \''.(int)$newmedalla.'\'');}
		}
	  }	
	}

	
}