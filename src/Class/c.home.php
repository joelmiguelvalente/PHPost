<?php

/**
 * @name src/Class/c.home.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsHome {
	
	protected Paginator $Paginator;
	protected Extras $Extras;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
		$this->Paginator = new Paginator;
		$this->Extras = new Extras;
	}
	
	/**
	 * @access public
	 * @return array
	 */
	public function getDataCategorie(): array {
		$seo = $this->Core->setSecure($_GET['cat']);
		return DB::fetch("SELECT c_nombre, c_seo FROM p_categorias WHERE c_seo = :seo LIMIT 1", ['seo' => $seo]);
	}

	private function getCategoryId(string $category): ?int {
		if ($category === '') {
			return null;
		}
		$categorySeo = $this->Core->setSecure($category);
		$row = DB::fetch("SELECT cid FROM p_categorias WHERE c_seo = :seo LIMIT 1", ['seo' => $categorySeo]);
		return !empty($row['cid']) ? (int) $row['cid'] : null;
	}

	private function canSeeHiddenPosts(): bool {
		return ($this->User->is_admod === 1 && (int)$this->Core->settings['c_see_mod'] === 1);
	}

	private function countPosts(string $visibilityWhere, string $categoryWhere, string $stickyWhere): int {
		$sql = "SELECT COUNT(p.post_id) AS total FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE {$visibilityWhere} {$categoryWhere} {$stickyWhere}";
		$row = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', $sql));
		return (int)($row[0] ?? 0);
	}

	private function getPosts(bool $sticky, string $category = ''): array {
		// Categoria
		$categoryId = $this->getCategoryId($category);
		$categoryWhere = $categoryId !== null ? "AND p.post_category = {$categoryId}" : '';
		// Post Fijado si/no
		$stickyWhere = $sticky ? 'AND p.post_sticky = 1' : 'AND p.post_sticky = 0';
		// Tipo de usuario admin/comun
		$visibilityWhere = $this->canSeeHiddenPosts() ? '' : 'u.user_activo = 1 AND u.user_baneado = 0 AND p.post_status = 1';

		$orderBy = $sticky ? 'p.post_sponsored' : 'p.post_id';
		// Paginacion
		$total = $this->countPosts($visibilityWhere, $categoryWhere, $stickyWhere);
		$limit = $sticky ? '0,10' : $this->Paginator->setPageLimit((int)$this->Core->settings['c_max_posts'], false, (int)$total);

		$sql = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_hits, p.post_portada, p.post_date, p.post_comments, p.post_puntos, p.post_private, p.post_sponsored, p.post_status, p.post_sticky, u.user_id, u.user_name, u.user_activo, u.user_baneado, c.c_nombre, c.c_seo, c.c_img FROM p_posts p LEFT JOIN u_miembros u ON p.post_user = u.user_id LEFT JOIN p_categorias c ON c.cid = p.post_category WHERE 1 = 1 {$visibilityWhere} {$categoryWhere} {$stickyWhere} ORDER BY {$orderBy} DESC LIMIT {$limit}";

		$query = result_array(db_exec([__FILE__, __LINE__], 'query', $sql));
		foreach($query as $pid => $post) {
			$query[$pid]['c_img'] = $this->Core->route('assets:images') . '/icons/categories/' . $post['c_img'];
		}
		$pages = $sticky ? null : $this->Paginator->getPages((int)$total, (int)$this->Core->settings['c_max_posts']);
		return [
			'data'  => $query,
			'pages' => $pages
		];
	}

	public function getLastPosts(string $category = ''): array {
		return $this->getPosts(false, $category);
	}

	public function getLastStickys(string $category = ''): array {
		return $this->getPosts(true, $category);
	}
}
