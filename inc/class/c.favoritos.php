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
		/*
		saveFavorito()
	*/
	function saveFavorito(){
		global $tsCore, $tsUser, $tsMonitor, $tsActividad;
		# ANTIFLOOD
		//
		$post_id = $tsCore->setSecure($_POST['postid']);
		$fecha = (int) empty($_POST['reactivar']) ? time() : $tsCore->setSecure($_POST['reactivar']);
		/* DE QUIEN ES EL POST */
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_user FROM p_posts WHERE post_id = \''.(int)$post_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		/*        ------       */
		if($data['post_user'] != $tsUser->uid){
			// YA LO TENGO?
			$my_favorito = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT fav_id FROM p_favoritos WHERE fav_post_id = \''.(int)$post_id.'\' AND fav_user = \''.$tsUser->uid.'\' LIMIT 1'));
			if(empty($my_favorito)){
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO p_favoritos (fav_user, fav_post_id, fav_date) VALUES (\''.$tsUser->uid.'\', \''.(int)$post_id.'\', \''.$fecha.'\')')) {
					// AGREGAR AL MONITOR
					$tsMonitor->setNotificacion(1, $data['post_user'], $tsUser->uid, $post_id);
					// ACTIVIDAD 
					$tsActividad->setActividad(2, $post_id);
					//
					return '1: Bien! Este post fue agregado a tus favoritos.';
				}
				else return '0: '.show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db');
			} else return '0: Este post ya lo tienes en tus favoritos.';
		} else return '0: No puedes agregar tus propios post a favoritos.';
	}
	/*
		getFavoritos()
	*/
	function getFavoritos(){
		global $tsCore, $tsUser;
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT f.fav_id, f.fav_date, p.post_id, p.post_title, p.post_date, p.post_puntos, COUNT(p_c.c_post_id) as post_comments,  c.c_nombre, c.c_seo, c.c_img FROM p_favoritos AS f LEFT JOIN p_posts AS p ON p.post_id = f.fav_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN p_comentarios AS p_c ON p.post_id = p_c.c_post_id && p_c.c_status = \'0\' WHERE f.fav_user = \''.$tsUser->uid.'\' AND p.post_status = \'0\' GROUP BY c_post_id');
		$data = result_array($query);
		
		//
		foreach($data as $fav){
			$favoritos .= '{"fav_id":'.$fav['fav_id'].',"post_id":'.$fav['post_id'].',"titulo":"'.$fav['post_title'].'","categoria":"'.$fav['c_seo'].'","categoria_name":"'.$fav['c_nombre'].'","imagen":"'.$fav['c_img'].'","url":"'.$tsCore->settings['url'].'/posts/'.$fav['c_seo'].'/'.$fav['post_id'].'/'.$tsCore->setSEO($fav['post_title']).'.html","fecha_creado":'.$fav['post_date'].',"fecha_creado_formato":"'.strftime("%d\/%m\/%Y a las %H:%M:%S hs",$fav['post_date']).'.","fecha_creado_palabras":"'.$fav['post_date'].'","fecha_guardado":'.$fav['fav_date'].',"fecha_guardado_formato":"'.strftime("%d\/%m\/%Y a las %H:%M:%S hs",$fav['fav_date']).'.","fecha_guardado_palabras":"'.$tsCore->setHace($fav['fav_date'],true).'","puntos":'.$fav['post_puntos'].',"comentarios":'.$fav['post_comments'].'},';
		}
		//
		return $favoritos;
	}
	/*
		delFavorito()
	*/
	function delFavorito(){
		global $tsCore, $tsUser;
		//
		$fav_id = $tsCore->setSecure($_POST['fav_id']);
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT fav_post_id FROM p_favoritos WHERE fav_id = \''.(int)$fav_id.'\' AND fav_user = \''.$tsUser->uid.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		$is_myfav = db_exec('num_rows', $query);
		
		// ES MI FAVORITO?
		if(!empty($data['fav_post_id'])){
			if(db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM p_favoritos WHERE fav_id = \''.(int)$fav_id.'\' AND fav_user = \''.$tsUser->uid.'\'')){
				return '1: Favorito borrado.';
			} else return '0: No se pudo borrar.';
		} else return '0: No se pudo borrar, no es tu favorito.';
	}
}