<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

require_once __DIR__ . '/c.moderacion.php';

class tsAgregar {
	
	public int $postId;

	private string $myIP;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User,
		protected IP $IP
	) {
		$this->postId = $this->getPostId();
		$this->myIP = $this->IP->getIPBinary();
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
		return (!$this->User->is_admod && ((int)$this->Core->settings['c_desapprove_post'] === 1 || $this->User->permiso('global.posts.revisar') === true));
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
		$data = DB::fetchAll("SELECT p.post_id, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 'publicado' $where AND MATCH(p.post_title) AGAINST(:search IN BOOLEAN MODE) ORDER BY RAND() DESC LIMIT 5", ['search' => $search]);
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
		$cuerpo = Html::escape($_POST['cuerpo'], true);
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
		if (count($items ?? []) < $min) {
			return false;
		}
		foreach ($items as $tag) {
			if (!preg_match('/^[\p{L}\p{N}_-]+$/u', $tag)) {
				return false;
			}
		}
		return true;
	}

	private function normalizeLineBreaks(string $content): string {
    	// Convierte todos los saltos a LF (\n) - ESTÁNDAR UNIVERSAL
   	return preg_replace('/\r\n|\r/', "\n", $content);
	}

	private function collectPostData(bool $newPost = true): array {
		$postData = [
			'post_title' => $this->Core->parseBadWords($this->normalizeLineBreaks($_POST['title'])),
			'post_portada' => trim((string)$_POST['portada'] ?? ''),
			'post_body' => $this->normalizeLineBreaks($_POST['body']),
			'post_tags' => $this->Core->parseBadWords(Html::escape($_POST['tags'], true)),
			'post_category' => (int)$_POST['category'],
			'post_status' => $_POST['status'] ?? 'publicado'
		];
		if($newPost) {
			$postData['post_date'] = time();
		}
		return $postData;
	}

	private function validatePostData(array $data): bool {
		if (trim($data['post_title']) === '') {
			return false;
		}
		if (trim(strip_tags($data['post_body'])) === '') {
			return false;
		}
		if (!$this->validTags($data['post_tags'])) {
			return false;
		}
		if ($data['post_category'] <= 0) {
			return false;
		}
		return true;
	}

	private function applyOptionalFlags(array &$postData): void {
		$keys = ['visitantes', 'smileys', 'private', 'block_comments', 'sponsored', 'sticky'];
		foreach ($keys as $key) {
			$value = isset($_POST[$key]) && $_POST[$key] === 'on' ? 1 : 0;
			if (in_array($key, ['sponsored', 'sticky'], true)) {
				$value = ($this->User->is_admod || $this->User->permiso('moderacion.posts.fijar') === false) ? $value : 0;
			}
			$postData['post_' . $key] = $value;
		}
	}

	private function moderatePost(int $postId): void {
		DB::insert('w_historial', [
			'pofid' => $postId,
			'action' => 3,
			'type' => 1,
			'mod' => $this->User->uid,
			'reason' => 'Revisión al publicar',
			'date' => time(),
			'mod_ip' => $this->myIP
		]);
	}
	
	/*
		newPost()
	*/
	public function newPost(): string|int {
		global $tsMonitor, $tsActividad;
		//
		if(!($this->User->is_admod || $this->User->permiso('global.posts.publicar'))) {
			return 'No tienes permiso para crear posts.';
		}
		//
		$postData = $this->collectPostData();
		$exists = DB::value("SELECT COUNT(*) FROM p_posts WHERE post_body = :body", ['body' => $postData['post_body']]);
		if ($exists > 0) return 'Contenido duplicado.';
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
		$query = DB::exists("SELECT 1 FROM p_categorias WHERE cid = :cid LIMIT 1", ['cid' => $postData['post_category']]);
		if(!$query) return 'La categoría especificada no existe.';
		// INSERTAMOS
		$time = time();
		DB::begin();
		try {
			$postData['post_body'] = $this->normalizeLineBreaks($postData['post_body']);
			$postId = DB::insert('p_posts', [
				'post_user' => $this->User->uid,
				'post_category' => $postData['post_category'],
				'post_title' => $postData['post_title'],
				'post_body' => $postData['post_body'],
				'post_portada' => $postData['post_portada'],
				'post_date' => time(),
				'post_tags' => $postData['post_tags'],
				'post_ip' => $this->myIP,
				'post_private' => $postData['post_private'],
				'post_block_comments' => $postData['post_block_comments'],
				'post_sponsored' => $postData['post_sponsored'],
				'post_sticky' => $postData['post_sticky'],
				'post_smileys' => $postData['post_smileys'],
				'post_visitantes' => $postData['post_visitantes'],
				'post_status' => $this->postApproved() ? 'revision' : $postData['post_status']
			]);
			// Si está oculto, lo creamos en el historial e.e
			if($this->postApproved()) $this->moderatePost($postId);
			// ESTADÍSTICAS
			DB::increment('w_stats', 'stats_posts', 'stats_no = :stats_no', ['stats_no' => 1]);
			// ULTIMO POST
			DB::update('u_miembros', ['user_lastpost' => $time], 'user_id = :uid', ['uid' => $this->User->uid]);
			// AGREGAR AL MONITOR DE LOS USUARIOS QUE ME SIGUEN
			$tsMonitor->setFollowNotificacion(5, 1, (int)$this->User->uid, (int)$postID);
			// REGISTRAR MI ACTIVIDAD
			$tsActividad->setActividad(1, (int)$postID);
			// SUBIR DE RANGO?
			#$this->subirRango((int)$this->User->uid);
			DB::commit();
			return $postId;
		} catch (Exception $e) {
			DB::rollback();
			return 'Error al crear post: ' . $e->getMessage();
		}
	}

	/*
		savePost()
	*/
	public function savePost(): string|int {
		$postId = $this->postId;
		$data = DB::fetch("SELECT post_user, post_sponsored, post_sticky, post_status FROM p_posts WHERE post_id = :pid LIMIT 1", ['pid' => $postId]);
		//
		if($data['post_status'] !== 'publicado' && !$this->User->is_admod && !$this->User->permiso('moderacion.posts.editar')) {
			return 'El post no puede ser editado.';
		}
		$postData = $this->collectPostData(false);
		if (!$this->validatePostData($postData)) {
			return 'Datos inválidos.';
		}
		// Pueden ir vacios
		$this->applyOptionalFlags($postData);
		// ACTUALIZAMOS
		if($this->User->uid === (int)$data['post_user'] || !$this->User->is_admod || !$this->User->permiso('moderacion.posts.editar')) {
			if(!DB::update('p_posts', $postData, 'post_id = :pid', ['pid' => $postId])) {
				return 'No se puede actualizar';
			}
			// GUARDAR EN EL HISTORIAL	DE MODERACION
			$razon = (string)($_POST['razon'] ?? 'Sin motivo');
			if(($this->User->is_admod || $this->User->permiso('moderacion.posts.editar')) && $this->User->uid !== (int)$data['post_user'] && $razon) {
				$tsModeracion = Container::get(tsModeracion::class);
				return $tsModeracion->setHistory('editar', 'post', [
					'post_id' => $postId, 
					'title' => $postData['post_title'], 
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
	public function getEditPost(): string|array {
		$pid = $this->postId;
		$post = DB::fetch("SELECT * FROM p_posts WHERE post_id = :pid LIMIT 1", ['pid' => $pid]);
		//
		if(empty($post['post_id'])) {
			return 'El post elegido no existe.';
		}
		if($post['post_status'] !== 'publicado' && !$this->User->is_admod && !$this->User->permiso('moderacion.posts.editar')) {
			return 'El post no puede ser editado.';
		}
		if(($this->User->uid !== (int)$post['post_user']) && !$this->User->is_admod && !$this->User->permiso('moderacion.posts.editar')) {
			return 'No puedes editar un post que no es tuyo.';
		}
		//
		return $post;
	}

	public function getPostDataID(int $tsPost = 0): string {
		$pid = ($tsPost === 0) ? (int)$_GET['id'] : (int)$tsPost;
		$tsCat = DB::fetch("SELECT c_seo FROM p_categorias WHERE cid = :cat", 
			['cat' => (int)($_POST['category'] ?? 0)
		]);
		//
		$seoTitle = Extras::slugify($_POST['title'], '-');
		return "{$this->Core->settings['url']}/posts/{$tsCat['c_seo']}/$pid/{$seoTitle}.html";
	}

}
