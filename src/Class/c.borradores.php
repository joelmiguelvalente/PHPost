<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsBorradores {

	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*\
								BORRADORES
	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	/*
		newDraft()
	*/
	function newDraft(bool $save = false): string{
		global $tsCore, $tsUser;
		//
		$draftData = array(
			'date' => time(),
			'title' => Html::escape($tsCore->parseBadWords($_POST['titulo'])),
			'body' => Html::escape($_POST['cuerpo'],),
			'tags' => Html::escape($tsCore->parseBadWords($_POST['tags'])),
			'category' => Html::escape($_POST['categoria']),
			'private' => empty($_POST['privado']) ? 0 : 1,
			'block_comments' => empty($_POST['sin_comentarios']) ? 0 : 1,
			'sponsored' => empty($_POST['patrocinado']) ? 0 : 1,
            'sticky' => empty($_POST['sticky']) ? 0 : 1,
			'smileys' => empty($_POST['smileys']) ? 0 : 1,
			'visitantes' => empty($_POST['visitantes']) ? 0 : 1,
		);
		//
		if(!empty($draftData['title'])) {
			if(!empty($draftData['category']) && $draftData['category'] > 0) {
			if($save) {
				// UPDATE
				$bid = intval($_POST['borrador_id']);
				$set = [];
				$params = ['bid' => $bid, 'b_user' => $tsUser->info['user_id']];
				foreach ($draftData as $key => $value) {
					$set[] = "b_{$key} = :b_{$key}";
					$params["b_{$key}"] = $value;
				}
				$sql = 'UPDATE p_borradores SET ' . implode(', ', $set) . ' WHERE bid = :bid AND b_user = :b_user';
                if(DB::query($sql, $params)) return '1: '.$bid;
				else return '0: '.show_error('Error al ejecutar la consulta de la línea '.__LINE__.' de '.__FILE__.'.', 'db');
		   } else {
				// INSERT
			    DB::insert('p_borradores', [
			    	'b_user' => $tsUser->info['user_id'],
			    	'b_date' => $draftData['date'],
			    	'b_title' => $draftData['title'],
			    	'b_body' => $draftData['body'],
			    	'b_tags' => $draftData['tags'],
			    	'b_category' => $draftData['category'],
			    	'b_private' => $draftData['private'],
			    	'b_block_comments' => $draftData['block_comments'],
			    	'b_sponsored' => $draftData['sponsored'],
			    	'b_sticky' => $draftData['sticky'],
			    	'b_smileys' => $draftData['smileys'],
			    	'b_visitantes' => $draftData['visitantes'],
			    	'b_status' => '1',
			    	'b_causa' => '',
			    ]);
			    return '1: '.DB::insertId();
			   else return '0: '.show_error('Error al ejecutar la consulta de la línea '.__LINE__.' de '.__FILE__.'.', 'db');
			}
			} else $return = 'Categoría';
		} else $return = 'Títulos';
		//
		return '0: El campo <b>'.$return.'</b> es requerido para esta operación';
		//
	}
	/*
		getDrafts()
	*/
	function getDrafts(): string{
		global $tsCore, $tsUser;
		//
		$query = DB::fetchAll('SELECT c.c_nombre, c.c_seo, c.c_img, b.bid, b.b_title, b.b_date, b.b_status, b.b_causa FROM p_categorias AS c LEFT JOIN p_borradores AS b ON c.cid = b.b_category WHERE b.b_user = :uid ORDER BY b.b_date', ['uid' => $tsUser->info['user_id']]);
		//
		$drafts = $query;
		// SET
		$tipos = array('eliminados','borradores');
		foreach($drafts as $draft){
            $causa = empty($draft['b_causa']) ? 'Eliminado por el autor' : htmlspecialchars($draft['b_causa']);
			$dft .= '{"id":'.$draft['bid'].',"titulo":"'.$draft['b_title'].'","categoria":"'.$draft['c_seo'].'","imagen":"'.$draft['c_img'].'","fecha_guardado":'.$draft['b_date'].',"status":'.$draft['b_status'].',"causa":"'.$causa.'","categoria_name":"'.$draft['c_nombre'].'","tipo":"'.$tipos[$draft['b_status']].'","url":"'.$tsCore->settings['url'].'/agregar/'.$draft['bid'].'","fecha_print":"'.strftime("%d\/%m\/%Y a las %H:%M:%S hs",$draft['b_date']).'"},';
		}
		return $dft;
	}
	/*
		getDraft()
	*/
	function getDraft(int $status = 1): ?array{
		global $tsCore, $tsUser;
		//
		$bid = intval($_GET['action']);
		return DB::fetch('SELECT bid, b_user, b_date, b_title, b_body, b_tags, b_category, b_private, b_block_comments, b_sponsored, b_sticky, b_smileys, b_post_id, b_status, b_causa FROM p_borradores WHERE bid = :bid AND b_user = :uid AND b_status = :status LIMIT 1', ['bid' => (int)$bid, 'uid' => $tsUser->info['user_id'], 'status' => $status]);
	}
	/*
		delDraft()
	*/
	function delDraft(): string{
		global $tsCore, $tsUser;
		//
		$bid = intval($_POST['borrador_id']);
        if(DB::delete('p_borradores', 'bid = :bid AND b_user = :uid', ['bid' => (int)$bid, 'uid' => $tsUser->info['user_id']])) return '1: Borrador eliminado';
		else return '0: Ocurrió un error';
	}

	
	
}
