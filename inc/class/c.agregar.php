<?php

/**
 * @name c.agregar.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once __DIR__ . '/c.moderacion.php';
require_once TS_UTILS . '/Extras.php';
require_once TS_UTILS . '/IP.php';

class tsAgregar {
	
	protected tsCore $Core;
	protected tsUser $User;
	protected Extras $Extras;
	protected IP $IP;

	public int $postId;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		$this->Extras = new Extras;
		$this->IP = new IP;
		$this->postId = $this->getPostId();
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
	 * @return bool
	 */
	private function postApproved(): bool {
		return (!$this->User->is_admod && ((int)$this->Core->settings['c_desapprove_post'] === 1 || $this->User->permisos['gorpap'] === true));
	}

	/**
	 * @access private
	 * @return string
	 */
	private function activeUserSqlCondition(): string {
		return $this->canSeeInactiveUsers() ? '' : "AND u.user_activo = 1 AND u.user_baneado = 0";
	}

	/**
	 * @access public
	 * @param string
	 * @return array
	 */
	public function simiPosts(string $search = ''): string|array {
		if ($search === '') {
			return '';
		}
		$where = $this->activeUserSqlCondition(); 
		$data = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT p.post_id, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 $where AND MATCH(p.post_title) AGAINST('$search' IN BOOLEAN MODE) ORDER BY RAND() DESC LIMIT 5"));
		
		//
		return $data;
	}

   /**
	* @access public
    * @param string $q
	 * @return string
	 */
	public function genTags(string $q = ''): string {
		if ($q === '') {
			return '';
		}
		// limpiar, bajar a minúsculas y colapsar espacios
		$texto = mb_strtolower($q, 'UTF-8');
		$texto = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $texto);
		$texto = preg_replace('/\s+/u', ' ', trim($texto));
		$tags = [];
		foreach (explode(' ', $texto) as $word) {
			$length = mb_strlen($word, 'UTF-8');
			if ($length >= 4 && $length <= 12) {
				$tags[$word] = true; // evita duplicados
			}
		}
		return implode(', ', array_keys($tags));
	}

	/*
		getPreview()
	*/
	public function getPreview(): string {
		$cuerpo = $this->Core->setSecure($_POST['cuerpo'], true);
		return $this->Core->parseBadWords($this->Core->parseBBCode($cuerpo), true);
	}

	/**
	 * @access public
	*/
	public function validTags(string $tags, int $min = 4): bool {
		$items = array_filter(
			array_map('trim', explode(',', $tags)),
			static fn($tag) => $tag !== ''
		);
		if (count($items) < $min) {
			return false;
		}
		foreach ($items as $tag) {
			if (!preg_match('/^[\p{L}\p{N}_-]+$/u', $tag)) {
				return false;
			}
		}
		return true;
	}

	private function collectPostData(bool $newPost = true): array {
		$postData = [
			'title' => $this->Core->parseBadWords($this->Core->setSecure($_POST['titulo'], true)),
			'body' => $this->Core->setSecure($_POST['cuerpo']),
			'tags' => $this->Core->parseBadWords($this->Core->setSecure($_POST['tags'], true)),
			'category' => (int)$_POST['categoria'],
		];
		if($newPost) {
			$postData['date'] = time();
		}
		return $postData;
	}

	private function validatePostData(array $data): bool {
		if (trim($data['title']) === '') {
			return false;
		}
		if (trim(strip_tags($data['body'])) === '') {
			return false;
		}
		if (!$this->validTags($data['tags'])) {
			return false;
		}
		if ($data['category'] <= 0) {
			return false;
		}
		return true;
	}

	private function applyOptionalFlags(array &$postData): void {
		$keys = ['visitantes', 'smileys', 'private', 'block_comments', 'sponsored', 'sticky'];
		foreach ($keys as $key) {
			$value = isset($_POST[$key]) && $_POST[$key] === 'on' ? 1 : 0;
			if (in_array($key, ['sponsored', 'sticky'], true)) {
				$value = ($this->User->is_admod || $this->User->permisos['most'] === false) ? $value : 0;
			}
			$postData[$key] = $value;
		}
	}
	
	/*
		newPost()
	*/
	public function newPost(): string|int {
		global $tsMonitor, $tsActividad;
		//
		if(!($this->User->is_admod || $this->User->permisos['gopp'])) {
			return 'No tienes permiso para crear posts.';
		}
		//
		$postData = $this->collectPostData();
		$exists = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(post_id) AS few FROM `p_posts` WHERE post_body = '{$postData['body']}' LIMIT 1"))[0];
		if($exists > 0) return 'No se puede agregar el post, porque el contenido ya existe.';
		if (!$this->validatePostData($postData)) {
			return 'Datos inválidos.';
		}
		// Pueden ir vacios
		$this->applyOptionalFlags($postData);
		// ANTI FLOOD
		$antiflood = 2;
		if((int)$this->User->info['user_lastpost'] > (time() - (int)$antiflood)) {
			return 'No puedes publicar por ahora';
		}
		// EXISTE LA CATEGORIA?
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM p_categorias WHERE cid = {$postData['category']} LIMIT 1");
		if(db_exec('num_rows', $query) === 0) return 'La categor&iacute;a especificada no existe.';
		// INSERTAMOS
		$time = time();
		$postData['ip'] = $this->IP->executeIP();
		$desapprove = $this->postApproved() ? 3 : 0;
		if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `p_posts` (post_user, post_category, post_title, post_body, post_date, post_tags, post_ip, post_private, post_block_comments, post_sponsored, post_sticky, post_smileys, post_visitantes, post_status, post_draft) VALUES ({$this->User->uid}, {$postData['category']}, '{$postData['title']}', '{$postData['body']}', {$postData['date']}, '{$postData['tags']}', '{$postData['ip']}', {$postData['private']}, {$postData['block_comments']}, {$postData['sponsored']}, {$postData['sticky']}, {$postData['smileys']}, {$postData['visitantes']}, {$desapprove}, 0)")) {
			return show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db');
		}
		$postID = (int)db_exec('insert_id');
		// Si está oculto, lo creamos en el historial e.e
		if($desapprove) {
			db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `w_historial` (`pofid`, `action`, `type`, `mod`, `reason`, `date`, `mod_ip`) VALUES ({$postID}, 3, 1, {$this->User->uid}, 'Revisi&oacute;n al publicar', $time, '{$postData['ip']}')");
		}
		// ESTADÍSTICAS
		db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_stats` SET `stats_posts` = stats_posts + 1 WHERE `stats_no` = 1");
		// ULTIMO POST
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_lastpost = $time WHERE user_id = {$this->User->uid}");
		// AGREGAR AL MONITOR DE LOS USUARIOS QUE ME SIGUEN
		$tsMonitor->setFollowNotificacion(5, 1, (int)$this->User->uid, (int)$postID);
		// REGISTRAR MI ACTIVIDAD
		$tsActividad->setActividad(1, (int)$postID);
		// SUBIR DE RANGO?
		//$this->subirRango((int)$this->User->uid);
		//
		return (int)$postID;
  	}

	/*
		savePost()
	*/
	public function savePost() {
		$postId = $this->postId;
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_user, post_sponsored, post_sticky, post_status FROM p_posts WHERE post_id = $postId LIMIT 1"));
		//
		if((int)$data['post_status'] !== 0 && !$this->User->is_admod && !$this->User->permisos['moedpo']) {
			return 'El post no puede ser editado.';
		}
		$postData = $this->collectPostData(false);
		if (!$this->validatePostData($postData)) {
			return 'Datos inválidos.';
		}
		// Pueden ir vacios
		$this->applyOptionalFlags($postData);
		// ACTUALIZAMOS
		if($this->User->uid === (int)$data['post_user'] || !$this->User->is_admod || !$this->User->permisos['moedpo']) {
			$set = $this->Core->buildSqlSet($postData);
			if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE p_posts SET $set WHERE post_id = $postId")) {
				return show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db');
			}
			// GUARDAR EN EL HISTORIAL	DE MODERACION
			$razon = (string)($_POST['razon'] ?? 'Sin motivo');
			if(($this->User->is_admod || $this->User->permisos['moedpo']) && $this->User->uid !== (int)$data['post_user'] && $razon) {
				$tsMod = new tsMod();
				return $tsMod->setHistory('editar', 'post', [
					'post_id' => $postId, 
					'title' => $postData['title'], 
					'autor' => $data['post_user'], 
					'razon' => $razon
				]);
			} 
			return 1;
		}
	}

	/*
		getEditPost()
	*/
	public function getEditPost() {
		$pid = $this->postId;
		$post = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM p_posts WHERE post_id = $pid LIMIT 1"));
		//
		if(empty($post['post_id'])) {
			return 'El post elegido no existe.';
		}
		if((int)$post['post_status'] !== 0 && !$this->User->is_admod && !$this->User->permisos['moedpo']) {
			return 'El post no puede ser editado.';
		}
		if(($this->User->uid !== (int)$post['post_user']) && !$this->User->is_admod && !$this->User->permisos['moedpo']) {
			return 'No puedes editar un post que no es tuyo.';
		}
		//
		return $post;
	}

}