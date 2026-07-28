<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

if ( ! defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class tsPortal {

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected Paginator $Paginator
	) {}

	/** getFotos()
	 * @access public
	 * @param
	 * @return array
	 */
	public function getFotos(): ?array {
		return Container::get(tsFotos::class)->getLastFotos();
	}

	/** getStats()
	 * @access public
	 * @param
	 * @return array
	 */
	public function getStats(): array {
		return Container::get(tsTops::class)->getStats();
	}

	/** getNews()
	 * @access public
	 * @param
	 * @return array
	*/
	public function getNews(): ?array {
		return Container::get(tsMuro::class)->getNews();
	}

	/** setPostsConfig()
	 * @access public
	 * @param
	 * @return string
	 */
	public function savePostsConfig(): string {
		$cids = explode(',', $_POST['cids'] ?? '');
		$cat_ids = array_filter(array_map('intval', $cids));
		$ok = DB::update('u_portal', ['last_posts_cats' => json_encode($cat_ids)], 'user_id = :uid', [
			'uid' => $this->User->uid
		]);
		return $ok ? '1: Tus cambios fueron aplicados.' : '0: Inténtalo mas tarde.';
	}

	/** composeCategories()
	 * @access public
	 * @param array
	 * @return array
	 */
	public function composeCategories(): array {
		$data = DB::fetch("SELECT last_posts_cats FROM u_portal WHERE user_id = :uid", [
			'uid' => $this->User->uid
		]);
		$data = json_decode((string)($data['last_posts_cats'] ?? ''), true) ?: [];
		foreach($this->Core->getCategorias() as $key => $cat){
		    $cat['check'] = in_array((int)$cat['cid'], array_map('intval', $data), true) ? 1 : 0;
		    $categories[] = $cat;
		}
		return $categories;
	}

	/** getMyPosts()
	 * @access public
	 * @return array|false
	 */
	public function getMyPosts(): array|false {
	    $raw = DB::value("SELECT `last_posts_cats` FROM `u_portal` WHERE `user_id` = :uid", [
	    	'uid' => $this->User->uid
	    ]);
	    $cat_ids = json_decode((string)$raw, true);
	    if (!is_array($cat_ids) || empty($cat_ids)) {
	        return false;
	    }
	    // nunca confiar en lo guardado
	    $cat_ids = array_values(array_unique(array_map('intval', $cat_ids)));
	    if (empty($cat_ids)) {
	        return false;
	    }

	    $placeholders = [];
	    $params = [];
	    foreach ($cat_ids as $i => $cid) {
	        $key = "cat{$i}";
	        $placeholders[] = ":{$key}";
	        $params[$key] = $cid;
	    }
	    $inClause = implode(',', $placeholders);
	    $params = ['status' => 'publicado', ...$params];
	    $total = DB::value("SELECT COUNT(p.post_id) FROM p_posts AS p WHERE p.post_status = :status AND p.post_category IN ({$inClause})", $params);
	    if (!$total || $total <= 0) {
	        return false;
	    }
	    $pages = $this->Paginator->getPagination((int)$total, 20);
	    $posts['data'] = DB::fetchAll("SELECT p.post_id, p.post_category, p.post_title, p.post_date, p.post_puntos, p.post_private, u.user_name, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND p.post_category IN ({$inClause}) ORDER BY p.post_id DESC LIMIT {$pages['limit']}", $params);
	    $posts['pages'] = $pages;
	    return $posts;
	}

	/** getLastPosts()
	 * @access public
	 * @param string
	 * @return array
	 */
	public function getLastPosts(string $type = 'visited'): array {
		$column = "last_posts_{$type}";
		$dato = DB::fetch("SELECT {$column} FROM u_portal WHERE user_id = :uid LIMIT 1", [
			'uid' => $this->User->uid
		]);

		$visited = json_decode((string)$dato[$column], true);
		krsort($visited);
		// LO HAGO ASI PARA ORDENAR SIN NECESITAR OTRA VARIABLE
		foreach($visited as $key => $id){
			$data[] = DB::fetch("SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_puntos, p.post_private, u.user_name, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND p.post_id = :id LIMIT 1", [
				'status' => 'publicado',
				'id' => $id
			]);
		}
		//
		return $data;
	}

	/** getFavorites()
	 * @access public
	 * @param
	 * @return array
	 */
	public function getFavorites(): array {
		$total = DB::fetch("SELECT COUNT(fav_id) AS total FROM `p_favoritos` WHERE `fav_user` = :uid", [
			'uid' => $this->User->uid
		]);

		$pages = ($total['total'] > 0) ? $this->Paginator->getPagination($total['total'], 20) : ['limit' => 0];
		$data['data'] = DB::fetchAll("SELECT f.fav_id, f.fav_date, p.post_id, p.post_title, p.post_date, p.post_puntos, p.post_category, p.post_private, COUNT(p_c.c_post_id) as post_comments,  c.c_nombre, c.c_seo, c.c_img FROM p_favoritos AS f LEFT JOIN p_posts AS p ON p.post_id = f.fav_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN p_comentarios AS p_c ON p.post_id = p_c.c_post_id AND p_c.c_status = 0 WHERE f.fav_user = :uid AND p.post_status = :status GROUP BY c_post_id ORDER BY f.fav_date DESC LIMIT :limit", [
			'uid' => $this->User->uid,
			'status' => 'publicado',
			'limit' => $pages['limit']
		]);
		//
		$data['pages'] = $pages;
		//
		return $data;
	}
}
