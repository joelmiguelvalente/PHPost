<?php

/**
 * @name c.muro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once __DIR__ . '/c.cuenta.php';
require_once TS_HELPERS . '/CoreHelper.php';
require_once TS_HELPERS . '/UrlHelper.php';
require_once TS_HELPERS . '/MuroHelper.php';
require_once TS_UTILS . '/Avatar.php';

class tsMuro {

	protected CoreHelper $CoreHelper;
	protected UrlHelper $UrlHelper;
	protected MuroHelper $MuroHelper;
	private string $myIP;

	private array $status = [
		'muro' 				=> ['status' => true, 'message' => ''],
		'muro_firma' 		=> ['status' => true, 'message' => ''],
		'mesaje_privado' 	=> ['status' => true, 'message' => ''],
		'ultimas_visitas' 	=> ['status' => true, 'message' => '']
	];
	
	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
		$this->UrlHelper = new UrlHelper($Core);
		$this->MuroHelper = new MuroHelper($Core, $User);
		$this->myIP = (new IP)->getIP();
	}
	
	/**
	 * @access public 
	 * @param int
	 * @param string
	 * @param int
	 * @param int
	*/
	public function getPrivacity(int $userId = 0, ?string $username = '', int $follow = 0, int $yfollow = 0): array {
		//
		$context = [
			'isMe' => ((int)$this->User->uid === (int)$userId),
			'username' => $username,
			'follow' => $follow,
			'yfollow' => $yfollow,
			'lesigoymesigue' => ($follow === 1 AND $yfollow === 1 ? true : false),
			'lesigoomesigue' => ($follow === 0 AND $yfollow === 0 ? false : true)
		];
		$data = DB::fetch("SELECT p_privacidad, p_publicar_muro FROM u_perfil WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
		$privacidad = $this->status;
		// VER MURO
		$this->MuroHelper->getMuroConfig($privacidad, $context, $data['p_privacidad']);
		$this->MuroHelper->getMuroPublicar($privacidad, $context, $data['p_publicar_muro']);
		//
		return $privacidad;
	}
	
	/**
	 * @access public
	 * @param bool
	 * @param string
	 */
	public function ajaxCheck(bool $return = false, ?string $urlIn = null): string|array {
		$type = $_GET['type'] ?? null;
		if (!$type) {
			return '0: El campo <string>type</string> es obligatorio.';
		}
		$url = $this->MuroHelper->sanitizeUrl($urlIn ?? ($_POST['url'] ?? null));
		if ($url === '') {
			return '0: El campo <string>url</string> es obligatorio.';
		}
		return match ($type) {
			'foto'   => $this->MuroHelper->checkImage($url, $return),
			'enlace' => $this->MuroHelper->checkLink($url, $return),
			'video'  => $this->MuroHelper->checkYoutube($url, $return),
			default  => '0: El campo <string>type</string> es obligatorio.',
		};
	}

	private function registerPostSideEffects(int $pid, int $pubId): void {
	   global $tsMonitor, $tsActividad;
	   $tsMonitor->setNotificacion(12, $pid, (int)$this->User->uid, $pubId);
	   $isMy = ($pid === $this->User->uid) ? 0 : 2;
	   $tsActividad->setActividad(10, $pubId, $isMy);
	}

	private function insertMuroAdjunto(array $data): void {
		DB::insert('u_muro_adjuntos', $data);
	}

	private function streamPostFoto(string $attachment, int $pid, string $body, int $date): array|string {
		$foto = $this->ajaxCheck(true, $attachment);
		if (is_string($foto) && str_starts_with($foto, '0')) {
			return $foto;
		}
		$this->Core->antiFlood();
		$pubId = $this->MuroHelper->insertMuro($pid, $body, 2, $date);
		if (!$pubId) return '0: Error al publicar.';
		$this->insertMuroAdjunto(['pub_id' => $pubId, 'adj_url' => $foto, 'adj_image' => $foto]);

		$return = $this->MuroHelper->baseReturn($pubId, $pid, 2, $date);
		$return['p_body']    = $this->MuroHelper->setMenciones($body);
		$return['adj_url']   = $foto;
		$return['adj_image'] = $foto;
		return $return;
	}

	private function streamPostEnlace(string $attachment, int $pid, string $body, int $date): array|string {
		$enlace = $this->ajaxCheck(true, $attachment);
		if (!is_array($enlace)) {
      	return $enlace;
    	}
		$this->Core->antiFlood();
		$pubId = $this->MuroHelper->insertMuro($pid, $body, 3, $date);
		if (!$pubId) return '0: Error al publicar.';
		$this->insertMuroAdjunto(['pub_id' => $pubId, 'adj_url' => $enlace['url'], 'adj_title' => $enlace['title']]);

		$return = $this->MuroHelper->baseReturn($pubId, $pid, 3, $date);
   	$return['p_body']    = $this->MuroHelper->setMenciones($body);
   	$return['adj_title'] = $enlace['title'];
   	$return['adj_url']   = rawurldecode($enlace['url']);
   	return $return;
	}

	private function streamPostVideo(string $attachment, int $pid, string $body, int $date): array|string {
		$video = $this->ajaxCheck(true, $attachment);
	   if (!is_array($video)) {
	      return $video;
	   }
	   $this->Core->antiFlood();
	   $pubId = $this->MuroHelper->insertMuro($pid, $this->Core->setSecure($body, true), 4, $date);
	   if (!$pubId) {
	      return '0: Error al publicar.';
	   }
		$this->insertMuroAdjunto(['pub_id' => $pubId, 'adj_url' => $video['ID'], 'adj_title' => $video['title'], 'adj_description' => $video['desc']]);

	   $return = $this->MuroHelper->baseReturn($pubId, $pid, 4, $date);
	   $return['p_body']          = $this->MuroHelper->setMenciones($body);
	   $return['adj_title']       = $video['title'];
	   $return['adj_url']         = $video['ID'];
	   $return['adj_description'] = $video['desc'];
	   return $return;
	}

	/* 
		streamPost()
	*/
	public function streamPost() {
		//
		$pid 			= (int)($_POST['pid'] ?? 0);
		$data 		= $this->Core->setSecure($_POST['data'], true);
		$attachment = $this->Core->setSecure($_POST['adj'], true);
		$type 		= $this->Core->setSecure(trim($_GET['type'] ?? ''));
		$date			= time();
		// VALIDAMOS SI EXISTE EL PERFIL/USUARIO
		$exists = $this->User->getUserName($pid);
		if(empty($exists)) return '0: El usuario al que intentas comentar no existe.';
		// VERIFICAR QUE PERMITA COMPARTIR EN SU MURO
		$tsCuenta = new tsCuenta($this->Core, $this->User);
		$privacidad = $this->getPrivacity((int)$pid, $exists, (int)$tsCuenta->isFollowed($pid, true), (int)$tsCuenta->isFollowed($pid, false));
		// SE PERMITE FIRMAR EL MURO?
		if(!$privacidad['muro_firma']['status']) return '0: '.$privacidad['muro_firma']['message'];
		// TIPO DE PUBLICACION
		switch($type) {
			// PUBLICAR STATUS/PUBLICACION
			case 'status':
				$text = str_replace(["\n", "\t", ' '], '', $data);
				if ($text === '') {
					return '0: Tu publicación debe tener al menos una letra.';
				}
				$this->Core->antiFlood();
				//
				$pubId = $this->MuroHelper->insertMuro($pid, $data, 1, $date);
				if (!$pubId) {
					return '0: Error al publicar.';
				}
				$return = $this->MuroHelper->baseReturn($pubId, $pid, 1, $date);
				$return['p_body'] = $this->Core->parseBadWords($this->MuroHelper->setMenciones($data), true);
			break;
			 // PUBLICAR FOTO
			case 'foto':
				$return = $this->streamPostFoto($attachment, $pid, $data, $date);
			break;
			// PUBLICAR ENLACE
			case 'enlace':
				$return = $this->streamPostEnlace($attachment, $pid, $data, $date);
			break;
			// PUBLICAR VIDEO
			case 'video':
				$return = $this->streamPostVideo($attachment, $pid, $data, $date);
			break;
			default:
				$return = '0: El campo <b>type</b> es obligatorio.';
			break;
		}
		$this->registerPostSideEffects((int)$pid, (int)$return['pub_id']);
		// RETORNAR VALOR
		return $return;
	}

	/*
		streamRepost()
	*/
	public function streamRepost(){
		global $tsMonitor, $tsActividad;
		//
		$data = $this->Core->setSecure($this->Core->parseBadWords($_POST['data']), true);
		$pid = (int)($_POST['pid'] ?? 0);
		$date = time();
		$pub = DB::fetch("SELECT p_user, p_user_pub FROM u_muro WHERE pub_id = :pid LIMIT 1", ['pid' => $pid]);
		//
		if((int)$pub['p_user'] === 0) {
			return '0: La publicaci&oacute;n no existe.';
		}
		$text = str_replace(["\n", "\t", ' '], '', $data);
		if ($text === '') {
			return '0: Tu comentario debe tener al menos una letra.';
		}
		// ANTI FLOOD
		$this->Core->antiFlood();
		// CONTINUAMOS
		if(!DB::insert('u_muro_comentarios', [
			'pub_id' => $pid, 
			'c_user' => $this->User->uid, 
			'c_date' => $date, 
			'c_body' => $data, 
			'c_ip' => $this->myIP
		])) {
			return '0: Hubo un problema al comentar.';
		}
		$cid = DB::insertId();
		// MONITOR
		$tsMonitor->setMuroRepost($pid, (int)$pub['p_user'], (int)$pub['p_user_pub']);
		// ACTIVIDAD
		$is_my = ((int)$pub['p_user'] === $this->User->uid) ? 1 : 3;
		$tsActividad->setActividad(10, $cid, $is_my);
		// UPDATES
		DB::increment('u_muro', 'p_comments', 'pub_id = :pub_id', ['pub_id' => $pid]);
		// PARA LA PANTILLA
		return [
			'cid' => $cid, 
			'c_body' => $data, 
			'c_date' => $date, 
			'c_user' => $this->User->uid, 
			'c_likes' => 0, 
			'like' => 'Me gusta',
			'user_name' => $this->User->nick
		];
	}

	private function loadNewsWall(string $query, array $param) {
		$query = DB::fetchAll($query, $param);
		$data = [];
		foreach($query as $key => $row) {
			// CARGAR LIKES
			$row['likes'] = ((int)$row['p_likes'] > 0) ? $this->getPubExtras($row['pub_id'], 'likes', $row['p_likes']) : ['link' => 'Me gusta'];
			// CARGAR COMENTARIOS
			if((int)$row['p_comments'] > 0) {
				$row['comments'] = $this->getPubExtras($row['pub_id'], 'comments', 2);
			}
			// MENCIONES
			$row['p_body'] = $this->Core->parseBadWords($this->MuroHelper->setMenciones($row['p_body']), true);
			
			// CARGAR ADJUNTOS
			if((int)$row['p_type'] !== 1) {
				$attachment = DB::fetch("SELECT * FROM u_muro_adjuntos WHERE pub_id = :pid LIMIT 1", ['pid' => $row['pub_id']]);
				$data[] = array_merge($row, $attachment); 
			} else $data[] = $row;
		}
		return $data;
	}
	
	/**
	 * @access public
	 * @param int(x2)
	 * @return array
	 **/
	public function getNews(int $start = 0, int $limit = 10) {
		// SOLO MOSTRAREMOS LAS ULTIMAS 100 PUBLICACIONES
		if($start > 90) return ['total' => '-1'];
		// SEGUIDORES
		$follows = DB::fetchAll("SELECT f_id FROM u_follows WHERE f_user = :uid AND f_type = 1", ['uid' => $this->User->uid]);
		// ORDENAMOS 
		foreach($follows as $key => $val){
			// PERMISO PARA VER SUS PUBLICACIONES??
			$privacidad = $this->getPrivacity($val['f_id'], null, true);
			if($privacidad['muro']['status']) {
				$amigos[] = "{$val['f_id']}";
			}
		}
		$amigos[] = "{$this->User->uid}";
		$amigos = implode(', ',$amigos);
		// OBTENEMOS LAS ULTIMAS PUBLICACIONES
		$query = "SELECT p.*, u.user_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.p_user IN(:amigos) AND p.p_user = p.p_user_pub ORDER BY p.p_date DESC LIMIT :start, :limit";
		
		$data = $this->loadNewsWall($query, ['amigos' => $amigos, 'start' => $start, 'limit' => $limit]);
		// RETORNAMOS
		return ['total' => count($data ?? 0), 'data' => $data];
	}

	/*
		getWall($count)
	*/
	public function getWall(int $userId, int $start = 0): array {
		$total = DB::value("SELECT COUNT(*) FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.p_user = :uid", ['uid' => $userId]) ?? 0;
		//
		$query = "SELECT p.*, u.user_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.p_user = :uid ORDER BY p.pub_id DESC LIMIT :start, 10";
		$data = $this->loadNewsWall($query, ['uid' => $userId, 'start' => $start]);
		return ['total' => count($data ?? 0), 'data' => $data];
	}

	private function getPubExtrasLikes(int $pubId, int $likes): array {
	   if ($likes <= 0) {
	      return ['link' => 'Me gusta', 'text' => ''];
	   }
	   $iLike = $this->checkUserLike($pubId);
	   $userName = $this->determineUserNameForDisplay($pubId, $likes, $iLike);
	  
	   return $this->buildLikeResponse($pubId, $likes, $iLike, $userName);
	}

	/**
	 * Verifica si el usuario actual dio "me gusta" al contenido
	 */
	private function checkUserLike(int $pubId): bool {
	   if (!$this->User->is_member) {
	      return false;
	   }
	   return DB::exists("SELECT 1 FROM u_muro_likes  WHERE user_id = :uid AND obj_id = :pubid AND obj_type = 1", ['uid' => $this->User->uid, 'pubid' => $pubId]);
	}

	/**
	 * Determina qué nombre de usuario necesitamos para mostrar en la interfaz
	 */
	private function determineUserNameForDisplay(int $pubId, int $likes, bool $iLike): ?string {
	   // Caso 1: Solo 1 like en total
	   if ($likes === 1) {
	      return $this->getSingleLikeUserName($pubId, $iLike ? $this->User->uid : null);
	   }
	   // Caso 2: Usuario actual dio like y hay exactamente 2 likes
	   if ($iLike && $likes === 2) {
	      return $this->getSingleLikeUserName($pubId, $this->User->uid);
	   }
	   return null;
	}

	/**
	 * Obtiene el nombre de usuario de un "me gusta", excluyendo al usuario actual si es necesario
	 */
	private function getSingleLikeUserName(int $pubId, ?int $excludeUserId = null): ?string {
	   $params = ['pubid' => $pubId];
	   $excludeClause = $excludeUserId !== null ? 'AND l.user_id != :excluid' : ''; 
	  	if ($excludeUserId !== null) {
	   	$params['excluid'] = $excludeUserId;
	  	}
	  $result = DB::fetch("SELECT u.user_name FROM u_muro_likes AS l LEFT JOIN u_miembros AS u ON l.user_id = u.user_id WHERE l.obj_id = :pubid AND l.obj_type = 1 {$excludeClause} LIMIT 1", $params);
	  return $result ? $result['user_name'] : null;
	}

	/**
	 * Construye la respuesta final basada en los datos recopilados
	 */
	private function buildLikeResponse(int $pubId, int $likes, bool $iLike, ?string $userName): array {
	   $data = [
	      'link' => $iLike ? 'Ya no me gusta' : 'Me gusta',
	      'text' => ''
	   ];
	   // Caso especial: usuario actual dio like y hay 2 likes en total
	   if ($iLike && $likes === 2 && $userName) {
	      $profileUrl = $this->UrlHelper->buildPerfilUrl($userName);
	      $safeUserName = htmlspecialchars($userName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	      $data['text'] = "A <a href=\"{$profileUrl}\">{$safeUserName}</a> y a ti les gusta esto.";
	   }
	   // Usuario actual dio like y hay más de 1 like
	   elseif ($iLike && $likes > 1) {
	      $data['text'] = $this->buildMultipleLikesText($pubId, $likes - 1, true);
	   }
	   // Usuario actual dio like y es el único like
	   elseif ($iLike && $likes === 1) {
	      $data['text'] = 'Te gusta esto.';
	   }
	   // Hay un solo like de otro usuario
	   elseif ($likes === 1 && $userName) {
	      $profileUrl = $this->UrlHelper->buildPerfilUrl($userName);
	      $safeUserName = htmlspecialchars($userName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	      $data['text'] = "A <a href=\"{$profileUrl}\">{$safeUserName}</a> le gusta esto.";
	   }
	   // 2 o más likes de otros usuarios
	   else {
	      $data['text'] = $this->buildMultipleLikesText($pubId, $likes, false);
	   }
	   return $data;
	}

	/**
	 * Construye el texto para múltiples likes
	 */
	private function buildMultipleLikesText(int $pubId, int $count, bool $includesCurrentUser): string {
	   $prefix = $includesCurrentUser ? 'ti y a ' : '';
	   $countText = $this->formatLikeCount($count);
	   return sprintf('A %s<a onclick="muro.show_likes(%d, \'pub\'); return false;">%s</a> les gusta esto.', $prefix, $pubId, $countText);
	}

	/**
	 * Formatea el conteo de likes para mostrar en el texto
	 */
	private function formatLikeCount(int $count): string {
	   return $count === 1 ? '1 persona' : "{$count} personas";
	}

	/*
		getPubExtras($pud_id, $type)
	*/
	public function getPubExtras(int $pubId, string $type = 'likes', int $likes = 0): array {
		switch($type){
			case 'likes':
				$data = $this->getPubExtrasLikes($pubId, $likes);
			break;
			case 'comments':
				$limit = ($likes > 0) ? "LIMIT {$likes}" : '';
				//
				$query = DB::fetchAll("SELECT c.*, u.user_name FROM u_muro_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user = u.user_id WHERE c.pub_id = :uid ORDER BY c.c_date DESC :limit", ['pubid' => $pubId, 'limit' => $limit]);
				foreach($query as $key => $row) {
					$row['c_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($this->MuroHelper->setMenciones($row['c_body'])), true);
					$row['like'] = 'Me gusta';
					if((int)$row['c_likes'] > 0) {
						$iLike = DB::exists("SELECT 1 FROM u_muro_likes WHERE user_id = :uid AND obj_id = :objid AND obj_type = 2", ['uid' => $this->User->uid, 'objid' => $row['cid']]);
						if($iLike) $row['like'] = 'Ya no me gusta';
					}
					$data[] = $row;
				}
				// ORDENAMOS
				asort($data);
			break;
		}
		return $data;
	}

	/*
		getStory()
	*/
	public function getStory(int $pubId, int $userId): string|array {
		// ELEGIMOS
		$pub = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT p.*, u.user_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.pub_id = $pubId LIMIT 1"));
		// COMPROBAMOS
		if($pub['pub_id'] === '') return 'La publicaci&oacute;n que has solicitado no existe.';
		elseif($userId !== (int)$pub['p_user']) return "La publicaci&oacute;n que has solicitado no pertenece al perfil de <strong>{$this->User->getUserName($userId)}</strong>.";
		// CARGAR LIKES
		$pub['likes'] = ((int)$pub['p_likes'] > 0) ? $this->getPubExtras($pub['pub_id'], 'likes', $pub['p_likes']) : ['link' => 'Me gusta']; // FIX: 19/01/2026
		// CARGAR COMENTARIOS
		if((int)$pub['p_comments'] > 0){
			$pub['comments'] = $this->getPubExtras($pub['pub_id'], 'comments');
		}
		// EXTRA
		$pub['hide_more_cm'] = true;
		// ADJUNTOS
		if((int)$pub['p_type'] !== 1) {
			$attachment = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_muro_adjuntos WHERE pub_id = \''.(int)$pubId.'\' LIMIT 1'));
			$data = array_merge($pub, $attachment);
		} else $data = $pub;
		// RETORNAMOS
		return $data;
	}

	/*
		getComments()
	*/
	public function getComments(): string|array {
		$pid = (int) ($_POST['pid'] ?? 0);
		// EXISTE?
		$comment = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT `p_user`, `p_comments` FROM `u_muro` WHERE `pub_id` = $pid LIMIT 1"));
		//
		if(empty($comment)) return '0: La publicaci&oacute;n no existe.';
		$data['data'] = $this->getPubExtras($pid, 'comments');
		// TOTAL / USER DUEÑO DE LA APLICACION
		$data['total'] = $comment['p_comments'];
		$data['user'] = $comment['p_user'];
		//
		return $data;
	}

	/*
		delete()
	*/
	public function deletePost(): string {
		$id = (int) ($_POST['id'] ?? 0);
		$type = ($_POST['type'] === 'pub') ? 'pub' : 'cmt';
		//
		switch($type) {
			case 'pub':
				// DATOS
				$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT `p_user`, `p_user_pub` FROM `u_muro` WHERE `pub_id` = $id LIMIT 1"));
				//
				if($data['p_user'] === '') return '0: La publicaci&oacute;n no existe.';
				// SI ES EL DUEÑO DEL MURO O DE LA PUBLICACION...
				if((int)$data['p_user'] !== $this->User->uid || (int)$data['p_user_pub'] !== $this->User->uid || !$this->User->is_admod || !$this->User->permiso('moderacion.muros.eliminar_publicaciones')) {
					return '0: Hmmm... &iquest;Haciendo pruebas?';
				}
				if(!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `u_muro` WHERE `pub_id` = $id")) {
					return '0: Error al eliminar';
				}
				// BORRAMOS LOS LIKES DE LA PUBLICACION
				db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `u_muro_likes` WHERE `obj_id` = $id AND `obj_type` = 1");
				// BORRAMOS LOS LIKES DE TODOS LOS COMENTARIOS
				$comments = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT `cid` FROM `u_muro_comentarios` WHERE `pub_id` = $id"));		
				// IDS A BORRAR
				$ids = [];
				foreach($comments as $key => $val) {
					$ids[] = $val['cid'];
				}
				$delete_ids = implode(', ', $ids);
				db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `u_muro_likes` WHERE `obj_id` IN ($delete_ids) AND `obj_type` = 2");
				// BORRAR COMENTARIOS
				db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `u_muro_comentarios` WHERE `pub_id` = $id");
				db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `u_muro_adjuntos` WHERE `pub_id` = $id");
				//
				return '1: OK';
			break;
			// ELIMINAR COMENTARIO
			case 'cmt':
				// DATOS
				$data = db_exec('fetch_assoc',db_exec([__FILE__, __LINE__], 'query', "SELECT c.cid, c.c_user, p.pub_id, p.p_user FROM u_muro_comentarios AS c LEFT JOIN u_muro AS p ON c.pub_id = p.pub_id WHERE c.cid = $id LIMIT 1"));				
				//
				if($data['cid'] === '') return '0: El comentario no existe.';
				// SI ES EL DUEÑO DEL MURO O DEL COMENTARIO...
				if((int)$data['p_user'] !== $this->User->uid || (int)$data['c_user'] !== $this->User->uid || !$this->User->is_admod || !$this->User->permiso('moderacion.muros.eliminar_comentarios')) {
					return '0: Hmmm... &iquest;Haciendo pruebas?';
				}
				if(!db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_comentarios` WHERE `cid` = \''.(int)$id.'\'')) {
					return '0: Error al eliminar comentario';
				}
				// UPDATES
				db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `u_muro_likes` WHERE `obj_id` = $id AND `obj_type` = 2");
				db_exec([__FILE__, __LINE__], 'query', "UPDATE `u_muro` SET `p_comments` = p_comments - 1 WHERE `pub_id` = {$data['pub_id']}");
				//
				return '1: Ok';
			break;
		}
	}

	/*
		likePost()
	*/
	public function likePost(): array {
		global $tsMonitor, $tsActividad;
		// ANTI FLOOD
		$text = $this->Core->antiFlood(false, 'like', 'No te pueden gustar tantas cosas en tan poco tiempo.');
		if($text !== 1) return ['status' => 'error', 'text' => $text];
		//
		$id = (int)($_POST['id'] ?? 0);
		$type = ($_POST['type'] === 'com') ? 2 : 1;
		$status = 'ok';
		// EXISTE O NO
		$sql = ($type === 1) ? "SELECT p_user AS uid FROM u_muro WHERE pub_id = {$id}" : "SELECT c_user AS uid FROM u_muro_comentarios WHERE cid = {$id}";
		$query = db_exec([__FILE__, __LINE__], 'query', $sql);
		$exists = db_exec('fetch_assoc', $query);
		
		if($exists['uid'] === '') return '0: La publicaci&oacute;n ya no existe.';
		// CHECAMOS SI YA LE GUSTA ESTO
		$likes = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT like_id, user_id FROM u_muro_likes WHERE obj_id = $id AND obj_type = $type"));
		$total = count($likes ?? 0);
		// CHECAMOS
		$iLike = 0;
		foreach($likes as $key => $val) {
			if((int)$val['user_id'] === $this->User->uid) $iLike = $val['like_id'];
		}
		// SI AUN NO ME GUSTA
		if(empty($iLike)){
			if(db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_muro_likes (user_id, obj_id, obj_type) VALUES ({$this->User->uid}, $id, $type)")) {
				// SUMAR LIKE
				if($type === 1) {
					db_exec([__FILE__, __LINE__], 'query', "UPDATE u_muro SET p_likes = p_likes + 1 WHERE pub_id = $id");
					$ac_type = ((int)$exists['uid'] === $this->User->uid) ? 0 : 2; 
				} else {
					db_exec([__FILE__, __LINE__], 'query', "UPDATE u_muro_comentarios SET c_likes = c_likes + 1 WHERE cid = $id");
					$ac_type = ((int)$exists['uid'] === $this->User->uid) ? 1 : 3;
				}
				// MONITOR
				$tsMonitor->setNotificacion(14, (int)$exists['uid'], $this->User->uid, (int)$id, (int)$type);
				// ACTIVIDAD
				$tsActividad->setActividad(11, (int)$id, (int)$ac_type);
				//
			} else $status = 'error';
		} else {
			if(db_exec([__FILE__, __LINE__], 'query', "DELETE FROM u_muro_likes WHERE like_id = $iLike")){
				// RESTAR LIKE
				if($type === 1) {
					db_exec([__FILE__, __LINE__], 'query', "UPDATE u_muro SET p_likes = p_likes - 1 WHERE pub_id = $id");
				} else {
					db_exec([__FILE__, __LINE__], 'query', "UPDATE u_muro_comentarios SET c_likes = c_likes - 1 WHERE cid = $id");
				}
			} else $status = 'error';
		}
		// RESPUESTA
		$t_likes = empty($iLike) ? (int)($total+1) : (int)($total-1);
		if($type === 1) {
			//
			$data = $this->getPubExtras((int)$id, 'likes', $t_likes);
			$link = $data['link'];
			$text = $data['text'];
		} else {
			$ed_like = ($t_likes > 1) ? 's' : '';
			$link = empty($iLike) ? 'Ya no me gusta' : 'Me gusta';
			$text = ($t_likes > 0) ? "$t_likes persona$ed_like" : '';
		}
		//
		return ['status' => $status, 'link' => $link, 'text' => $text];
	}

	/*
		showLikes()
	*/
	public function showLikes(): array {
		$id = (int)($_POST['id'] ?? 0);
		$type = ($_POST['type'] === 'com') ? 2 : 1;
		$data = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT l.user_id, u.user_name FROM u_muro_likes AS l LEFT JOIN u_miembros AS u ON l.user_id = u.user_id WHERE obj_id = $id AND obj_type = $type"));
		if(empty($data)) return ['status' => 0, 'data' => 'La publicaci&oacute;n no existe.'];
		return ['status' => 1, 'data' => $data];
	}
}
