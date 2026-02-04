<?php

/**
 * @name c.tops.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsTops {

	protected tsCore $Core;

	private int $cacheTTL;

	private array $filter = ['hoy' => 1, 'ayer' => 2, 'semana' => 3, 'mes' => 4, 'historico' => 5];

	public function __construct(tsCore $Core) {
		$this->Core = $Core;
		$this->cacheTTL = (int)$this->Core->settings['c_stats_cache'] * 60;
	}

	private function betweenDate(int $date = 0): string {
		$data = $this->setTime((int)$date);
		return "BETWEEN {$data['start']} AND {$data['end']}";
	}
	
	/*
		setTopPostsVars($text, $type)
	*/
	private function getTopPostsVars(int $fecha, int $cat, string $type): array {
		$data = $this->setTime($fecha);
		$cat = ($cat !== 0) ? "" : " AND c.cid = $cat";
		$type = "p.post_{$type}";
		$data = "BETWEEN {$data['start']} AND {$data['end']}{$cat} ORDER BY {$type}";
		return $this->getTopPostsQuery((string)$type, (string)$data);
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

	/**
	 * @access public Es llamado por getHomeTops()
	*/
	public function getHomeTopPostsQuery(string $between) {
		return result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT p.post_id, p.post_category, p.post_title, p.post_puntos, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date {$between} ORDER BY p.post_puntos DESC LIMIT 15"));
	}

	/**
	 * @access public Es llamado por getHomeTops()
	*/
	function getHomeTopUsersQuery(string $between) {
		return result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date {$between} GROUP BY p.post_user ORDER BY total DESC LIMIT 10"));
	}

	/**
	 * -----------------------------------------
	 * SECCION TOPS
	 * -----------------------------------------
	 */

	/*
		getTopPosts()
	*/
	public function getTopPosts(int $fecha, int $cat): array {
		$data = [];
		foreach(['puntos', 'seguidores', 'comments', 'favoritos'] as $type) {
			$data[$type] = $this->getTopPostsVars($fecha, $cat, $type);
		}
		return $data;
	}

	/*
		getTopUsers()
	*/
	public function getTopUsers(int $fecha = 0, int $cat = 0): array {
		$between = $this->betweenDate((int)$fecha);
		$categorie = ($cat !== 0) ? "" : " AND post_category = $cat";
		$limit = 10;
		// PUNTOS
		$array['puntos'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date {$between}{$categorie} GROUP BY p.post_user ORDER BY total DESC LIMIT $limit"));
		// SEGUIDORES
		$array['seguidores'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(f.follow_id) AS total, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON f.f_id = u.user_id WHERE f.f_type = 1 AND f.f_date {$between} GROUP BY f.f_id ORDER BY total DESC LIMIT $limit"));
		// MEDALLAS
		$array['medallas'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(*) AS total, u.user_id, u.user_name FROM w_medallas_assign AS m LEFT JOIN u_miembros AS u ON m.medal_for = u.user_id LEFT JOIN w_medallas AS wm ON wm.medal_id = m.medal_id WHERE wm.m_type = 1 {$between} GROUP BY u.user_id, u.user_name ORDER BY total DESC LIMIT $limit"));
		//
		return $array;
	}

	/*
		getTopPostsQuery($data)
	*/
	public function getTopPostsQuery(string $type, string $data): array {
		return result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT p.post_id, p.post_category, {$type}, p.post_puntos, p.post_title, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date {$data} DESC LIMIT 10"));
	}

	private function checkTimeStats(): bool|int {
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT stats_time_cache FROM w_stats WHERE stats_no = 1"));
		$statsTime = (int)$data['stats_time_cache'];
		return ($statsTime + $this->cacheTTL) < time();
	}

	private function getStatsWithoutTable(&$return) {
		$sentencias = [
			'miembros' => "SELECT COUNT(user_id) AS total FROM u_miembros WHERE user_activo = 1 AND user_baneado = 0",
			'posts' => "SELECT COUNT(post_id) AS total FROM p_posts WHERE post_status = 0",
			'fotos' => "SELECT COUNT(foto_id) as total FROM f_fotos WHERE f_status = 0",
			'comments' => "SELECT COUNT(cid) as total FROM p_comentarios WHERE c_status = 0",
			'foto_comments' => "SELECT COUNT(cid) as total FROM f_comentarios"
		];
		foreach($sentencias as $who => $sql) {
      	$row = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', $sql));
      	$return['stats_' . $who] = (int)($row[0] ?? 0);
		}
		return $return;
	}

	/*
		getStats() : NADA QUE VER CON LA CLASE PERO BUENO PARA AHORRAR ESPACIO...
		: ESTADISTICAS DE LA WEB
	*/
	public function getStats() {
		$time = time();
		// OBTENEMOS LAS ESTADISTICAS
		$return = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT stats_max_online, stats_max_time, stats_time, stats_time_cache, stats_miembros, stats_posts, stats_fotos, stats_comments, stats_foto_comments FROM w_stats WHERE stats_no = 1"));
		
		$return['stats_time'] = time();
		if($this->checkTimeStats()) {
			$return['stats_time_cache'] = time();
			$this->getStatsWithoutTable($return);
		}
		// PARA SABER SI ESTA ONLINE
		$isOnline = (int)(time() - $this->cacheTTL);
		// USUARIOS ONLINE - COMPROBAMOS SI CONTAMOS A TODOS LOS USUARIOS O SOLO A REGISTRADOS
		$guests = (int)($this->Core->settings['c_count_guests'] ?? 0) === 0;
		$sql = $guests ? "SELECT COUNT(user_id) AS total FROM u_miembros WHERE user_lastactive" : "SELECT COUNT(DISTINCT `session_ip`) AS s FROM u_sessions WHERE session_time";
		$total = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "$sql > $isOnline"));
		
		if((int)$total[0] >= (int)$return['stats_max_online']) {
			$return['stats_max_online'] = (int)$total[0];
			$return['stats_max_time'] = $time;
		}
		$set = $this->Core->buildSqlSet($return);
		db_exec([__FILE__, __LINE__], 'query', "UPDATE w_stats SET stats_time = $set WHERE stats_no = 1");
		//
		return $return;
	}
	
	/*
		setTime($fecha)
	*/
	public function setTime(int $fecha = 0): array {
	   $ahora = time();
	   return match ($fecha) {
	      // HOY
	      1 => ['start' => strtotime('today'),'end' => strtotime('tomorrow') - 1],
	      // AYER
	      2 => ['start' => strtotime('yesterday'),'end' => strtotime('today') - 1],
	      // SEMANA
	      3 => ['start' => strtotime('-1 week'),'end' => strtotime('tomorrow') - 1],
	      // MES
	      4 => ['start' => strtotime('first day of this month', $ahora),'end' => strtotime('tomorrow', $ahora) - 1],
	      // TODO EL TIEMPO (default)
	      default => ['start' => 0,'end' => $ahora],
	   };
	}
}