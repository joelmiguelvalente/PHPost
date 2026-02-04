<?php

/**
 * @name c.buscador.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsBuscador {
	
	protected tsCore $Core;
	protected tsUser $User;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
	}

	/*
		getQuery()
	*/
	function getQuery() {
		global $tsCore, $tsUser;
		//
		$q = $tsCore->setSecure($_GET['q']);
		$c = intval($_GET['cat']);
		$a = $tsCore->setSecure($_GET['autor']);
		$e = $_GET['e'];
		// ESTABLECER FILTROS
		if($c > 0) $where_cat = 'AND p.post_category = \''.(int)$c.'\'';
		if($e == 'tags') $search_on = 'p.post_tags';
		else $search_on = 'p.post_title';
		// BUSQUEDA
		$w_search = 'AND MATCH('.$search_on.') AGAINST(\''.$q.'\' IN BOOLEAN MODE)';
		// SELECCIONAR USUARIO
		if(!empty($a)){
				// OBTENEMOS ID
				$aid = $tsUser->getUserID($a);
				// BUSCAR LOS POST DEL USUARIO SIN CRITERIO DE BUSQUEDA
				if(empty($q) && $aid > 0) $w_search = 'AND p.post_user = \''.(int)$aid.'\'';
				// BUSCAMOS CON CRITERIO PERO SOLO LOS DE UN USUARIO
				elseif($aid >= 1) $w_autor = 'AND p.post_user = \''.(int)$aid.'\'';
				//
		}
		// PAGINAS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(p.post_id) AS total FROM p_posts AS p WHERE p.post_status = \'0\' '.$where_cat.' '.$w_autor.' '.$w_search.' ORDER BY p.post_date');
		$total = db_exec('fetch_assoc', $query);
		$total = $total['total'];
		
		$data['pages'] = $tsCore->getPagination($total, 12);
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_comments, p.post_favoritos, p.post_puntos, u.user_name, c.c_seo, c.c_nombre, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' '.$where_cat.' '.$w_autor.' '.$w_search.' ORDER BY p.post_date DESC LIMIT '.$data['pages']['limit']);
		$data['data'] = result_array($query);
		
		// ACTUALES
		$total = explode(',',$data['pages']['limit']);
		$data['total'] = ($total[0]) + count($data['data']);
		//
		return $data;
		}
	
}