<?php

/**
 * @name c.favoritos.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsFavoritos {

	protected Extras $Extras;

	private int $postId;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
		$this->Extras = new Extras;
		$this->postId = $this->getPostId();
	}

	private function getPostId(): int {
		return (int)($_POST['postid'] ?? 0);
	}

	private function whoIsThePosts(): int {
		$user = DB::fetch("SELECT post_user FROM p_posts WHERE post_id = :pid", ['pid' => $this->postId]);
		return (int)$user['post_user'];
	}

	private function IAlreadyHaveIt(): bool {
		return DB::exists("SELECT fav_id FROM p_favoritos WHERE fav_post_id = :pid AND fav_user = :user LIMIT 1", ['pid' => $this->postId, 'user' => $this->User->uid]);
	}

	private function createLinkPost(array $post): string {
		$title = $this->Extras->slugify($post['post_title']);
		$url = sprintf('%s/posts/%s/%d/%s.html', $this->Core->settings['url'], $post['c_seo'], $post['post_id'], $title);
		return $url;
	}

	/*
		saveFavorito()
	*/
	public function saveFavorito() {
		global $tsMonitor, $tsActividad;
		# ANTIFLOOD
		$fecha = (int)($_POST['reactivar'] ?? time());
		/* DE QUIEN ES EL POST */
		$postUser = $this->whoIsThePosts();
		if($postUser === $this->User->uid) {
			return '0: No puedes agregar tus propios post a favoritos.';
		}
		// YA LO TENGO?
		if($this->IAlreadyHaveIt()){
			return '0: Este post ya lo tienes en tus favoritos.';
		}
		$insert = DB::insert("p_favoritos", [
			'fav_user' => $this->User->uid, 
			'fav_post_id' => $this->postId, 
			'fav_date' => $fecha
		]);
		if(!$insert) {
			return '0: Hubo un problema al guardarlo en favoritos.';
		}
		// AGREGAR AL MONITOR
		$tsMonitor->setNotificacion(1, $postUser, $this->User->uid, $this->postId);
		// ACTIVIDAD 
		$tsActividad->setActividad(2, $this->postId);
		return '1: Bien! Este post fue agregado a tus favoritos.';
	}

	/*
		getFavoritos()
	*/
	public function getFavoritos(): array {
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT f.fav_id, f.fav_date, p.post_id, p.post_title, p.post_date, p.post_puntos, COUNT(p_c.c_post_id) as post_comments, c.c_nombre, c.c_seo, c.c_img FROM p_favoritos AS f LEFT JOIN p_posts AS p ON p.post_id = f.fav_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN p_comentarios AS p_c ON p.post_id = p_c.c_post_id && p_c.c_status = 0 WHERE f.fav_user = :user AND p.post_status = 0 GROUP BY c_post_id');
		$data = DB::fetchAll($query, ['user' => $this->User->uid]);
		foreach($data as $pid => $post) {
			$data[$pid]['url'] = $this->createLinkPost($post);
		}
		//
		return $data;
	}

	/*
		delFavorito()
	*/
	public function delFavorito() {
		$favId = (int)($_POST['fav_id'] ?? 0);
		$params = ['fid' => $favId, 'user' => $this->User->uid];
		$data = DB::fetch("SELECT fav_post_id FROM p_favoritos WHERE fav_id = :fid AND fav_user = :user LIMIT 1", $params);
		// ES MI FAVORITO?
		if(empty($data['fav_post_id'])) {
			return '0: No se pudo borrar, no es tu favorito.';
		}	
		if(!DB::delete('p_favoritos', 'fav_id = :fid AND fav_user = :user', $params)) {
			return '0: No se pudo borrar.';
		}
		return '1: Favorito borrado.';
	}
}