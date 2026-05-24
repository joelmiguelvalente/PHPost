<?php

declare(strict_types=1);

/**
 * @package    PHPost/Class
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 * @note Desarrollado con asistencia de Claude (Anthropic)
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsBuscador {

	private const POSTS_PER_PAGE  = 12;

	private const USERS_PER_PAGE  = 20;

	private const FOTOS_PER_PAGE  = 16;

	private const MURO_PER_PAGE   = 15;

	private const VALID_ENGINES   = ['web', 'tags', 'usuarios', 'fotos', 'muro'];

	private const SELECT_FIELDS   = 'p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_comments, p.post_favoritos, p.post_puntos, u.user_name, c.c_seo, c.c_nombre, c.c_img';

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User
	) {}

	// ─── Parámetros comunes ────────────────────────────────────────────────────

	private function getParamsSearching(): array {
		$engine = trim($_GET['engine'] ?? 'web');

		return [
			'query'    => trim($_GET['query']    ?? ''),
			'category' => max(0, (int)($_GET['category'] ?? 0)),
			'autor'    => trim($_GET['autor']    ?? ''),
			'engine'   => in_array($engine, self::VALID_ENGINES, true) ? $engine : 'web',
		];
	}

	public function getTypeFilter(): array {
		$params = $this->getParamsSearching();

		return $params + [
			'on' => $params['engine'] === 'tags' ? 'p.post_tags' : 'p.post_title',
		];
	}

	// ─── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Resuelve IDs de usuarios por coincidencia parcial de nombre.
	 * Retorna array de ints. El wildcard % se arma en PHP, no en SQL.
	 */
	private function resolveAuthorIds(string $autor): array {
		$rows = DB::fetchAll(
			"SELECT user_id FROM u_miembros
			 WHERE user_name LIKE :autor
			 AND user_activo = 1 AND user_baneado = 0
			 LIMIT 50",
			['autor' => '%' . $autor . '%']
		);
		return array_map('intval', array_column($rows, 'user_id'));
	}

	/**
	 * Arma el fragmento "1,2,3" para usar en IN (...).
	 * Seguro porque los IDs son ints casteados.
	 * Retorna null si el array está vacío.
	 */
	private function buildInClause(array $ids): ?string {
		return empty($ids) ? null : implode(',', $ids);
	}

	// ─── Posts (web / tags) ────────────────────────────────────────────────────

	public function getQuery(): array {
		$filter = $this->getTypeFilter();
		$query  = $filter['query'];
		$params = ['status' => 0];

		$conditions = ['p.post_status = :status'];

		// Filtro por categoría
		if ($filter['category'] > 0) {
			$conditions[]       = 'p.post_category = :category';
			$params['category'] = $filter['category'];
		}

		// Filtro por autor (parcial, múltiples IDs — son ints, interpolación segura)
		if (!empty($filter['autor'])) {
			$ids = $this->resolveAuthorIds($filter['autor']);
			if (!empty($ids)) {
				$conditions[] = 'p.post_user IN (' . $this->buildInClause($ids) . ')';
			}
		}

		// FULLTEXT: AGAINST no admite placeholders en MySQLi,
		// se usa escape() exclusivamente para el valor dentro de AGAINST.
		if (!empty($query)) {
			$safeQuery    = DB::escape($query);
			$conditions[] = "MATCH({$filter['on']}) AGAINST('{$safeQuery}' IN BOOLEAN MODE)";
		}

		$where   = 'WHERE ' . implode(' AND ', $conditions);
		$orderBy = 'ORDER BY p.post_date DESC';

		$total = (int) DB::value(
			"SELECT COUNT(p.post_id) FROM p_posts AS p {$where}",
			$params
		);
		$pages = (new Paginator)->getPagination($total, self::POSTS_PER_PAGE);

		[$offset, $perPage] = array_map('intval', explode(',', $pages['limit']));
		$params['offset']  = $offset;
		$params['perpage'] = $perPage;

		$rows = DB::fetchAll("SELECT " . self::SELECT_FIELDS . " FROM p_posts AS p LEFT JOIN u_miembros   AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid     = p.post_category {$where} {$orderBy} LIMIT :offset, :perpage", $params);

		return [
			'pages' => $pages,
			'data'  => $rows,
			'total' => $offset + count($rows),
		];
	}

	// ─── Usuarios ──────────────────────────────────────────────────────────────

	public function getUsuarios(): array {
		$p    = $this->getParamsSearching();
		$term = !empty($p['autor']) ? $p['autor'] : $p['query'];

		if (empty($term)) {
			return ['pages' => [], 'data' => [], 'total' => 0];
		}

		$params = ['term' => '%' . $term . '%', 'activo' => 1, 'baneado' => 0];

		$total = (int) DB::value("SELECT COUNT(user_id) FROM u_miembros WHERE user_activo = :activo AND user_baneado = :baneado AND user_name LIKE :term", $params);
		$pages = (new Paginator)->getPagination($total, self::USERS_PER_PAGE);

		[$offset, $perPage] = array_map('intval', explode(',', $pages['limit']));
		$params['offset']  = $offset;
		$params['perpage'] = $perPage;

		$rows = DB::fetchAll("SELECT u.user_id, u.user_name, u.user_puntos, u.user_posts, u.user_comentarios, u.user_seguidores, u.user_registro, p.p_avatar, p.p_nombre, p.user_pais FROM u_miembros AS u LEFT JOIN u_perfil AS p ON p.user_id = u.user_id WHERE u.user_activo = :activo AND u.user_baneado = :baneado AND u.user_name LIKE :term ORDER BY u.user_puntos DESC LIMIT :offset, :perpage", $params);

		return [
			'pages' => $pages,
			'data'  => $rows,
			'total' => $offset + count($rows),
		];
	}

	// ─── Fotos ─────────────────────────────────────────────────────────────────

	public function getFotos(): array {
		$p      = $this->getParamsSearching();
		$query  = $p['query'];
		$params = ['status' => 0, 'closed' => 0];

		$conditions = ['f.f_status = :status', 'f.f_closed = :closed'];

		if (!empty($p['autor'])) {
			$ids = $this->resolveAuthorIds($p['autor']);
			if (!empty($ids)) {
				$conditions[] = 'f.f_user IN (' . $this->buildInClause($ids) . ')';
			}
		}

		if (!empty($query)) {
			$params['query_like'] = '%' . $query . '%';
			$conditions[]         = '(f.f_title LIKE :query_like OR f.f_description LIKE :query_like)';
		}

		$where = 'WHERE ' . implode(' AND ', $conditions);

		$total = (int) DB::value("SELECT COUNT(f.foto_id) FROM f_fotos AS f {$where}", $params);
		$pages = (new Paginator)->getPagination($total, self::FOTOS_PER_PAGE);

		[$offset, $perPage] = array_map('intval', explode(',', $pages['limit']));
		$params['offset']  = $offset;
		$params['perpage'] = $perPage;

		$rows = DB::fetchAll("SELECT f.foto_id, f.f_title, f.f_description, f.f_url, f.f_date, f.f_visitas, f.f_hits, f.f_user, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user {$where} ORDER BY f.f_date DESC LIMIT :offset, :perpage", $params);

		return [
			'pages' => $pages,
			'data'  => $rows,
			'total' => $offset + count($rows),
		];
	}

	// ─── Muro ──────────────────────────────────────────────────────────────────

	public function getMuro(): array {
		$p      = $this->getParamsSearching();
		$query  = $p['query'];
		$params = ['visibility' => 'everyone'];

		$conditions = ['m.p_visibility = :visibility', 'm.p_body IS NOT NULL'];

		if (!empty($p['autor'])) {
			$ids = $this->resolveAuthorIds($p['autor']);
			if (!empty($ids)) {
				$conditions[] = 'm.p_user IN (' . $this->buildInClause($ids) . ')';
			}
		}

		if (!empty($query)) {
			$params['query_like'] = '%' . $query . '%';
			$conditions[]         = 'm.p_body LIKE :query_like';
		}

		$where = 'WHERE ' . implode(' AND ', $conditions);

		$total = (int) DB::value("SELECT COUNT(m.pub_id) FROM u_muro AS m {$where}", $params);
		$pages = (new Paginator)->getPagination($total, self::MURO_PER_PAGE);

		[$offset, $perPage] = array_map('intval', explode(',', $pages['limit']));
		$params['offset']  = $offset;
		$params['perpage'] = $perPage;

		$rows = DB::fetchAll("SELECT m.pub_id, m.p_body, m.p_date, m.p_likes, m.p_comments, m.p_user, m.p_user_pub, u.user_name, pu.user_name AS pub_en_user FROM u_muro AS m LEFT JOIN u_miembros AS u  ON u.user_id  = m.p_user LEFT JOIN u_miembros AS pu ON pu.user_id = m.p_user_pub {$where} ORDER BY m.p_date DESC LIMIT :offset, :perpage", $params);

		return [
			'pages' => $pages,
			'data'  => $rows,
			'total' => $offset + count($rows),
		];
	}

	// ─── Tags (devuelve los posts que tienen el tag buscado) ──────────────────

	public function getTags(): array {
		$p     = $this->getParamsSearching();
		$query = $p['query'];

		if (empty($query)) {
			return ['pages' => [], 'data' => [], 'total' => 0];
		}

		$params = [
			'status'     => 0,
			'query_like' => '%' . $query . '%',
		];

		$total = (int) DB::value("SELECT COUNT(p.post_id) FROM p_posts AS p WHERE p.post_status = :status AND p.post_tags LIKE :query_like AND p.post_tags != ''", $params);
		$pages = (new Paginator)->getPagination($total, self::POSTS_PER_PAGE);

		[$offset, $perPage] = array_map('intval', explode(',', $pages['limit']));
		$params['offset']  = $offset;
		$params['perpage'] = $perPage;

		$rows = DB::fetchAll("SELECT " . self::SELECT_FIELDS . " FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND p.post_tags LIKE :query_like AND p.post_tags != '' ORDER BY p.post_date DESC LIMIT :offset, :perpage", $params);

		return [
			'pages' => $pages,
			'data'  => $rows,
			'total' => $offset + count($rows),
		];
	}

	// ─── Conteos para badges de pestañas ──────────────────────────────────────

	public function getCounts(): array {
		$p     = $this->getParamsSearching();
		$query = $p['query'];
		$autor = $p['autor'];

		if (empty($query) && empty($autor)) {
			return ['posts' => 0, 'fotos' => 0, 'usuarios' => 0, 'muro' => 0, 'tags' => 0];
		}

		// IDs de autor resueltos una sola vez para reutilizar
		$authorIds = !empty($autor) ? $this->resolveAuthorIds($autor) : [];
		$authorIn  = $this->buildInClause($authorIds);

		// ── Posts ──
		$pPost  = ['status' => 0];
		$wPost  = ['post_status = :status'];
		if (!empty($query)) {
			// FULLTEXT no admite placeholder, usamos escape()
			$safeQuery = DB::escape($query);
			$wPost[]   = "MATCH(post_title) AGAINST('{$safeQuery}' IN BOOLEAN MODE)";
		}
		if ($authorIn) $wPost[] = "post_user IN ({$authorIn})";
		$countPosts = (int) DB::value("SELECT COUNT(post_id) FROM p_posts WHERE " . implode(' AND ', $wPost), $pPost);

		// ── Fotos ──
		$pFoto = ['status' => 0, 'closed' => 0];
		$wFoto = ['f_status = :status', 'f_closed = :closed'];
		if (!empty($query)) {
			$pFoto['q'] = '%' . $query . '%';
			$wFoto[]    = '(f_title LIKE :q OR f_description LIKE :q)';
		}
		if ($authorIn) $wFoto[] = "f_user IN ({$authorIn})";
		$countFotos = (int) DB::value("SELECT COUNT(foto_id) FROM f_fotos WHERE " . implode(' AND ', $wFoto), $pFoto);

		// ── Usuarios ──
		$term = !empty($autor) ? $autor : $query;
		$countUsuarios = empty($term) ? 0 : (int) DB::value("SELECT COUNT(user_id) FROM u_miembros WHERE user_activo = :activo AND user_baneado = :baneado AND user_name LIKE :term", ['activo' => 1, 'baneado' => 0, 'term' => '%' . $term . '%']);

		// ── Muro ──
		$pMuro = ['visibility' => 'everyone'];
		$wMuro = ['p_visibility = :visibility', 'p_body IS NOT NULL'];
		if (!empty($query)) {
			$pMuro['q'] = '%' . $query . '%';
			$wMuro[]    = 'p_body LIKE :q';
		}
		if ($authorIn) $wMuro[] = "p_user IN ({$authorIn})";
		$countMuro = (int) DB::value("SELECT COUNT(pub_id) FROM u_muro WHERE " . implode(' AND ', $wMuro), $pMuro);

		// ── Tags ──
		$countTags = empty($query) ? 0 : (int) DB::value("SELECT COUNT(post_id) FROM p_posts WHERE post_status = :status AND post_tags LIKE :q AND post_tags != ''", ['status' => 0, 'q' => '%' . $query . '%']);

		return [
			'posts'    => $countPosts,
			'fotos'    => $countFotos,
			'usuarios' => $countUsuarios,
			'muro'     => $countMuro,
			'tags'     => $countTags,
		];
	}
}
