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
require_once TS_HELPERS . '/PostHelper.php';
require_once TS_HELPERS . '/UserHelper.php';
require_once TS_UTILS . '/ImageProcessor.php';

class tsPosts {
	
	protected tsVisitas $Visitas;
	protected UserHelper $UserHelper;
	protected PostHelper $PostHelper;
	protected Extras $Extras;
	protected ImageProcessor $Processor;

	public int $postId;
	private int $cacheTTL;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
		$this->postId = $this->getPostId();
		$this->Visitas = new tsVisitas($this->Core, $this->User);
		$this->PostHelper = new PostHelper($this->Core, $this->User);;
		$this->UserHelper = new UserHelper($this->Core);
		$this->Extras = new Extras;
      $this->Processor = new ImageProcessor([
      	'storage_path' => TS_STORAGE . '/media/',
		   'type' => 'posts',
		   'id' => (int)$this->postId
		]);
		// Variables normales
		$this->cacheTTL = (int)$this->Core->settings['c_stats_cache'] * 60;
	}

	/**
	 * @access private
	 * @return int
	 */
	private function getPostId(): int {
		return (int)($_GET['id'] ?? $_POST['postid'] ?? 0);
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
	 * @return void|string
	 */
	private function redirectToPost(array $post, bool $onlyLink = false): mixed {
		$title = $this->Extras->slugify($post['post_title']);
		$url = sprintf('%s/posts/%s/%d/%s.html', $this->Core->settings['url'], $post['c_seo'], $post['post_id'], $title);
		if($onlyLink) {
			return $url;
		} else $this->Core->redirectTo($url);
	}

	private function OperatorAndOrder(?string $direction): array {
	   // Definir operador y orden
	   $isPrev = ($direction === 'prev');
	   $operator = $isPrev ? '<' : '>';
	   $order = $isPrev ? 'DESC' : 'ASC';
	   return ['operator' => $operator, 'order' => $order];
	}

	private function navigationQuery(): string {
		$sql = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 ";
		$sql .= $this->PostHelper->activeUserSqlCondition();
		return $sql;
	}

	/**
	 * @access public
	 * @return void
	 */
	public function navigatePost(): void {
	   $action = $this->getNavigationAction();
	   $sql = $this->navigationQuery();
	    if ($action === 'random') {
	      $sql .= " ORDER BY RAND() DESC LIMIT 1";
	      $query = DB::query($sql, []);
	    } else {
	    	$navigation = $this->OperatorAndOrder($action);
	      $sql .= " AND p.post_id {$navigation['operator']} :postId ORDER BY p.post_id {$navigation['order']} LIMIT 1";
	      $query = DB::query($sql, ['postId' => $this->postId]);
	   }
	   $results = $query->get_result();
	   if ($results->num_rows === 0) {
	      $this->redirectToPosts();
	      return;
	   }
	   $data = $results->fetch_assoc();
	   $this->redirectToPost($data);
	}

	/**
	 * Obtiene el título del post siguiente/anterior
	 *
	 * @param string|null $direction Dirección: 'prev' o 'next'. Si no se especifica, busca aleatorio
	 * @return array|false Datos del post o false si no existe
	 */
	public function getNearbyPostTitle(?string $direction = null): array|false {
	   $currentPostId = $this->postId;
	   $navigation = $this->OperatorAndOrder($direction);
	   // Consulta
	   $data = DB::fetch("{$this->navigationQuery()} AND p.post_id {$navigation['operator']} :postId ORDER BY post_id {$navigation['order']} LIMIT 1", ['postId' => $currentPostId]);
	   if ($data) {
	      $data['post_url'] = $this->redirectToPost($data, true);
	      return $data;
	   }
	   return false;
	}

	/**
	 * @access private
	 * @return array
	 */
	private function getDataPost(): array {
		$group = ":pid {$this->PostHelper->activeUserSqlCondition()}";
		// DATOS DEL POST
		return DB::fetch("SELECT p.* ,m.*, u.user_id FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN u_perfil AS m ON p.post_user = m.user_id WHERE post_id = {$group} LIMIT 1", ['pid' => $this->postId]);
	}

	/**
	 * @access private
	 * @param array
	 * @return array
	 */
	private function postStatus(array $postData): array {
		if((int)$postData['post_id'] === 0) {
			$tsDraft = DB::fetch("SELECT post_draft FROM p_borradores WHERE post_id = :pid LIMIT 1", ['pid' => $postData['post_id']]);
			$msg = !isset($tsDraft['post_draft']) ? 'fue eliminado!' : 'no existe o fue eliminado.';
			return ['deleted', "Oops! Este post $msg"];
		}
		if((int)$postData['post_status'] === 1 && $this->PostHelper->canPermsUsers('moacp')) {
			return ['denunciado','Oops! El Post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias.'];
		}
		if((int)$postData['post_status'] === 2 && $this->PostHelper->canPermsUsers('morp')) {
			return ['deleted','Oops! El post fue eliminado!'];
		}
		if((int)$postData['post_status'] === 3 && $this->PostHelper->canPermsUsers('mocp')) {
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
	private function refreshPostStats(array $postData) {
		$pid = (int)$this->postId;
		$data = [];
		#if($this->checkedStatsPosts((int)$postData['post_cache'])) {
			$queries = [
				'comments' => "SELECT COUNT(u.user_name) AS c FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = :pid AND c.c_status = 0 {$this->PostHelper->activeUserSqlCondition()}",
				'seguidores' => "SELECT COUNT(u.user_name) AS s FROM u_miembros AS u LEFT JOIN u_follows AS f ON u.user_id = f.f_user WHERE f.f_type = 2 AND f.f_id = :pid {$this->PostHelper->activeUserSqlCondition()}",
				'shared' => "SELECT COUNT(follow_id) AS m FROM u_follows WHERE f_type = 3 AND f_id = :pid",
				'favoritos' => "SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = :pid"
			];
			foreach($queries as $who => $sql) {
	      	$data['post_' . $who] = DB::value($sql, ['pid' => $pid]);
			}
			$data['post_cache'] = time();
			//ACTUALIZAMOS LAS ESTADÍSTICAS
			DB::update('p_posts', $data, 'post_id = :pid', ['pid' => $pid]);
			$postData += $data;
			return $postData;
		#}
	}

	/**
	 * @access private
	 * @param int x2
	 * @return bool
	 */
	private function isFollow(int $fid, ?int $type = 1): bool {
		return DB::exists("SELECT 1 FROM u_follows WHERE f_id = :fid AND f_user = :user AND f_type = :type", [
			'fid' => $fid,
			'user' => $this->User->uid,
			'type' => $type
		]);
	}

	private function createPortada(string $portada): string {
		if(empty($portada)) return '';
		$localPath = $this->Processor->process($portada);
		$fileName = basename($localPath);
      return $this->Core->settings['url'] . $this->Processor->getPublicUrl($fileName);
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
		$postData = $this->refreshPostStats($postData);
		// USUARIO BLOQUEADO?
		$postData['block'] = $this->UserHelper->isBlocked((int)$postData['post_user'], $this->User->uid);
		// FOLLOWS
		$postData['follow'] = $this->isFollow($this->postId, 2);
		// VISITANTES RECIENTES
		$postData['visitas'] = $this->Visitas->getLastViews($this->postId, 2);
		//PUNTOS
		if((int)$postData['post_user'] === $this->User->uid || $this->User->is_admod) {
			$postData['puntos'] = DB::fetchAll("SELECT p.*, u.user_id, u.user_name FROM p_votos AS p LEFT JOIN u_miembros AS u ON p.tuser = u.user_id WHERE p.tid = :tid AND p.type = 1 ORDER BY p.cant DESC", ['tid' => $this->postId]);
		}
		// CATEGORIAS
		$postData['categoria'] = DB::fetch("SELECT c.c_nombre, c.c_seo, c.c_img FROM p_categorias AS c WHERE c.cid = :cid", ['cid' => $postData['post_category']]);
		// BBCode
		$typeBody = ((int)$postData['post_smileys'] === 0) ? 'normal' : 'firma';
		$postData['post_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($postData['post_body'], $typeBody, $this->postId), true);
		// Portada
		$postData['post_portada'] = $this->createPortada($postData['post_portada']);
		// FIRMA
		$postData['user_firma'] = $this->Core->parseBadWords($this->Core->parseBBCode($postData['user_firma'] ?? '', 'firma'),true);
		// MEDALLAS
		$postData['medallas'] = DB::fetchAll("SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = :for AND m.m_type = 2 ORDER BY a.medal_date", ['for' => $this->postId]);
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
		$param = ['uid' => $userId];
		$data = DB::fetch("SELECT u.user_id, u.user_name, u.user_rango, u.user_posts, u.user_puntos, u.user_lastactive, u.user_last_ip, u.user_activo, u.user_baneado, p.user_pais, p.user_sexo, p.user_firma FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id WHERE u.user_id = :uid LIMIT 1", $param);
		// EXTRA DEL USUARIO
		$data['user_seguidores'] = DB::value("SELECT COUNT(*) FROM u_follows WHERE f_id = :uid AND f_type = 1", $param);
		$data['user_comentarios'] = DB::value("SELECT COUNT(*) FROM p_comentarios WHERE c_user = :uid AND c_status = 0", $param);
		$data['user_comentarios'] = DB::value("SELECT COUNT(*) FROM p_posts WHERE post_user = :uid AND post_status = 0", $param);
		// RANGOS DE ESTE USUARIO
		$data['rango'] = DB::fetch("SELECT r_name, r_color, r_image FROM u_rangos WHERE rango_id = :rango LIMIT 1", ['rango' => $data['user_rango']]);
		// STATUS
		$data['status'] = $this->UserHelper->getStatusCode((int)$data['user_lastactive'], (int)$data['user_baneado']);
		// PAIS
		$tsPaises = require_once dirname(__DIR__, 1) . "/extras/Paises.php";
		$userPais = empty($data['user_pais']) ? 'XX' : $data['user_pais'];
		$data['pais'] = [
			'icon' => strtolower($userPais),
			'name' => $tsPaises[$userPais]
		];
		// FOLLOWS
		$data['follow'] = $this->isFollow((int)$userId, 1);

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
			default => (int)($this->User->permiso('limites.puntos_por_post') ?? 0),
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
		$param = ['pid' => $this->postId];
		$post = DB::fetch("SELECT post_id, post_user, post_draft FROM p_posts WHERE post_id = :pid", $param);
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
		$continue = DB::update('p_posts', ['post_draft' => 2], 'post_id = :pid', $param);
		// Soft delete
		if (!$continue) {
			db_exec([__FILE__, __LINE__], 'query', 'ROLLBACK');
			return '0: Error eliminando el post.';
		}
		// Soft delete de comentarios (recomendado)
		DB::update('p_comentarios', ['c_status' => 2], 'c_post_id = :pid', $param);
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
		$exists = DB::exists("SELECT post_id, post_user FROM p_posts WHERE post_id = :pid", ['pid' => $this->postId]);
		if (!$post) {
        	return '0: El post no existe.';
   	}
   	if ($post['post_status'] != 0) { // 0 = activo, 1 = pendiente, 2 = eliminado
   	   return '0: El post no está en estado activo para eliminar.';
   	}
		DB::begin();
		try {
			DB::delete('p_comentarios', 'c_post_id = :pid', ['pid' => $this->postId]);
			DB::delete('p_posts', 'post_id = :pid', ['pid' => $this->postId]);
			
			DB::decrement('w_stats', 'stats_posts', 'stats_no = :stats_no', ['stats_no' => 1]);
			DB::decrement('u_miembros', 'user_posts', 'user_id = :user_id', ['user_id' => $post['post_user']]);
			DB::commit();
			return "1: El post se ha eliminado correctamente.";
		} catch (Exception $e) {
        	DB::rollback();
        	return '0: Error crítico: ' . $e->getMessage();
    	}		
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
		$sql = "SELECT DISTINCT p.post_id, p.post_title, p.post_category, p.post_private, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE MATCH (p.post_tags) AGAINST ('$searchTerms' IN BOOLEAN MODE) AND p.post_status = 0 AND p.post_sticky = 0 ORDER BY RAND() LIMIT 10";
		//
		return DB::fetchAll($sql);
	}

	private function verifyNoDuplicateAccount() {
		// Validar IP
	   $IP = (new IP)->getIP();
		if(!$this->User->is_admod) {
			$param = ['ip' => $IP, 'uid' => $this->User->uid];
			$existUser = DB::exists("SELECT user_id FROM u_miembros WHERE user_last_ip = :ip AND user_id != :user", $param);
			$existSession = DB::exists("SELECT session_id FROM u_sessions WHERE session_ip = :ip AND session_user_id != :user", $param);
			if($existUser || $existSession) {
				return '0: Has usado otra cuenta anteriormente, deber&aacute;s contactar con la administraci&oacute;n.';
			}
		}
		return null;
	}

	private function getMaxAllowedPoints(int $puntos) {
	   $configValue = (int)$this->Core->settings['c_allow_points'];
	   $maxPoints = match($configValue) {
	      0  => (int)$this->User->permiso('limites.puntos_por_post'),
	      -1 => (int)$this->User->info['user_puntosxdar'],
	      -2 => (int)$this->Core->settings['c_max_points_unlimited'] ?? 999999999,
	      default => max(0, $configValue)
	   };
	   // Validar cantidad de puntos
	   if ($puntos > $maxPoints) {
	      return "0: Voto no v&aacute;lido. No puedes dar $puntos puntos, s&oacute;lo se permiten $maxPoints.";
	   }
	   if ($puntos > $this->User->info['user_puntosxdar']) {
	      return "0: Voto no v&aacute;lido. No puedes dar $puntos puntos, s&oacute;lo te quedan {$this->User->info['user_puntosxdar']}.";
	   }
	}

	private function applyPostVote(int $puntos, int $postUser) {
		// Transacción para asegurar integridad
	   DB::begin();
	   try {
	   	$sentencias = [
	      	// Actualizar puntos del post
	   		"UPDATE p_posts SET post_puntos = post_puntos + :puntos WHERE post_id = :pid" => ['puntos' => $puntos,'pid' => $this->postId],
	      	// Actualizar puntos del dueño del post
	   		"UPDATE u_miembros SET user_puntos = user_puntos + :puntos WHERE user_id = :uid" => ['puntos' => $puntos,'uid' => $postUser],
	      	// Restar puntos del votante
	   		"UPDATE u_miembros SET user_puntosxdar = user_puntosxdar - :puntos WHERE user_id = :uid" => ['puntos' => $puntos,'uid' => $this->User->uid]
	   	];
	   	foreach($sentencias as $sql => $params) {
	   		DB::raw($sql, $params);
	   	}
	      // Registrar voto
	      DB::insert('p_votos', ['tid' => $this->postId,'tuser' => $this->User->uid,'cant' => $puntos,'type' => 1,'date' => time()]);
	      DB::commit();
	   } catch (\Exception $e) {
	      DB::rollback();
	      return '0: Error interno al registrar el voto.';
	   }
	}
		
	/*
		votarPost()
	*/
	public function votarPost() {
	   global $tsMonitor, $tsActividad;
	   // Verificar permisos
	   if (!($this->User->is_admod || $this->User->permiso('global.posts.puntuar'))) {
	      return '0: No tienes permiso para votar.';
	   }
	   // Validar puntos
	   $puntos = (int)($_POST['puntos'] ?? 0);
	   if ($puntos <= 0) {
	      return '0: Voto no v&aacute;lido. No puedes dar 0 puntos.';
	   }
	   $puntos = abs($puntos); // Asegurar positivo
	   // Verificar si ha usado otra cuenta
	   $otherAccountCheck = $this->verifyNoDuplicateAccount();
	   if ($otherAccountCheck !== null) {
	      return $otherAccountCheck;
	   }
	   // Obtener info del post
	   $postData = DB::fetch("SELECT post_user FROM p_posts WHERE post_id = :pid LIMIT 1", ['pid' => $this->postId]);
	   if (!$postData) {
	      return '0: El post no existe.';
	   }
	   // No votar tu propio post
	   if ((int)$postData['post_user'] === $this->User->uid) {
	      return '0: No puedes votar tu propio post.';
	   }
	   // Verificar si ya votó
	   if (DB::exists("SELECT tid FROM p_votos WHERE tid = :pid AND tuser = :uid AND type = 1 LIMIT 1", [
	      'pid' => $this->postId,
	      'uid' => $this->User->uid
	   ])) {
	      return '0: No es posible votar a un mismo post m&aacute;s de una vez.';
	   }
	   // Obtener límite de puntos
	   $this->getMaxAllowedPoints((int)$puntos);
	   $this->applyPostVote((int)$puntos, (int)$postData['post_user']);
	   // Notificación
	   $tsMonitor->setNotificacion(3, (int)$postData['post_user'], $this->User->uid, $this->postId, $puntos);
	   // Actividad
	   $tsActividad->setActividad(3, (int)$this->postId, (int)$puntos);
	   // Subir rango
	   $this->subirRango((int)$postData['post_user'], $this->postId);
	   return '1: Puntos agregados!';
	}

	/**
	 * Sube el rango del usuario basado en puntos, posts, fotos o comentarios
	 */
	public function subirRango(int $userId, ?int $postId = null): bool {
	   // Obtener puntos y rango actual del usuario
	   $userData = DB::fetch("SELECT u.user_puntos, u.user_rango, r.r_type FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = :userId LIMIT 1", ['userId' => $userId]);
	   if (!$userData) {
	      return false; // Usuario no encontrado
	   }
	   // Si tiene rango especial (tipo 0) o rango fijo (3), no actualizar
	   if ($userData['r_type'] == 0 || $userData['user_rango'] == 3) {
	      return true;
	   }
	   // Si solo se sube por puntos de un post específico
	   $puntosActual = $userData['user_puntos'];
	   if ($postId && (int)$this->Core->settings['c_newr_type'] === 0) {
	      $postPuntos = DB::value("SELECT post_puntos FROM p_posts WHERE post_id = :postId LIMIT 1", ['postId' => $postId]);
	      if ($postPuntos !== null) {
	         $puntosActual = (int)$postPuntos;
	      }
	   }
	   // Contar posts, fotos y comentarios activos del usuario
	   $posts = DB::value("SELECT COUNT(post_id) AS p FROM p_posts WHERE post_user = :userId AND post_status = 0", ['userId' => $userId]) ?: 0;
	   $fotos = DB::value("SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_user = :userId AND f_status = 0", ['userId' => $userId]) ?: 0;
	   $comentarios = DB::value("SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_user = :userId AND c_status = 0", ['userId' => $userId]) ?: 0;
	   // Obtener rangos configurados
	   $rangos = DB::fetchAll("SELECT rango_id, r_cant, r_type FROM u_rangos WHERE r_type > 0 ORDER BY r_cant");
	   $newRango = null;
	   foreach ($rangos as $rango) {
		   $cantidad = (int)$rango['r_cant'];
		   $tipo = (int)$rango['r_type'];
		   // Usar match para determinar si cumple la condición
		   $cumple = match ($tipo) {
		      1 => $puntosActual >= $cantidad,
		      2 => $posts >= $cantidad,
		      3 => $fotos >= $cantidad,
		      4 => $comentarios >= $cantidad,
		      default => false,
		   };
		   if ($cumple) {
		      $newRango = $rango['rango_id'];
		   }
		}
	   // Actualizar rango si es diferente
	   if ($newRango && $newRango != $userData['user_rango']) {
	      DB::update('u_miembros', ['user_rango' => $newRango], 'user_id = :userId', ['userId' => $userId]);
	   }
	   return true;
	}
	
	/*
		DarMedalla()
	*/
	public function DarMedalla(int $postId): void {
	   // Obtener datos principales del post
	   $param = ['pid' => $postId];
	   $data = DB::fetch("SELECT post_id, post_user, post_puntos, post_hits FROM p_posts WHERE post_id = :pid LIMIT 1", $param);
	   if (!$data) {
	      return;
	   }
	   // Obtener todas las métricas en una sola operación
	   $metrics = [
	      'followers' => DB::value("SELECT COUNT(follow_id) FROM u_follows WHERE f_id = :pid AND f_type = 2", $param) ?: 0,
	      'comments' => DB::value("SELECT COUNT(cid) FROM p_comentarios WHERE c_post_id = :pid AND c_status = 0", $param) ?: 0,
	      'favorites' => DB::value("SELECT COUNT(fav_id) FROM p_favoritos WHERE fav_post_id = :pid", $param) ?: 0,
	      'denuncias' => DB::value("SELECT COUNT(did) FROM w_denuncias WHERE obj_id = :pid AND d_type = 'post'", $param) ?: 0,
	      'medallas' => DB::value("SELECT COUNT(wm.medal_id) FROM w_medallas AS wm LEFT JOIN w_medallas_assign AS wma ON wm.medal_id = wma.medal_id WHERE wm.m_type = 2 AND wma.medal_for = :pid", $param) ?: 0,
	      'shares' => DB::value("SELECT COUNT(follow_id) FROM u_follows WHERE f_id = :pid AND f_type = 3", $param) ?: 0
	   ];
	   // Obtener medallas disponibles
	   $medallas = DB::fetchAll("SELECT medal_id, m_cant, m_cond_post FROM w_medallas WHERE m_type = 2 ORDER BY m_cant DESC");
	   foreach ($medallas as $medalla) {
	      $newmedalla = match($medalla['m_cond_post']) {
	         ($data['post_puntos'] >= $medalla['m_cant'])  => $medalla['medal_id'],
	        	($metrics['followers'] >= $medalla['m_cant']) => $medalla['medal_id'],
	         ($metrics['comments'] >= $medalla['m_cant'])  => $medalla['medal_id'],
	         ($metrics['favorites'] >= $medalla['m_cant']) => $medalla['medal_id'],
	         ($metrics['denuncias'] >= $medalla['m_cant']) => $medalla['medal_id'],
	         ($data['post_hits'] >= $medalla['m_cant']) 	 => $medalla['medal_id'],
	         ($metrics['medallas'] >= $medalla['m_cant'])  => $medalla['medal_id'],
	         ($metrics['shares'] >= $medalla['m_cant']) 	 => $medalla['medal_id'],
	         default => null
	     	};
	      if ($newmedalla) {
	         $this->asignarMedalla($newmedalla, $postId, $data['post_user']);
	      }
	   }
	}

	private function asignarMedalla(int $medalId, int $postId, int $postUser): void {
	   $exists = DB::exists("SELECT 1 FROM w_medallas_assign WHERE medal_id = :mid AND medal_for = :mfor", ['mid' => $medalId, 'mfor' => $postId]);
	   if ($exists) {
	      return;
	   }
	   DB::insert('w_medallas_assign', [
	      'medal_id' => $medalId,
	      'medal_for' => $postId,
	      'medal_date' => time(),
	      'medal_ip' => (new IP)->getIP() ?? ''
	   ]);
	   DB::insert('u_monitor', [
	      'user_id' => $postUser,
	      'obj_uno' => $medalId,
	      'obj_dos' => $postId,
	      'not_type' => 16,
	      'not_date' => time()
	   ]);
	   DB::query("UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = ?", [$medalId]);
	}

	
}