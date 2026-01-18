<?php

/**
 * @name c.comentarios.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsComentarios {
	
	protected tsCore $Core;
	protected tsUser $User;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
	}

	/*
		getLastComentarios()
		: PARA EL PORTAL
	*/
	function getLastComentarios() {
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT cm.cid, cm.c_status, u.user_name, u.user_activo, u.user_baneado, p.post_id, p.post_title, p.post_status, c.c_seo FROM p_comentarios AS cm LEFT JOIN u_miembros AS u ON cm.c_user = u.user_id LEFT JOIN p_posts AS p ON p.post_id = cm.c_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category '.($this->User->is_admod && $this->Core->settings['c_see_mod'] == 1 ? '' : 'WHERE p.post_status = \'0\'  AND cm.c_status = \'0\' AND u.user_activo = \'1\' && u.user_baneado = \'0\'').' ORDER BY cid DESC LIMIT 10');
		if(!$query) exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
		$data = result_array($query);
		
		//
		return $data;
	}
	/*
		getComentarios()
	*/
	function getComentarios($post_id){
		//
		$start = $this->Core->setPageLimit($this->Core->settings['c_max_com']);
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_name, u.user_activo, u.user_baneado, c.* FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = \''.(int)$post_id.'\' '.($this->User->is_admod ? '' : 'AND c.c_status = \'0\' AND u.user_activo = \'1\' && u.user_baneado = \'0\'').' ORDER BY c.cid LIMIT '.$start);
		// COMENTARIOS TOTALES
		$return['num'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT cid FROM p_comentarios WHERE c_post_id = \''.(int)$post_id.'\' '.($this->User->is_admod ? '' : 'AND c_status = \'0\'').''));
		//
		$comments = result_array($query);
		
					
		// PARSEAR EL BBCODE
		$i = 0;
		foreach($comments as $comment){
			// CON ESTE IF NOS AHORRAMOS CONSULTAS :)
			if($comment['c_votos'] != 0){
				$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT voto_id FROM p_votos WHERE tid = \''.(int)$comment['cid'].'\' AND tuser = \''.$this->User->uid.'\' AND type = \'2\' LIMIT 1');
				$votado = db_exec('num_rows', $query);
				
			} else $votado = 0;
			
			// BLOQUEADO
			$return['block'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT bid, b_user, b_auser FROM `u_bloqueos` WHERE b_user = \''.(int)$comment['c_user'].'\' AND b_auser = \''.$this->User->uid.'\' LIMIT 1'));

			//
			$return['data'][$i] = $comment;
			$return['data'][$i]['votado'] = $votado;
			$return['data'][$i]['c_html'] = $this->Core->parseBadWords($this->Core->parseBBCode($return['data'][$i]['c_body']), true);
			$i++;
		}
		//
		return $return;
	}
	/*
		newComentario()
	*/
	function newComentario(){
		global $tsActividad;
		
		// NO MAS DE 1500 CARACTERES PUES NADIE COMENTA TANTO xD
		$comentario = substr($_POST['comentario'],0,1500);
		$post_id = ($_POST['postid']);
		/* DE QUIEN ES EL POST */
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_user, post_block_comments FROM p_posts WHERE post_id = \''.(int)$post_id.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		/* COMPROBACIONES */
		$this->Text = preg_replace('# +#',"",$comentario);
		$this->Text = str_replace("\n","",$this->Text);
		if($this->Text == '') return '0: El campo <b>Comentario</b> es requerido para esta operaci&oacute;n';
		/*        ------       */
		$most_resp = $_POST['mostrar_resp'];
		$fecha = time();
		//
		if($data['post_user']){
			if($data['post_block_comments'] != 1 || $data['post_user'] == $this->User->uid || $this->User->is_admod || $this->User->permisos['mocepc']){
				if(empty($this->User->is_admod) && $this->User->permisos['gopcp'] == false) return '0: No deber&iacute;as hacer estas pruebas.';
				// ANTI FLOOD
				$this->Core->antiFlood();
				$_SERVER['REMOTE_ADDR'] = $_SERVER['X_FORWARDED_FOR'] ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
				if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) { die('0: Su ip no se pudo validar.'); }
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `p_comentarios` (`c_post_id`, `c_user`, `c_date`, `c_body`, `c_ip`) VALUES (\''.(int)$post_id.'\', \''.$this->User->uid.'\', \''.$fecha.'\', \''.$comentario.'\', \''.$_SERVER['REMOTE_ADDR'].'\')')) {
					$cid = db_exec('insert_id');
					//SUMAMOS A LAS ESTADÍSTICAS
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE w_stats SET stats_comments = stats_comments + 1 WHERE stats_no = \'1\'');
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_comments = post_comments +  1 WHERE post_id = \''.(int)$post_id.'\'');
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_comentarios = user_comentarios + 1 WHERE user_id = \''.$this->User->uid.'\'');
					// NOTIFICAR SI FUE CITADO Y A LOS QUE SIGUEN ESTE POST, DUEÑO
					$this->quoteNoti($post_id, $data['post_user'], $cid, $comentario);
					// ACTIVIDAD
					$tsActividad->setActividad(5, $post_id);
					// array(comid, comhtml, combbc, fecha, autor_del_post)
					if(!empty($most_resp)) return array($cid, $this->Core->parseBadWords($this->Core->parseBBCode($comentario), true),$comentario, $fecha, $_POST['auser'], '', $_SERVER['REMOTE_ADDR']);
				   else return '1: Tu comentario fue agregado satisfactoriamente.';
				} else return '0: Ocurri&oacute; un error int&eacute;ntalo m&aacute;s tarde.';
			} else return '0: El post se encuentra cerrado y no se permiten comentarios.';
		} else return '0: El post no existe.';
	}
	/*
		quoteNoti()
		:: Avisa cuando citan los comentarios.
	*/
	function quoteNoti($post_id, $post_user, $cid, $comentario){
		global $tsMonitor;
		$ids = array();
		$total = 0;
		//
		preg_match_all("/\[quote=(.*?)\]/is",$comentario,$users);
		//
		if(!empty($users[1])) {
			foreach($users[1] as $user){
				# DATOS
				$udata = explode('|',$user);
				$user = empty($udata[0]) ? $user : $udata[0];
				$lcid = empty($udata[1]) ? $cid : (int)$udata[1];
				# COMPROBAR
				if($user != $this->User->nick){
					$uid = $this->User->getUserID($this->Core->setSecure($user));
					if(!empty($uid) && $uid != $this->User->uid && !in_array($uid, $ids)){
						$ids[] = $uid;
						$tsMonitor->setNotificacion(9, $uid, $this->User->uid, $post_id, $lcid);
					}
					++$total;
				}
			}
		}
		// AGREGAR AL MONITOR DEL DUEÑO DEL POST SI NO FUE CITADO
		if(!in_array($post_user, $ids)){
			$tsMonitor->setNotificacion(2, $post_user, $this->User->uid, $post_id);
		}
		// ENVIAR NOTIFICAIONES A LOS Q SIGUEN EL POST :D
		// PERO NO A LOS QUE CITARON :)
		$tsMonitor->setFollowNotificacion(7, 2, $this->User->uid, $post_id, 0, $ids);
		// 
		return true;
	}
	/*
		editComentario()
	*/
	function editComentario(){
		//
		$cid = intval($_POST['cid']);
		$comentario =  $this->Core->parseBadWords($this->Core->setSecure(substr($_POST['comentario'],0,1500), true));
		/* COMPROBACIONES */
		$this->Text = preg_replace('# +#',"",$comentario);
		$this->Text = str_replace("\n","",$this->Text);
		if($this->Text == '') return '0: El campo <b>Comentario</b> es requerido para esta operaci&oacute;n';
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c_user FROM p_comentarios WHERE cid = \''.$cid.'\' LIMIT 1');
		$cuser = db_exec('fetch_assoc', $query);
		
		//
		if($this->User->is_admod || ($this->User->uid == $cuser['c_user'] && $this->User->permisos['goepc']) || $this->User->permisos['moedcopo']){
			// ANTI FLOOD
			$this->Core->antiFlood();
			if(db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_comentarios SET c_body = \''.$comentario.'\' WHERE cid = \''.(int)$cid.'\''))
				return '1: El comentario fue editado.';
			else return '0: Ocurri&oacute; un error :(';
		} else return '0: Hey, este comentario no es tuyo.';
	}
	/* 
		delComentario()
	*/
	function delComentario(){
		//
		$comid = $this->Core->setSecure($_POST['comid']);
		$autor = $this->Core->setSecure($_POST['autor']);
		//if(!empty($_POST['postid']) $post_id = intval($_POST['postid']); else  $post_id = intval($_POST['post_id']);
		$post_id = isset($_POST['postid']) ? intval($_POST['postid']) : intval($_POST['post_id']);
		$post_id = $this->Core->setSecure($_POST['postid']);
		// ES DE MI POST EL COMENTARIO?		        
		if(!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 'SELECT cid FROM p_comentarios WHERE cid = \''.(int)$comid.'\''))){ return '0: El comentario no existe'; } // [17/04/2012] Evitar miles de comentarios
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_id FROM p_posts WHERE post_id = \''.(int)$post_id.'\' AND post_user = \''.$this->User->uid.'\'');
		$is_mypost = db_exec('num_rows', $query);
		
		// ES MI COMENTARIO?
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT cid FROM p_comentarios WHERE cid = \''.(int)$comid.'\' AND c_user = \''.$this->User->uid.'\'');
		$is_mycmt = db_exec('num_rows', $query);
		
		// SI ES....
		if(!empty($is_mypost) || (!empty($is_mycmt) && !empty($this->User->permisos['godpc'])) || !empty($this->User->is_admod) || !empty($this->User->permisos['moecp'])){
			if(db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM p_comentarios WHERE cid = \''.(int)$comid.'\' AND c_user = \''.(int)$autor.'\' AND c_post_id = \''.(int)$post_id.'\'')) {
				// BORRAR LOS VOTOS
				db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM p_votos WHERE tid = \''.(int)$comid.'\'');
				// RESTAR EN LAS ESTADÍSTICAS
				db_exec([__FILE__, __LINE__], 'query', 'UPDATE w_stats SET stats_comments = stats_comments - 1 WHERE stats_no = \'1\'');
				db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_comments = post_comments - 1 WHERE post_id = \''.(int)$post_id.'\'');
				db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_comentarios = user_comentarios - 1 WHERE user_id = \''.(int)$autor.'\'');
				//
				return '1: Comentario borrado.';
			}else return '0: Ocurri&oacute; un error, intentalo m&aacute;s tarde.';
		} else return '0: No tienes permiso para hacer esto.';
	}
	
	/* 
		OcultarComentario()
	*/
	function OcultarComentario(){
		//
		if($this->User->is_admod || $this->User->permisos['moaydcp']){
		//
		$data = db_exec('fetch_assoc', $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT cid, c_user, c_post_id, c_status FROM p_comentarios WHERE cid = \''.(int)$_POST['comid'].'\''));
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE w_stats SET stats_comments = stats_comments '.($data['c_status'] == 1 ? '+' : '-').' 1 WHERE stats_no = \'1\'');
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_posts SET post_comments = post_comments '.($data['c_status'] == 1 ? '+' : '-').' 1 WHERE post_id = \''.$data['c_post_id'].'\'');
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_comentarios = user_comentarios '.($data['c_status'] == 1 ? '+' : '-').' 1 WHERE user_id = \''.$data['c_user'].'\'');
		// OCULTAMOS O MOSTRAMOS
		if(db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_comentarios SET c_status = '.($data['c_status'] == 1 ? '\'0\'' : '\'1\'').' WHERE cid = \''.(int)$_POST['comid'].'\'')) {
		if($data['c_status'] == 1) return '2: El comentario fue habilitado.';
		else return '1: El comentario fue ocultado.';
		} else return 'Ocurri&oacute; un error';
	 } else return '0: No tienes permiso para hacer eso.';
		
	}
	/*
		votarComentario()
	*/
	function votarComentario(){
		global $tsMonitor, $tsActividad;
		
		// VOTAR
		$cid = $this->Core->setSecure($_POST['cid']);
		$post_id = $this->Core->setSecure($_POST['postid']);
		$votoVal = ($_POST['voto'] == 1) ? 1 : 0;
		$voto = ($votoVal == 1) ? "+ 1" : "- 1";
		//COMPROBAMOS PERMISOS
		if(($votoVal == 1 && ($this->User->is_admod || $this->User->permisos['govpp'])) || ($votoVal == 0 && ($this->User->is_admod || $this->User->permisos['govpn'])) ){
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c_user FROM p_comentarios WHERE cid = \''.(int)$cid.'\' LIMIT 1');
		$data = db_exec('fetch_assoc', $query);
		
		// ES MI COMENTARIO?
		$is_mypost = ($data['c_user'] == $this->User->uid) ? true : false;
		// NO ES MI COMENTARIO, PUEDO VOTAR
		if(!$is_mypost){
			// YA LO VOTE?
			$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT tid FROM p_votos WHERE tid = \''.(int)$cid.'\' AND tuser = \''.$this->User->uid.'\' AND type = \'2\' LIMIT 1');
			$votado = db_exec('num_rows', $query);
			
			if(empty($votado)){
				// SUMAR VOTO
				db_exec([__FILE__, __LINE__], 'query', 'UPDATE p_comentarios SET c_votos = c_votos '.$voto.' WHERE cid = \''.(int)$cid.'\'');
				// INSERTAR EN TABLA
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO p_votos (tid, tuser, type) VALUES (\''.(int)$cid.'\', \''.$this->User->uid.'\', \'2\' ) ')){
					// SUMAR PUNTOS??
					if($votoVal == 1 && $this->Core->settings['c_allow_sump'] == 1) {
						db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_puntos = user_puntos +1 WHERE user_id = \''.$data['c_user'].'\'');
						$this->subirRango($data['c_user']);
					}
					// AGREGAR AL MONITOR
					$tsMonitor->setNotificacion(8, $data['c_user'], $this->User->uid, $post_id, $cid, $votoVal);
					// ACTIVIDAD
					$tsActividad->setActividad(6, $post_id, $votoVal);
				}
				//
				return '1: Gracias por tu voto';
			} return '0: Ya has votado este comentario';
		} else return '0: No puedes votar tu propio comentario';
	  } else return '0: No tienes permiso para hacer eso.';
	}

}