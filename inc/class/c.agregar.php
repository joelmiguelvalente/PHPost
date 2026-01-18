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

require_once dirname(__DIR__, 1) . '/utils/Extras.php';
require_once dirname(__DIR__, 1) . '/utils/IP.php';

class tsAgregar {
	
	protected tsCore $Core;
	protected tsUser $User;
	protected Extras $Extras;
	protected IP $IP;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		$this->Extras = new Extras;
		$this->IP = new IP;
	}

	private function seeMod(): bool {
		return ($this->User->is_admod && (int)$this->Core->settings['c_see_mod'] === 1);
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
		$where = $this->seeMod() ? '' : 'AND u.user_activo = 1 AND u.user_baneado = 0'; 
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
	function getPreview(): string {
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
		$desapprove = (int)(!$this->User->is_admod && ((int)$this->Core->settings['c_desapprove_post'] === 1 || $this->User->permisos['gorpap'] === true) ? 3 : 0);
		if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `p_posts` (post_user, post_category, post_title, post_body, post_date, post_tags, post_ip, post_private, post_block_comments, post_sponsored, post_sticky, post_smileys, post_visitantes, post_status, post_draft) VALUES ({$this->User->uid}, {$postData['category']}, '{$postData['title']}', '{$postData['body']}', {$postData['date']}, '{$postData['tags']}', '{$postData['ip']}', {$postData['private']}, {$postData['block_comments']}, {$postData['sponsored']}, {$postData['sticky']}, {$postData['smileys']}, {$postData['visitantes']}, {$desapprove}, 0)")) {
			return show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db');
		}
		$postID = (int)db_exec('insert_id');
		// Si está oculto, lo creamos en el historial e.e
		if(!$this->User->is_admod && ((int)$this->Core->settings['c_desapprove_post'] === 1 || $this->User->permisos['gorpap'] === true)) {
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
		$this->subirRango((int)$this->User->uid);
		//
		return (int)$postID;
  	}

	/*
		savePost()
	*/
	function savePost(){
		//
		$post_id = (int)$_GET['pid'];
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_user, post_sponsored, post_sticky, post_status FROM p_posts WHERE post_id = \''.(int)$post_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		//
		if($data['post_status'] != '0' && !$this->User->is_admod && !$this->User->permisos['moedpo']) {
			return 'El post no puede ser editado.';
		}
		//
		$postData = array(
			'title' => $this->Core->parseBadWords($_POST['titulo'], true),
			'body' => $this->Core->setSecure($_POST['cuerpo'], true),
			'tags' => $this->Core->parseBadWords($this->Core->setSecure($_POST['tags'], true)),
			'category' => $_POST['categoria'],
		);
		// VACIOS
		foreach($postData as $key => $val){
			$val = trim(preg_replace('/[^ A-Za-z0-9]/', '', $val));
			$val = str_replace(' ', '', $val);
			if(empty($val)) return 0;
		}
		// TAGS
		$tags = $this->validTags($postData['tags']);
		if(empty($tags)) return 'Tienes que ingresar por lo menos <b>4</b> tags.';
		//
		$postData['visitantes'] = empty($_POST['visitantes']) ? 0 : 1;
		$postData['smileys'] = empty($_POST['smileys']) ? 0 : 1;			
		$postData['private'] = empty($_POST['privado']) ? 0 : 1;
		$postData['block_comments'] = empty($_POST['sin_comentarios']) ? 0 : 1;
		// SOLO MODERADORES Y ADMINISTRADORES
		if(empty($this->User->is_admod)  && $this->User->permisos['most'] == false) {
			$postData['sponsored'] = $data['post_sponsored'];
			$postData['sticky'] = $data['post_sticky'];   
		} else {
			$postData['sponsored'] = empty($_POST['patrocinado']) ? 0 : 1;
			$postData['sticky'] = empty($_POST['sticky']) ? 0 : 1;
		}
		// ACTUALIZAMOS
		if($this->User->uid == $data['post_user'] || !empty($this->User->is_admod) || !empty($this->User->permisos['moedpo'])){
			if(db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_title = \''.$postData['title'].'\', post_body = \''.$postData['body'].'\', post_tags = \''.$this->Core->setSecure($postData['tags']).'\', post_category = \''.(int)$postData['category'].'\', post_private = \''.$postData['private'].'\', post_block_comments = \''.$postData['block_comments'].'\', post_sponsored = \''.$postData['sponsored'].'\', post_smileys = \''.$postData['smileys'].'\', post_visitantes = \''.$postData['visitantes'].'\', post_sticky = \''.$postData['sticky'].'\' WHERE post_id = \''.(int)$post_id.'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') )) {
				 // GUARDAR EN EL HISTORIAL	DE MODERACION		 
				 if(($this->User->is_admod || $this->User->permisos['moedpo']) && $this->User->uid != $data['post_user'] && $_POST['razon']){
					 include("c.moderacion.php");
					 $tsMod = new tsMod();
					 return $tsMod->setHistory('editar', 'post', array('post_id' => $post_id, 'title' => $postData['title'], 'autor' => $data['post_user'], 'razon' => $_POST['razon']));
				 } else return 1;
			}
		}
	}

	/*
		getEditPost()
	*/
	function getEditPost(){
		//
		$pid = intval($_GET['pid']);
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM p_posts WHERE post_id = \''.(int)$pid.'\' LIMIT 1');
		$ford = db_exec('fetch_assoc', $query);
		
		//
		if(empty($ford['post_id'])){
			return 'El post elegido no existe.';
		}elseif($ford['post_status'] != '0' && $this->User->is_admod == 0 && $this->User->permisos['moedpo'] == false){
			return 'El post no puede ser editado.';
		}elseif(($this->User->uid != $ford['post_user']) && $this->User->is_admod == 0 && $this->User->permisos['moedpo'] == false){
			return 'No puedes editar un post que no es tuyo.';
		}
		// PEQUEÑO HACK
		foreach($ford as $key => $val){
			$iden = str_replace('post_','b_',$key);
			$data[$iden] = $val;
		}
		//
		return $data;
	}

}