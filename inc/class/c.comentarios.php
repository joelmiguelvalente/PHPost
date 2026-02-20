<?php

/**
 * @name c.comentarios.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_HELPERS . '/PostHelper.php';

class tsComentarios {
	
	protected tsCore $Core;
	protected tsUser $User;
	protected PostHelper $PostHelper;
	protected Paginator $Paginator;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		$this->Paginator = new Paginator;
		$this->PostHelper = new PostHelper($this->Core, $this->User);
	}

	private function getAdmodConditions(string $prefix = 'c', bool $withPost = false): string {
      return $this->User->is_admod ? '' : ' AND '.$prefix.'.c_status = 0 AND u.user_activo = 1 AND u.user_baneado = 0' . ($withPost ? " AND p.post_status = 0" : '');
    }

	/*
		getLastComentarios()
		: PARA EL PORTAL
	*/
	public function getLastComentarios() {
		$admod = $this->getAdmodConditions('cm', true);
		$query = DB::fetchAll("SELECT cm.cid, cm.c_status, u.user_name, u.user_activo, u.user_baneado, p.post_id, p.post_title, p.post_status, c.c_seo FROM p_comentarios AS cm LEFT JOIN u_miembros AS u ON cm.c_user = u.user_id LEFT JOIN p_posts AS p ON p.post_id = cm.c_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category $admod ORDER BY cid DESC LIMIT 10");
		return $query;
	}

	private function processComment(array $comment): array {
      $votado = DB::exists("SELECT 1 FROM p_votos WHERE tid = :tid AND tuser = :tuser AND type = 2",
      [
         'tid' => $comment['cid'],
         'tuser' => $this->User->uid
      ]) ? 1 : 0;

      $isBlocked = DB::exists("SELECT 1 FROM u_bloqueos WHERE b_user = :buser AND b_auser = :bauser",
      [
         'buser' => $comment['c_user'],
         'bauser' => $this->User->uid
      ]);
      $c_body = $this->Core->parseBBCode($comment['c_body']);
      $c_html = $this->Core->parseBadWords($c_body, true);

      return [
         ...$comment,
         'votado' => $votado,
         'blocked' => $isBlocked ? 1 : 0,
         'c_html' => $comment['c_body'],
         'c_body' => $c_html
      ];
   }

   public function getComentarios(int $postId): array {
      $admodConditions = $this->getAdmodConditions();
      // Paginación
      $limit = (int)$this->Core->settings['c_max_com'];
      $start = $this->Paginator->setPageLimit($limit);

      $commentsQuery = "SELECT u.user_name, u.user_activo, u.user_baneado, c.* FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = :post_id $admodConditions ORDER BY c.cid LIMIT $start";
      $comments = DB::fetchAll($commentsQuery, ['post_id' => $postId]);
      // Conteo de comentarios
      $countQuery = "SELECT COUNT(*) as total FROM p_comentarios c LEFT JOIN u_miembros u ON c.c_user = u.user_id WHERE c.c_post_id = :post_id $admodConditions";
      $total = (int)DB::fetch($countQuery, ['post_id' => $postId])['total'];
      // Procesamiento de comentarios
      $processedComments = array_map(fn($comment) => $this->processComment($comment), $comments);
	   $blockValue = 0;
	   if (!empty($processedComments)) {
	      // Tomar el valor del ÚLTIMO comentario (como hacía el código original)
	      $lastComment = end($processedComments);
	      $blockValue = $lastComment['blocked'] ? 1 : 0;
	   }
	   return [
	      'num' => $total,
	      'data' => $processedComments,
	      'block' => $blockValue
	   ];
   }

	private function cleanComment(): string {
	   // Límite de 1500 caracteres (soporte UTF-8 completo)
	   $comentario = trim($_POST['comentario'] ?? '');
	   $comentario = mb_substr($comentario, 0, 1500, 'UTF-8');
	   // Verificación eficiente: ¿contiene al menos 1 carácter visible?
	   if (!preg_match('/\S/u', $comentario)) {
	      return '0: El campo <b>Comentario</b> es requerido para esta operaci&oacute;n';
	   }
	   return $comentario;
	}

	/*
		newComentario()
	*/
	public function newComentario(): array|string {
		global $tsActividad;
		$postId = (int)($_POST['postid'] ?? 0);
		/* DE QUIEN ES EL POST */
		$data = DB::fetch("SELECT post_user, post_block_comments FROM p_posts WHERE post_id = :pid LIMIT 1", ['pid' => $postId]);
		$comentario = $this->cleanComment();
		$most_resp = isset($_POST['mostrar_resp']) ? true : false;
		$fecha = time();
		//
		if(!$data['post_user']) {
			return '0: El post no existe.';
		}
		if ((int)$data['post_block_comments'] === 1 && (int)$data['post_user'] !== $this->User->uid && (!$this->User->is_admod || !$this->User->permiso('moderacion.posts.comentarios_cerrado'))) {
		   return '0: El post se encuentra cerrado y no se permiten comentarios.';
		}
		if(!$this->User->is_admod && $this->User->permiso('global.posts.comentar') === false) {
			return '0: No deber&iacute;as hacer estas pruebas.';
		}
		// ANTI FLOOD
		$this->Core->antiFlood();
		$MyIP = (new IP)->getIP();
		if(!DB::insert('p_comentarios', [
			'c_post_id' => $postId,
			'c_user' => $this->User->uid,
			'c_date' => $fecha,
			'c_body' => $comentario,
			'c_ip' => $MyIP
		])) {
			return '0: Ocurri&oacute; un error int&eacute;ntalo m&aacute;s tarde.';
		}
		$cid = DB::insertId();
		//SUMAMOS A LAS ESTADÍSTICAS
		DB::increment('w_stats', 'stats_comments', 'stats_no = :sid', ['sid' => 1]);
		DB::increment('p_posts', 'post_comments', 'post_id = :pid', ['pid' => $postId]);
		DB::increment('u_miembros', 'user_comentarios', 'user_id = :uid', ['uid' => $this->User->uid]);
		// NOTIFICAR SI FUE CITADO Y A LOS QUE SIGUEN ESTE POST, DUEÑO
		$this->quoteNoti((int)$postId, (int)$data['post_user'], (int)$cid, $comentario);
		// ACTIVIDAD
		$tsActividad->setActividad(5, $postId);
		// array(comid, comhtml, combbc, fecha, autor_del_post)
		if($most_resp) {
			return [$cid, $this->Core->parseBadWords($this->Core->parseBBCode($comentario), true), $comentario, $fecha, $_POST['auser'], '', $MyIP
			];
		}
		return '1: Tu comentario fue agregado satisfactoriamente.';
	}

	/*
		quoteNoti()
		:: Avisa cuando citan los comentarios.
	*/
	public function quoteNoti(int $postId, int $post_user, int $cid, string $comentario): bool {
		global $tsMonitor;
	   // Validación temprana de IDs válidos
	   if ($postId <= 0 || $post_user <= 0 || $cid <= 0) {
	      return false;
	   }
	   $quotedUserIds = [];
	   $currentUser = $this->User->uid;
	   $currentNick = $this->User->nick;
	   // Procesamiento eficiente de citas usando array asociativo para O(1) lookups
	   if (preg_match_all('/\[quote=([^\]]+)\]/is', $comentario, $matches)) {
	      foreach ($matches[1] as $userString) {
	         // Parsear username y comment ID de forma segura
	         [$username, $commentId] = $this->parseQuoteUser($userString, $cid);
	         // Validación centralizada
	         if (!$this->isValidQuoteTarget($username, $currentNick, $currentUser, $post_user)) {
	            continue;
	         }
	         $userId = $this->User->getUserID($this->Core->setSecure($username));
	         if ($userId <= 0 || $userId === $currentUser) {
	            continue;
	         }
	         // Evitar duplicados con array asociativo (O(1) vs O(n))
	         if (!isset($quotedUserIds[$userId])) {
	            $quotedUserIds[$userId] = $commentId;
	            $tsMonitor->setNotificacion(9, $userId, $currentUser, $postId, $commentId);
	         }
	      }
	   }
	   // Notificar al dueño del post si no fue citado
	   if ($post_user !== $currentUser && !isset($quotedUserIds[$post_user])) {
	      $tsMonitor->setNotificacion(2, $post_user, $currentUser, $postId);
	   }
	   // Notificar seguidores excluyendo citados y autor
	   $excludeIds = array_keys($quotedUserIds);
	   $excludeIds[] = $currentUser;
	   $tsMonitor->setFollowNotificacion(7, 2, $currentUser, $postId, 0, $excludeIds);
	   return true;
	}

	/**
	 * Parsea los datos de usuario de una etiqueta [quote]
	 */
	private function parseQuoteUser(string $userString, int $defaultCid): array {
	   $parts = explode('|', trim($userString), 2);
	   $username = trim($parts[0]);
	   $commentId = isset($parts[1]) ? (int)$parts[1] : $defaultCid;
	   return [$username, max(1, $commentId)]; // Asegurar commentId válido
	}

	/**
	 * Verifica si el usuario es un objetivo válido para notificación
	 */
	private function isValidQuoteTarget(string $username, string $currentNick, int $currentUser, int $postUser): bool {
	   return !empty($username) && $username !== $currentNick &&$username !== '' &&$username !== (string)$currentUser &&$username !== (string)$postUser;
	}

	/*
		editComentario()
	*/
	public function editComentario(): string {
		$cid = (int)($_POST['cid'] ?? 0);
		$comentario = $this->cleanComment();
		$query = DB::fetch("SELECT c_user FROM p_comentarios WHERE cid = :cid LIMIT 1", ['cid' => $cid]);
		//
		if(!$this->User->is_admod || ($this->User->uid != $query['c_user'] && !$this->User->permiso('global.comentarios.editar_propios')) || !$this->User->permiso('moderacion.posts.editar_comentarios')) {
			return '0: Hey, este comentario no es tuyo.';
		}
		// ANTI FLOOD
		$this->Core->antiFlood();
		$update = DB::update('p_comentarios', ['c_body' => $comentario], 'cid = :cid', ['cid' => $cid]);
		return ($update) ? '1: El comentario fue editado.' : '0: Ocurri&oacute; un error :(';
	}

	/* 
		delComentario()
	*/
	public function delComentario() {
	   $comid = (int)($_POST['comid'] ?? 0);
	   $comment = DB::fetch("SELECT c_post_id FROM p_comentarios WHERE cid = :cid", ['cid' => $comid]);
	   //
	   if (!$comment) return '0: El comentario no existe';
	   $postId = $comment['c_post_id'];
	   //
	   $isMyPost = DB::exists("SELECT 1 FROM p_posts WHERE post_id = :pid AND post_user = :uid", ['pid' => $postId, 'uid' => $this->User->uid]);
	   $isMyComment = DB::exists("SELECT 1 FROM p_comentarios WHERE cid = :cid AND c_user = :uid", ['cid' => $comid, 'uid' => $this->User->uid]);
	   //
	   if (!$isMyPost && (!$isMyComment || !$this->User->permiso('global.comentarios.eliminar_propios')) && (!$this->User->is_admod || !$this->User->permiso('moecp'))) {
	      return '0: No tienes permiso';
	   }

	   DB::begin();
	   try {
	      DB::delete('p_comentarios', 'cid = :cid AND c_post_id = :pid', ['cid' => $comid, 'pid' => $postId]);
	      DB::delete('p_votos', 'tid = :tid', ['tid' => $comid]);
	      DB::decrement('w_stats', 'stats_comments', 'stats_no = 1', ['stats_no' => 1]);
	      DB::decrement('p_posts', 'post_comments', 'post_id = :pid', ['pid' => $postId]); 
	      
	      DB::commit();
	      return '1: Comentario borrado.';
	   } catch (Exception $e) {
	      DB::rollback();
	      return '0: Error crítico: ' . $e->getMessage();
	   }
	}

	public function OcultarComentario() {
   	if (!$this->User->is_admod || !$this->User->permiso('moderacion.posts.revision')) {
   	   return '0: No tienes permiso para hacer eso.';
   	}
   	$comid = (int)($_POST['comid'] ?? 0);
   	//
   	$data = DB::fetch("SELECT c_user, c_post_id, c_status FROM p_comentarios WHERE cid = :cid", ['cid' => $comid]);
   	if (!$data) {
   	   return '0: El comentario no existe';
   	}
   	// 
    	DB::begin();
    	try {
    		$status = ((int)$data['c_status'] === 1);
     		$operation = $status ? '-' : '+';
     		$newStatus = $status ? 0 : 1;
     		DB::update('p_comentarios', ['c_status' => $newStatus], 'cid = :cid', ['cid' => $comid]);
     		DB::update('w_stats', ['stats_comments' => DB::raw("stats_comments $operation 1")], 'stats_no = 1');
     		DB::update('p_posts', ['post_comments' => DB::raw("post_comments $operation 1")], 'post_id = :pid', ['pid' => $data['c_post_id']]);
     		DB::update('u_miembros', ['user_comentarios' => DB::raw("user_comentarios $operation 1")], 'user_id = :uid', ['uid' => $data['c_user']]);
     		DB::commit();
     		// 
     		return $status ? '1: El comentario fue ocultado.' : '2: El comentario fue habilitado.';
    	} catch (Exception $e) {
        	DB::rollback();
        	return '0: Error crítico: ' . $e->getMessage();
    	}
	}

	/*
		votarComentario()
	*/
	public function votarComentario(){
		global $tsMonitor, $tsActividad;
   	// Sanitización SEGURA de parámetros
   	$cid = (int)($_POST['cid'] ?? 0);
   	$postId = (int)($_POST['postid'] ?? 0);
   	$votoVal = (isset($_POST['voto']) && $_POST['voto'] === '1') ? 1 : 0; 
   	// Verificación CORRECTA de permisos (sin redundancias)
   	$permiso = $this->User->is_admod;
   	$globalPermiso = ($votoVal === 1) ? 'positivo' : 'negativo';
   	if (!$permiso) {
   	   $permiso = $this->User->permiso("global.posts.votar_{$globalPermiso}");
   	}
   	if (!$permiso) {
   	   return '0: No tienes permiso para votar ' . $globalPermiso;
   	}
   	// Verificar existencia del comentario y autor
	   $comentario = DB::fetch("SELECT c_user FROM p_comentarios WHERE cid = :cid", ['cid' => $cid]);
	   if (!$comentario) {
	      return '0: El comentario no existe';
	   }
	   // Evitar votar propio comentario
	   if ($comentario['c_user'] === $this->User->uid) {
	      return '0: No puedes votar tu propio comentario';
	   }
	   // Verificar voto duplicado con EXISTS (más eficiente)
	   $yaVoto = DB::exists("SELECT 1 FROM p_votos WHERE tid = :tid AND tuser = :tuser AND type = 2", [
	      'tid' => $cid,
	      'tuser' => $this->User->uid
	   ]);
	   if ($yaVoto) {
	      return '0: Ya has votado este comentario';
	   }
	   // TRANSACCIÓN COMPLETA para atomicidad
	   DB::begin();
	   try {
	      // Lógica CORRECTA de votos (contadores separados)
	      $column = ($votoVal === 1) ? 'c_votos_pos' : 'c_votos_neg';
	      DB::update('p_comentarios', [$column => DB::raw($column.' + 1')], 'cid = :cid', ['cid' => $cid]);
	      // Insertar voto con binding seguro
	      DB::insert('p_votos', ['tid' => $cid, 'tuser' => $this->User->uid, 'type' => 2, 'voto' => $votoVal]);
	      // Actualizar puntos SOLO para votos positivos (y con transacción)
	      if ($votoVal === 1 && (int)$this->Core->settings['c_allow_sump'] === 1) {
	         DB::update('u_miembros', ['user_puntos' => DB::raw('user_puntos + 1')], 'user_id = :uid',  ['uid' => $comentario['c_user']]);
	         $this->subirRango($comentario['c_user']);
	      }
	      // Notificaciones DENTRO de la transacción (coherencia)
	      $tsMonitor->setNotificacion(8, $comentario['c_user'], $this->User->uid, $postId, $cid, $votoVal);
	      $tsActividad->setActividad(6, $postId, $votoVal);
	      DB::commit();
	      return '1: Gracias por tu voto';
	   } catch (Exception $e) {
	      DB::rollback();
	      return '0: Error al votar: ' . $e->getMessage();
	   }
	}

}