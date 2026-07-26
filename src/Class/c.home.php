<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsHome {

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User,
		protected Paginator $Paginator,
		protected Extras $Extras
	) {
	}
	
	/**
	 * @access public
	 * @return array
	 */
	public function getDataCategorie(): array {
		$seo = Html::escape($_GET['cat']);
		return DB::fetch("SELECT c_nombre, c_seo FROM p_categorias WHERE c_seo = :seo LIMIT 1", ['seo' => $seo]);
	}

	private function getCategoryId(string $category): ?int {
		if ($category === '') {
			return null;
		}
		$categorySeo = Html::escape($category);
		$row = DB::fetch("SELECT cid FROM p_categorias WHERE c_seo = :seo LIMIT 1", ['seo' => $categorySeo]);
		return !empty($row['cid']) ? (int) $row['cid'] : null;
	}

	private function canSeeHiddenPosts(): bool {
		return ($this->User->is_admod === 1 && (int)$this->Core->settings['c_see_mod'] === 1);
	}

	private function countPosts(string $visibilityWhere, string $categoryWhere, string $stickyWhere): int {
		$sql = "SELECT COUNT(*) FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE {$stickyWhere} {$visibilityWhere} {$categoryWhere}";
		$row = DB::value($sql);
		return (int)($row ?? 0);
	}

	private function getPosts(bool $sticky, string $category = ''): array {
		// Categoria
		$categoryId = $this->getCategoryId($category);
		$categoryWhere = $categoryId !== null ? "AND p.post_category = {$categoryId}" : '';
		// Post Fijado si/no
		$stickyWhere = $sticky ? 'p.post_sticky = 1' : 'p.post_sticky = 0';
		// Tipo de usuario admin/comun
		$visibilityWhere = $this->canSeeHiddenPosts() ? '' : "AND u.user_activo = 1 AND u.user_baneado = 0 AND p.post_status = 'publicado'";

		$orderBy = $sticky ? 'p.post_sponsored' : 'p.post_id';
		// Paginacion
		$total = $this->countPosts($visibilityWhere, $categoryWhere, $stickyWhere);
		$limit = $sticky ? '0,10' : $this->Paginator->setPageLimit((int)$this->Core->settings['c_max_posts'], false, (int)$total);

		$sql = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_hits, p.post_portada, p.post_date, p.post_comments, p.post_puntos, p.post_private, p.post_sponsored, p.post_status, p.post_sticky, u.user_id, u.user_name, u.user_activo, u.user_baneado, c.c_nombre, c.c_seo, c.c_img FROM p_posts p LEFT JOIN u_miembros u ON p.post_user = u.user_id LEFT JOIN p_categorias c ON c.cid = p.post_category WHERE {$stickyWhere} {$visibilityWhere} {$categoryWhere} ORDER BY {$orderBy} DESC LIMIT {$limit}";

		$query = DB::fetchAll($sql);
		foreach($query as $pid => $post) {
			$query[$pid]['c_img'] = Container::get(Routes::class)->absoluteUrl('assets:images', '/icons/categories/' . $post['c_img']);
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
