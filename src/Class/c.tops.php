<?php

/**
 * @name src/Class/c.tops.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsTops {

	private int $cacheTTL;

	private array $filter = ['hoy' => 1, 'ayer' => 2, 'semana' => 3, 'mes' => 4, 'historico' => 5];

	public function __construct(protected tsCore $Core) {
		$this->cacheTTL = (int)$this->Core->settings['c_stats_cache'] * 60;
	}

	/**
	 * Devuelve el fragmento SQL del BETWEEN y sus params listos para DB::
	 * Retorna: ['sql' => 'BETWEEN :start AND :end', 'params' => [...]]
	 */
	private function betweenDate(int $date = 0): array {
		$data = $this->setTime($date);
		return [
			'sql' => 'BETWEEN :start AND :end',
			'params' => ['start' => $data['start'], 'end' => $data['end']],
		];
	}

	/**
	 * Construye el fragmento SQL de categoría y su param
	 * Retorna: ['sql' => '', 'params' => []] si no hay categoría
	 *          ['sql' => 'AND c.cid = :cat', 'params' => ['cat' => $cat]] si hay
	 */
	private function categoryClause(int $cat): array {
		if ($cat === 0) {
			return ['sql' => '', 'params' => []];
		}
		return [
			'sql' => ' AND c.cid = :cat',
			'params' => ['cat' => $cat],
		];
	}

	/**
	 * -----------------------------------------
	 * SECCION HOME
	 * -----------------------------------------
	 */

	public function getHomeTops(string $method): array {
		$method = 'getHomeTop' . ucfirst($method) . 'Query';
		$result = [];
		foreach ($this->filter as $key => $rangeId) {
			$between = $this->betweenDate($rangeId);
			$result[$key] = $this->{$method}($between);
		}
		return $result;
	}

	public function getHomeTopPostsQuery(array $between): array {
		return DB::fetchAll("SELECT p.post_id, p.post_category, p.post_title, p.post_puntos, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date {$between['sql']} ORDER BY p.post_puntos DESC LIMIT 15", $between['params']);
	}

	public function getHomeTopUsersQuery(array $between): array {
		return DB::fetchAll("SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date {$between['sql']} GROUP BY p.post_user ORDER BY total DESC LIMIT 10", $between['params']);
	}

	/**
	 * -----------------------------------------
	 * SECCION TOPS
	 * -----------------------------------------
	 */

	public function getTopPosts(int $fecha, int $cat): array {
		$between  = $this->betweenDate($fecha);
		$category = $this->categoryClause($cat);
		$params = array_merge($between['params'], $category['params']);
		$data = [];
		foreach (['puntos', 'seguidores', 'comments', 'favoritos'] as $type) {
			$col = "p.post_{$type}";
			$data[$type] = DB::fetchAll("SELECT p.post_id, p.post_category, {$col}, p.post_puntos, p.post_title, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date {$between['sql']}{$category['sql']} ORDER BY {$col} DESC LIMIT 10", $params);
		}
		return $data;
	}

	public function getTopUsers(int $fecha = 0, int $cat = 0): array {
		$between  = $this->betweenDate($fecha);
		$category = $this->categoryClause($cat);
		$params = array_merge($between['params'], $category['params']);
		$data = [];
		// PUNTOS
		$data['puntos'] = DB::fetchAll("SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date {$between['sql']}{$category['sql']} GROUP BY p.post_user ORDER BY total DESC LIMIT 10", $params);
		// SEGUIDORES — no filtra por categoría
		$data['seguidores'] = DB::fetchAll("SELECT COUNT(f.follow_id) AS total, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON f.f_id = u.user_id WHERE f.f_type = 1 AND f.f_date {$between['sql']} GROUP BY f.f_id ORDER BY total DESC LIMIT 10", $between['params']);
		// MEDALLAS — no filtra por categoría ni fecha de post
		$data['medallas'] = DB::fetchAll("SELECT COUNT(*) AS total, u.user_id, u.user_name FROM w_medallas_assign AS m LEFT JOIN u_miembros AS u ON m.medal_for = u.user_id LEFT JOIN w_medallas AS wm ON wm.medal_id = m.medal_id WHERE wm.m_type = 1 AND m.medal_date {$between['sql']} GROUP BY u.user_id, u.user_name ORDER BY total DESC LIMIT 10", $between['params']);
		return $data;
	}

	/**
	 * -----------------------------------------
	 * ESTADISTICAS (widget home)
	 * -----------------------------------------
	 */

	public function getStats(): array {
		$time   = time();
		$return = DB::fetch("SELECT stats_max_online, stats_max_time, stats_time, stats_time_cache, stats_miembros, stats_posts, stats_fotos, stats_comments, stats_foto_comments FROM w_stats WHERE stats_no = 1") ?? [];
		$return['stats_time'] = $time;
		// Actualizar conteos si el caché expiró
		if ($this->checkTimeStats()) {
			$return['stats_time_cache'] = $time;
			$this->refreshStatsCounts($return);
		}
		// Usuarios online
		$isOnline = $time - $this->cacheTTL;
		$guests = (int)($this->Core->settings['c_count_guests'] ?? 0) === 0;
		if ($guests) {
			$onlineCount = (int)DB::value("SELECT COUNT(user_id) FROM u_miembros WHERE user_lastactive > :t", ['t' => $isOnline]);
		} else {
			$onlineCount = (int)DB::value("SELECT COUNT(DISTINCT session_ip) FROM u_sessions WHERE session_time > :t", ['t' => $isOnline]);
		}
		//
		if ($onlineCount >= (int)$return['stats_max_online']) {
			$return['stats_max_online'] = $onlineCount;
			$return['stats_max_time']   = $time;
		}

		$return['stats_online'] = $onlineCount;

		DB::update('w_stats', [
			'stats_time'       => $return['stats_time'],
			'stats_max_online' => $return['stats_max_online'],
			'stats_max_time'   => $return['stats_max_time'],
		], 'stats_no = :no', ['no' => 1]);

		return $return;
	}

	/**
	 * -----------------------------------------
	 * PRIVADOS
	 * -----------------------------------------
	 */

	private function checkTimeStats(): bool {
		$cached = (int)DB::value("SELECT stats_time_cache FROM w_stats WHERE stats_no = 1");
		return ($cached + $this->cacheTTL) < time();
	}

	private function refreshStatsCounts(array &$return): void {
		$queries = [
			'stats_miembros'      => "SELECT COUNT(user_id) FROM u_miembros WHERE user_activo = 1 AND user_baneado = 0",
			'stats_posts'         => "SELECT COUNT(post_id) FROM p_posts WHERE post_status = 0",
			'stats_fotos'         => "SELECT COUNT(foto_id) FROM f_fotos WHERE f_status = 0",
			'stats_comments'      => "SELECT COUNT(cid) FROM p_comentarios WHERE c_status = 0",
			'stats_foto_comments' => "SELECT COUNT(cid) FROM f_comentarios",
		];

		foreach ($queries as $key => $sql) {
			$return[$key] = (int)DB::value($sql);
		}

		DB::update('w_stats', array_intersect_key($return, array_flip(array_keys($queries))), 'stats_no = :no', ['no' => 1]);
	}

	public function setTime(int $fecha = 0): array {
		$ahora = time();
		return match ($fecha) {
			1 => [
				'start' => strtotime('today'),
				'end' => strtotime('tomorrow') - 1
			],
			2 => [
				'start' => strtotime('yesterday'),
				'end' => strtotime('today') - 1
			],
			3 => [
				'start' => strtotime('-1 week'),
				'end' => strtotime('tomorrow') - 1
			],
			4 => [
				'start' => strtotime('first day of this month', $ahora), 
				'end' => strtotime('tomorrow', $ahora) - 1
			],
			default => ['start' => 0, 'end' => $ahora],
		};
	}

}
