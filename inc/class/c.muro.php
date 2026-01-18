<?php

/**
 * @name c.muro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once dirname(__DIR__, 1) . '/helpers/CoreHelper.php';
require_once dirname(__DIR__, 1) . '/helpers/MuroHelper.php';

class tsMuro {

	protected tsCore $Core;
	protected tsUser $User;
	protected CoreHelper $CoreHelper;
	protected MuroHelper $MuroHelper;

	private array $status = [
		'muro' 				=> ['status' => true, 'message' => ''],
		'muro_firma' 		=> ['status' => true, 'message' => ''],
		'mesaje_privado' 	=> ['status' => true, 'message' => ''],
		'ultimas_visitas' => ['status' => true, 'message' => '']
	];
	
	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		$this->MuroHelper = new MuroHelper($Core->settings['url'], $User);
	}

	private function evaluatePrivacyRule(string $type, array $context, callable $deny, array $messages): void {
	   switch ($type) {
	   	case 'nobody':
	         $deny($messages['nobody']);
	      break;
	      case 'friends_mutual':
	         if (!$context['lesigoymesigue']) {
	            $deny($messages['friends_mutual']);
	         }
	      break;
	      case 'friends_any':
	         if (!$context['lesigoomesigue']) {
	           	$deny($messages['friends_any']);
	         }
	      break;
	      case 'following':
	        	if ($context['follow'] !== 1) {
	            $deny($messages['following']);
	         }
	      break;
	      case 'followers':
	         if ($context['yfollow'] !== 1) {
	            $deny($messages['followers']);
	         }
	      break;
	      case 'registered':
	         if (!$this->User->uid) {
	            $deny($messages['registered']);
	         }
	     	break;
	     	default:
	         $deny($messages['default']);
	      break;
	   }
	}

	private function getMuroConfig(array &$privacidad, array $context, string $type = ''): void {
   	// Excepciones globales
   	if ($context['isMe'] || $this->User->is_admod) {
   	   return;
   	}
   	$this->evaluatePrivacyRule(
      	$type,
      	$context,
      	function(string $message) use (&$privacidad) {
            $privacidad['muro']['status'] = false;
            $privacidad['muro']['message'] = $message;
        	},
         [
            'nobody' => "Lo sentimos pero {$context['username']} no permite ver su muro a nadie.",
            'friends_mutual' => "Debes seguir a {$context['username']} y éste debe seguirte para poder ver su muro.",
            'friends_any' => "Debes seguir a {$context['username']} o éste debe seguirte para poder ver su muro.",
            'following' => "Debes seguir a {$context['username']} para poder ver su muro.",
            'followers' => "{$context['username']} debe seguirte para que puedas ver su muro.",
            'registered' => "Solo usuarios <a href=\"{$this->Core->route('url')}/registro/\">registrados</a> pueden ver el muro de {$context['username']}",
            'default' => 'No tenés permisos para ver este muro.',
        ]
    );
	}

	private function getMuroPublicar(array &$privacidad, array $context, string $type = ''): void {
   	// Excepciones globales
   	if ($context['isMe'] || $this->User->is_admod) {
   	   return;
   	}
   	//string $privacy, array $context, callable $deny, array $messages
   	$this->evaluatePrivacyRule(
   	   $type,
   	   $context,
   	   function(string $message) use (&$privacidad) {
   	      $privacidad['muro_firma']['status'] = false;
   	      $privacidad['muro_firma']['message'] = $message;
   	   },
   	   [
   	      'nobody' => "Lo sentimos pero {$context['username']} no permite firmar su muro a nadie.",
   	      'friends_mutual' => "Debes seguir a {$context['username']} y éste debe seguirte para poder firmar su muro.",
   	      'friends_any' => "Debes seguir a {$context['username']} o éste debe seguirte para poder firmar su muro.",
   	      'following' => "Debes seguir a {$context['username']} para poder firmar su muro.",
   	      'followers' => "{$context['username']} debe seguirte para poder firmar su muro.",
   	      'registered' => "Solo usuarios registrados pueden firmar el muro de {$context['username']}",
   	      'default' => 'No tenés permisos para firmar este muro.',
   	   ]
   	);
	}
	
	/**
	 * @access public 
	 * @param int
	 * @param string
	 * @param int
	 * @param int
	*/
	public function getPrivacity(int $userId = 0, string $username = '', int $follow = 0, int $yfollow = 0): array {
		//
		$context = [
	      'isMe' => ((int)$this->User->uid === (int)$userId),
	      'username' => $username,
	      'follow' => $follow,
	      'yfollow' => $yfollow,
	      'lesigoymesigue' => ($follow === 1 AND $yfollow === 1 ? true : false),
	      'lesigoomesigue' => ($follow === 0 AND $yfollow === 0 ? false : true)
	   ];
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT p_privacidad, p_publicar_muro FROM u_perfil WHERE user_id = $userId LIMIT 1"));
		$privacidad = $this->status;
		// VER MURO
		$this->getMuroConfig($privacidad, $context, $data['p_privacidad']);
		$this->getMuroPublicar($privacidad, $context, $data['p_publicar_muro']);
		//
		return $privacidad;
	}
	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	/*
		ajaxCheck()
	*/
	public function ajaxCheck($return = false, $urlin = null){
		global $tsCore;
		//
		$type = $_GET['type'];
		$url = empty($urlin) ? $_POST['url'] : $urlin;
		// VALIDAR
		if(empty($url)) return '0: El campo <b>url</b> es obligatorio.';
		//
		switch($type){
			// VALIDAR UNA FOTO
			case 'foto':
				if(strlen($url) > 300) return '0: La url de la imagen es demasiado larga.';
				$data = getimagesize($url);
				// TAMAÑO MINIMO
				$min_w = 130;
				$min_h = 130;
				// MAX PARA EVITAR CARGA LENTA
				$max_w = 1024;
				$max_h = 1024;
				//
				if(empty($data[0])) return '0: La url ingresada no existe o no es una imagen v&aacute;lida.';
				elseif($data[0] < $min_w || $data[1] < $min_h) return '0: Tu foto debe tener un tama&ntilde;o superior a 130x130 pixeles.';
				elseif($data[0] > $max_w || $data[1] > $max_h) return '0: Tu foto debe tener un tama&ntilde;o menor a 1024x1024 pixeles.';
				else {
					if($return == false) return '1: <center><img src="'.$this->Core->setSecure($url, true).'"/></center>';
					else return $this->Core->setSecure($url, true);
				}
			break;
			// VALIDAR URL
			case 'enlace':
				// VALIDAR
				if(strlen($url) > 400) return '0: La url es demasiado larga.';
				$data = (new CoreHelper)->getUrlContent($url);
				// VALIDAR #1
				if(!$data) return '0: El enlace ingresado no es v&aacute;lido, no esta disponible o no existe.';
				// OBTENER META TITULO
				$title = explode('<title>',$data);
				$title = explode('</title>',$title[1]);
				$title = empty($title[0]) ? $url : $title[0];
				// VALIDAR #2
				if(!$title) return '0: La url ingresada no es una p&aacute;gina web v&aacute;lida.';
				// RETORNAMOS HTML/VALOR
				if($return == false) return '1: <center><a href="'.$url.'" target="_blank" class="big a_blue">'.$this->Core->setSecure($title, true).'</a><br/><span class="desc">'.$this->Core->setSecure($url, true).'</span></center>';
				else return array('title' => $this->Core->setSecure($title, true), 'url' => $this->Core->setSecure($url, true));
			break;
			// VALIDAR UN VIDE DE YOUTUBE
			case 'video':
				// VALIDAR #1
				$video_id = explode('watch?v=',$url);
				if(!is_array($video_id)) return '0: La direcci&oacute;n del video no es v&aacute;lida.';
				// VALIDAR #2
				$video_id = substr($video_id[1],0,11);
				if(strlen($video_id) != 11) return '0: La direcci&oacute;n del video no es v&aacute;lida.';
				// META TAGS
				$data = @get_meta_tags('http://www.youtube.com/watch?v='.$video_id);
				if(empty($data['title'])) return '0: La URL contiene un ID de video incorrecto o el video ha sido eliminado.';
				else {
					$description = str_replace('<br>','',html_entity_decode($data['description']));
					// RETORNAMOS HTML/VALORES
					if($return == false)
					return '1: <div class="vContent"><img src="http://img.youtube.com/vi/'.$video_id.'/0.jpg" class="thumb"/><div class="vDesc"><strong><a href="http://www.youtube.com/watch?v='.$video_id.'" target="_blank" class="a_blue">'.$data['title'].'</a></strong><div style="margin-top:5px">'.$description.'</div></div><div class="clearBoth"></div></div>';
					else return array('ID' => $video_id, 'title' => $this->Core->setSecure($data['title'], true), 'desc' => $this->Core->setSecure(substr($description,0,160)), true);
				}
				//
				
			break;
			default:
				return '0: El campo <b>type</b> es obligatorio.';
			break;
		}
	}
	/* 
		streamPost()
	*/
	public function streamPost(){
		global $tsCore, $tsUser, $tsMonitor, $tsActividad;
		//
		$pid = intval($_POST['pid']);
		$data = $this->Core->setSecure($_POST['data'], true);
		$adj = $this->Core->setSecure($_POST['adj'], true);
		$type = $_GET['type'];
		// VALIDAMOS SI EXISTE EL PERFIL/USUARIO
		$exists = $this->User->getUserName($pid);
		if(empty($exists)) return '0: El usuario al que intentas comentar no existe.';
		// VERIFICAR QUE PERMITA COMPARTIR EN SU MURO
		include 'c.cuenta.php';
		$tsCuenta = new tsCuenta();
		$priv = $this->getPrivacity($pid, $exists, $tsCuenta->iFollow($pid), $tsCuenta->yFollow($pid));
		// SE PERMITE FIRMAR EL MURO?
		if($priv['mf']['v'] == false) return '0: '.$priv['mf']['m'];
		// VARIABLES COMUNES
		$date = time(); 
		$_SERVER['REMOTE_ADDR'] = $_SERVER['X_FORWARDED_FOR'] ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		// TIPO DE PUBLICACION
		switch($type){
			// PUBLICAR STATUS/PUBLICACION
			case 'status':
				//$text = preg_replace('# +#',"",$data);
				$text = str_replace(array("\n","\t",' '),"",$data);
				// VACIO?
				if(strlen($text) <= 0) return '0: Tu publicaci&oacute;n debe tener al menos una letra.';
				// ANTI FLOOD
				$this->Core->antiFlood();
				//				//
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (\''.(int)$pid.'\', \''.$this->User->uid.'\', \''.$data.'\', \''.$date.'\', \'1\', \''.$_SERVER['REMOTE_ADDR'].'\') ')){
					$pub_id = db_exec('insert_id');
					//
					$type = ($pid == $this->User->uid) ? 'status' : 'mpub';
					// RETORNAMOS DATOS PARA EL TEMPLATE
					$return = array('pub_id' => $pub_id, 'p_user' => $pid, 'p_user_pub' => $this->User->uid, 'p_body' => $this->Core->parseBadWords($this->MuroHelper->setMenciones($data), true), 'p_date' => $date, 'p_likes' => 0, 'p_type' => 1, 'likes' => array('link' => 'Me gusta'));
				}
			break;
			 // PUBLICAR FOTO
			case 'foto':
				// VALIDAMOS
				$foto = $this->ajaxCheck(true, $adj);
				if(substr($foto,0,1) == '0') return $foto;
				// ANTI FLOOD
				$this->Core->antiFlood();
				// INSERTAMOS
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (\''.(int)$pid.'\', \''.$this->User->uid.'\', \''.$this->Core->setSecure($data, true).'\', \''.$date.'\', \'2\', \''.$this->Core->setSecure($_SERVER['REMOTE_ADDR']).'\')')){
					$pub_id = db_exec('insert_id');
					// INSERTAR ADJUNTO
					if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro_adjuntos (pub_id, a_url, a_img) VALUES (\''.(int)$pub_id.'\', \''.$this->Core->setSecure($foto, true).'\', \''.$this->Core->setSecure($foto, true).'\') ')){
						$type = 'mfoto';
						// RETORNAMOS DATOS PARA EL TEMPLATE
						$return = array('pub_id' => $pub_id, 'p_user' => $pid, 'p_user_pub' => $this->User->uid, 'p_body' => $this->MuroHelper->setMenciones($data), 'p_date' => $date, 'p_likes' => 0, 'p_type' => 2, 'likes' => array('link' => 'Me gusta'), 'a_url' => $foto, 'a_img' => $foto);   
					}
				}
			break;
			// PUBLICAR ENLACE
			case 'enlace':
				// VALIDAR
				$enlace = $this->ajaxCheck(true, $adj);
				// ANTI FLOOD
				$this->Core->antiFlood();
				// INSERTAR
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (\''.(int)$pid.'\', \''.$this->User->uid.'\', \''.$data.'\', \''.$date.'\', \'3\', \''.$_SERVER['REMOTE_ADDR'].'\' ) ')){
					$pub_id = db_exec('insert_id');
					// INSERTAR ADJUNTO
					if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro_adjuntos (pub_id, a_title, a_url) VALUES (\''.(int)$pub_id.'\', \''.$this->Core->setSecure($this->Core->parseBadWords($enlace['title']), true).'\', \''.$this->Core->setSecure($this->Core->parseBadWords($enlace['url']), true).'\') ')){
						$type = 'mlink';
						// RETORNAMOS DATOS PARA EL TEMPLATE
						$return = array('pub_id' => $pub_id, 'p_user' => $pid, 'p_user_pub' => $this->User->uid, 'p_body' => $this->MuroHelper->setMenciones($data), 'p_date' => $date, 'p_likes' => 0, 'p_type' => 3, 'likes' => array('link' => 'Me gusta'), 'a_title' => $enlace['title'], 'a_url' => $enlace['url']);   
					}
				}
			break;
			// PUBLICAR VIDEO
			case 'video':
				// VALIDAR
				$video = $this->ajaxCheck(true, $adj);
				// ANTI FLOOD
				$this->Core->antiFlood();
				// INSERTAR
				if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (\''.(int)$pid.'\', \''.$this->User->uid.'\', \''.$this->Core->setSecure($data, true).'\', \''.$date.'\', \'4\', \''.$this->Core->setSecure($_SERVER['REMOTE_ADDR']).'\') ')){
					$pub_id = db_exec('insert_id');
					// INSERTAR ADJUNTO
					if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro_adjuntos (pub_id, a_title, a_url, a_img, a_desc) VALUES (\''.(int)$pub_id.'\', \''.$this->Core->setSecure($this->Core->parseBadWords($video['title']), true).'\', \''.$video['ID'].'\', \'\', \''.$this->Core->setSecure($this->Core->parseBadWords($video['desc']), true).'\') ')){
						$type = 'mvideo';
						// RETORNAMOS DATOS PARA EL TEMPLATE
						$return = array('pub_id' => $pub_id, 'p_user' => $pid, 'p_user_pub' => $this->User->uid, 'p_body' => $this->MuroHelper->setMenciones($data), 'p_date' => $date, 'p_likes' => 0, 'p_type' => 4, 'likes' => array('link' => 'Me gusta'), 'a_title' => $video['title'], 'a_url' => $video['ID'], 'a_desc' => $video['desc']);   
					}
				}
			break;
			default:
				$return = '0: El campo <b>type</b> es obligatorio.';
			break;
		}
		//
		$return['user_name'] = $this->User->nick;
		// MONITOR
		$tsMonitor->setNotificacion(12, $pid, $this->User->uid, $pub_id);
		// ACTIVIDAD
		$is_my = ($pid == $this->User->uid) ? 0 : 2;
		$tsActividad->setActividad(10, $pub_id, $is_my);
		// RETORNAR VALOR
		return $return;
		
	}
	/*
		streamRepost()
	*/
	function streamRepost(){
		global $tsCore, $tsUser, $tsMonitor, $tsActividad;
		//
		$data = $this->Core->setSecure($this->Core->parseBadWords($_POST['data']));
		$pid = intval($_POST['pid']);
		//
	   $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT `p_user`, `p_user_pub` FROM `u_muro` WHERE `pub_id` = \''.(int)$pid.'\' LIMIT 1');
	   $pub = db_exec('fetch_assoc', $query);
		
		//
		if($pub['p_user'] > 0){
			// VACIO?
			$text = str_replace(array("\n","\t",' '),"",$data);
			if(strlen($text) <= 0) return '0: Tu comentario debe tener al menos una letra.';
			// ANTI FLOOD
			$this->Core->antiFlood();
			// CONTINUAMOS
			$date = time();
			$_SERVER['REMOTE_ADDR'] = $_SERVER['X_FORWARDED_FOR'] ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
			if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO `u_muro_comentarios` (`pub_id`, `c_user`, `c_date`, `c_body`, `c_ip`) VALUES (\''.(int)$pid.'\', \''.$this->User->uid.'\', \''.$date.'\', \''.$this->Core->setSecure($data, true).'\', \''.$_SERVER['REMOTE_ADDR'].'\')')){
				$cid = db_exec('insert_id');
				// MONITOR
				$tsMonitor->setMuroRepost($pid, $pub['p_user'], $pub['p_user_pub']);
				// ACTIVIDAD
				$is_my = ($pub['p_user'] == $this->User->uid) ? 1 : 3;
				$tsActividad->setActividad(10, $cid, $is_my);
				// UPDATES
				db_exec([__FILE__, __LINE__], 'query', 'UPDATE `u_muro` SET `p_comments` = p_comments + 1 WHERE `pub_id` = \''.(int)$pid.'\'');
				// PARA LA PANTILLA
				return array('cid' => $cid, 'c_body' => $this->Core->parseBadWords($data, true), 'c_date' => $date, 'c_user' => $this->User->uid, 'c_likes' => 0, 'like' => 'Me gusta','user_name' => $this->User->nick);
			} else return '0: '.show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db');
		} else return '0: La publicaci&oacute;n no existe.';
	}
	/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	/*
		getNews()
	*/
	function getNews($start = 0, $limit = 10){
		global $tsUser, $tsCore;
		// SOLO MOSTRAREMOS LAS ULTIMAS 100 PUBLICACIONES
		if($start > 90) return array('total' => '-1');
		// SEGUIDORES
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT f_id FROM u_follows WHERE f_user = \''.$this->User->uid.'\' AND f_type = \'1\'');
		$follows = result_array($query);
		
		// ORDENAMOS 
		foreach($follows as $key => $val){
			// PERMISO PARA VER SUS PUBLICACIONES??
			$priv = $this->getPrivacity($val['f_id'], null, true);
			if($priv['m']['v'] == true)
				$amigos[] = "'".$val['f_id']."'";
		}
		$amigos[] = "'$this->User->uid'";
		$amigos = implode(', ',$amigos);
		// OBTENEMOS LAS ULTIMAS PUBLICACIONES
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT p.*, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.p_user IN('.$amigos.') AND p.p_user = p.p_user_pub ORDER BY p.p_date DESC LIMIT '.$start.','.$limit);
		while($row = db_exec('fetch_array', $query)){
			// CARGAR LIKES
			if($row['p_likes'] > 0){
				$row['likes'] = $this->getPubExtras($row['pub_id'], 'likes', $row['p_likes']);
			} else $row['likes'] = array('link' => 'Me gusta');
			// CARGAR COMENTARIOS
			if($row['p_comments'] > 0){
				$row['comments'] = $this->getPubExtras($row['pub_id'], 'comments', 2);
			}
			// MENCIONES
			$row['p_body'] = $this->Core->parseBadWords($this->MuroHelper->setMenciones($row['p_body']), true);
			// CARGAR ADJUNTOS
			if($row['p_type'] != 1){
				$queryDos = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_muro_adjuntos WHERE pub_id = \''.$row['pub_id'].'\' LIMIT 1');
				$adj = db_exec('fetch_assoc', $queryDos);
				
				//
				$data[] = array_merge($row,$adj); 
			} else $data[] = $row;
			//
		}
		
		//die(count($data));
		// RETORNAMOS
		return array('total' => (empty($data) ? 0 : count($data)), 'data' => $data);
	}
	/*
		getWall($count)
	*/
	function getWall($userId, $start = 0){
		global $tsCore;
		// PUBLICACION
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT p.*, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.p_user = \''.(int)$userId.'\' ORDER BY p.pub_id DESC LIMIT '.$start.',10');
		$data = [];
		while($row = db_exec('fetch_array', $query)){
			// CARGAR LIKES
			if($row['p_likes'] > 0){
				$row['likes'] = $this->getPubExtras($row['pub_id'], 'likes', $row['p_likes']);
			} else $row['likes'] = array('link' => 'Me gusta');
			// CARGAR COMENTARIOS
			if($row['p_comments'] > 0){
				$row['comments'] = $this->getPubExtras($row['pub_id'], 'comments', 2);
			}
			// MENCIONES
			$row['p_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($this->MuroHelper->setMenciones($row['p_body'])), true);
			// CARGAR ADJUNTOS
			if($row['p_type'] != 1){
				$queryDos = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_muro_adjuntos WHERE pub_id = \''.$row['pub_id'].'\' LIMIT 1');
				$adj = db_exec('fetch_assoc', $queryDos);
				
				//
				$data[] = array_merge($row,$adj); 
			} else $data[] = $row;
		}
		
		//
		return array('total' => (empty($data) ? 0 : count($data)), 'data' => $data);
	}
	/*
		getPubExtras($pud_id, $type)
	*/
	function getPubExtras($pub_id, $type = 'likes', $likes = 0){
		global $tsUser, $tsCore;
		//
		switch($type){
			case 'likes':
				if(empty($likes)) return array('link' => 'Me gusta', 'text' => '');
				// VARIABLES
				$data['link'] = 'Me gusta';
				$i_like = false;
				// VEMOS SI ME GUSTA
				if($this->User->is_member){
					$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT `like_id` FROM `u_muro_likes` WHERE `user_id` = \''.$this->User->uid.'\' AND `obj_id` = \''.(int)$pub_id.'\' AND obj_type = \'1\'');
					$i_like = db_exec('num_rows', $query);
					   
				}
				// TEXOS
				if($likes == 1){
					if($i_like) {
						$data['link'] = 'Ya no me gusta';
						$data['text'] = 'Te gusta esto.';   
					}else {
						$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_name FROM u_muro_likes AS l LEFT JOIN u_miembros AS u ON l.user_id = u.user_id  WHERE l.obj_id = \''.(int)$pub_id.'\' AND l.obj_type = \'1\'');
						$u_like = db_exec('fetch_assoc', $query);
						
						//
						$data['text'] = 'A <a href="'.$this->Core->settings['url'].'/perfil/'.$u_like['user_name'].'">'.$u_like['user_name'].'</a> le gusta esto.';
					}
				} elseif($likes == 2){
					if($i_like){
						$data['link'] = 'Ya no me gusta';
						//
						$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_name FROM u_muro_likes AS l LEFT JOIN u_miembros AS u ON l.user_id = u.user_id  WHERE l.user_id != \''.$this->User->uid.'\' AND l.obj_id = \''.(int)$pub_id.'\' AND l.obj_type = 1');
						$u_like = db_exec('fetch_assoc', $query);
						
						//
						$data['text'] = 'A <a href="'.$this->Core->settings['url'].'/perfil/'.$u_like['user_name'].'">'.$u_like['user_name'].'</a> y a ti os gusta esto.';
					} else {
						$data['text'] = 'A <a onclick="muro.show_likes('.$pub_id.', \'pub\'); return false;">'.$likes.' personas</a> les gusta esto.';
					}
				} elseif($likes > 2){
					if($i_like){
						$data['link'] = 'Ya no me gusta';
						$data['text'] = 'A ti y a <a onclick="muro.show_likes('.$pub_id.', \'pub\'); return false;">otras '.($likes-1).' personas m&aacute;s</a> les gusta esto.';
					} else {
						$data['text'] = 'A <a onclick="muro.show_likes('.$pub_id.', \'pub\'); return false;">'.$likes.' personas</a> les gusta esto.';
					}
				}
			break;
			case 'comments':
				$limit = ($likes > 0) ? "LIMIT {$likes}" : '';
				//
				$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c.*, u.user_name FROM u_muro_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user = u.user_id WHERE c.pub_id = \''.(int)$pub_id.'\' ORDER BY c.c_date DESC '.$limit.'');
				while($row = db_exec('fetch_array', $query)){
					$row['c_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($this->MuroHelper->setMenciones($row['c_body'])), true);
					$row['like'] = 'Me gusta';
					// ME GUSTA?
					if($row['c_likes'] > 0){
						//
						$cuery = db_exec([__FILE__, __LINE__], 'query', 'SELECT `like_id` FROM `u_muro_likes` WHERE `user_id` = \''.$this->User->uid.'\' AND `obj_id` = \''.$row['cid'].'\' AND `obj_type` = \'2\'');
						$i_like = db_exec('num_rows', $cuery);
						
						if($i_like > 0) $row['like'] = 'Ya no me gusta';
						//
					}
					//
					
					$data[] = $row;
				}
				
				// ORDENAMOS
				asort($data);
				//
			break;
		}
		//
		return $data;
	}
	/*
		getStory()
	*/
	function getStory($pub_id, $userId){
		global $tsUser;
		// ELEGIMOS
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT p.*, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.pub_id = \''.(int)$pub_id.'\' LIMIT 1');
		$pub = db_exec('fetch_assoc', $query);
		
		// COMPROBAMOS
		if(empty($pub['pub_id'])) return 'La publicaci&oacute;n que has solicitado no existe.';
		elseif($userId != $pub['p_user']) return 'La publicaci&oacute;n que has solicitado no pertenece al perfil de <b>'.$this->User->getUserName($userId).'</b>.';
		// CARGAR LIKES
		if($pub['p_likes'] > 0){
			$pub['likes'] = $this->getPubExtras($pub['pub_id'], 'likes', $pub['p_likes']);
		} else $pub['likes'] = array('link' => 'Me gusta'); // FIX: 08/11/2014
		// CARGAR COMENTARIOS
		if($pub['p_comments'] > 0){
			$pub['comments'] = $this->getPubExtras($pub['pub_id'], 'comments');
		}
		// EXTRA
		$pub['hide_more_cm'] = true;
		// ADJUNTOS
		if($pub['p_type'] != 1){
			$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM u_muro_adjuntos WHERE pub_id = \''.(int)$pub_id.'\' LIMIT 1');
			$adj = db_exec('fetch_assoc', $query);
			
			$data = array_merge($pub,$adj);
		} else $data = $pub;
		// RETORNAMOS
		return $data;
	}
	/*
		getComments()
	*/
	function getComments(){
		global $tsCore;
		//
		$pid = (int) $_POST['pid'];
		// EXISTE?
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT `p_user`, `p_comments` FROM `u_muro` WHERE `pub_id` = \''.$pid.'\' LIMIT 1');
		$cmts = db_exec('fetch_assoc', $query);
		
		//
		if(!empty($cmts)){
			$data['data'] = $this->getPubExtras($pid, 'comments');
			// TOTAL / USER DUEÑO DE LA APLICACION
			$data['total'] = $cmts['p_comments'];
			$data['user'] = $cmts['p_user'];
			//
			return $data;
		} else return '0: La publicaci&oacute;n no existe.';
	}
	/*
		delete()
	*/
	function deletePost(){
		global $tsCore, $tsUser;
		//
		$id = $this->Core->setSecure($_POST['id']);
		$type = ($_POST['type'] == 'pub') ? 'pub' : 'cmt';
		//
		switch($type){
			case 'pub':
				// DATOS -robert
				$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT `p_user`, `p_user_pub` FROM `u_muro` WHERE `pub_id` = \''.(int)$id.'\' LIMIT 1');
				$data = db_exec('fetch_assoc', $query);
				
				//
				if(!empty($data['p_user'])){
					// SI ES EL DUEÑO DEL MURO O DE LA PUBLICACION...
					if($data['p_user'] == $this->User->uid || $data['p_user_pub'] == $this->User->uid || $this->User->is_admod || $this->User->permisos['moepm']){
					   if(db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro` WHERE `pub_id` = \''.(int)$id.'\'')){
							// BORRAMOS LOS LIKES DE LA PUBLICACION
						   db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_likes` WHERE `obj_id` = \''.(int)$id.'\' AND `obj_type` = \'1\'');
							// BORRAMOS LOS LIKES DE TODOS LOS COMENTARIOS
						   $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT `cid` FROM `u_muro_comentarios` WHERE `pub_id` = \''.(int)$id.'\'');
							$cmnts = result_array($query);
							
							// IDS A BORRAR
							foreach($cmnts as $key => $val){
								$delete_ids .= $val['cid'].', ';
							}
							db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_likes` WHERE `obj_id` IN (\''.$delete_ids.'\') AND `obj_type` = \'2\'');
							// BORRAR COMENTARIOS
							db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_comentarios` WHERE `pub_id` = \''.(int)$id.'\'');
							db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_adjuntos` WHERE `pub_id` = \''.(int)$id.'\'');
							//
							return '1: OK';
						} else return '0: Error';
					} else return '0: Hmmm... &iquest;Haciendo pruebas?';
				} else return '0: La publicaci&oacute;n no existe.';
			break;
			// ELIMINAR COMENTARIO
			case 'cmt':
				// DATOS
				$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c.cid, c.c_user, p.pub_id, p.p_user FROM u_muro_comentarios AS c LEFT JOIN u_muro AS p ON c.pub_id = p.pub_id WHERE c.cid = \''.(int)$id.'\' LIMIT 1');
				$data = db_exec('fetch_assoc', $query);
				
				//
				if(!empty($data['cid'])){
					// SI ES EL DUEÑO DEL MURO O DEL COMENTARIO...
					if($data['p_user'] == $this->User->uid || $data['c_user'] == $this->User->uid  || $this->User->is_admod || $this->User->permisos['moecm']){
						if(db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_comentarios` WHERE `cid` = \''.(int)$id.'\'')){
							// UPDATES
							db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `u_muro_likes` WHERE `obj_id` = \''.(int)$id.'\' AND `obj_type` = \'2\'');
							db_exec([__FILE__, __LINE__], 'query', 'UPDATE `u_muro` SET `p_comments` = p_comments - 1 WHERE `pub_id` = \''.$data['pub_id'].'\'');
							//
							return '1: Ok';
						}
					} else return '0: Hmmm... &iquest;Haciendo pruebas?';
				} else return '0: El comentario no existe.';
			break;
		}
	}
	/*
		likePost()
	*/
   function likePost(){
		global $tsUser, $tsCore, $tsMonitor, $tsActividad;
		// ANTI FLOOD
		$text = $this->Core->antiFlood(false, 'like', 'No te pueden gustar tantas cosas en tan poco tiempo.');
		if($text != 1) return array('status' => 'error', 'text' => $text);
		//
		$id = (int)$_POST['id'];
		$type = ($_POST['type'] == 'com') ? 2 : 1;
		$status = 'ok';
		// EXISTE O NO
		$sql = ($type == 1) ? "SELECT p_user AS uid FROM u_muro WHERE pub_id = {$id}" : "SELECT c_user AS uid FROM u_muro_comentarios WHERE cid = {$id}";
		$query = db_exec([__FILE__, __LINE__], 'query', $sql);
		$exists = db_exec('fetch_assoc', $query);
		
		
		if(empty($exists['uid'])) return '0: La publicaci&oacute;n ya no existe.';
		// CHECAMOS SI YA LE GUSTA ESTO
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT like_id, user_id FROM u_muro_likes WHERE obj_id = \''.(int)$id.'\' AND obj_type = \''.(int)$type.'\'');
		$likes = result_array($query);
		
		$total = count($likes);
		// CHECAMOS
		$i_like = 0;
		foreach($likes as $key => $val){
			if($val['user_id'] == $this->User->uid) $i_like = $val['like_id'];
		}
		// SI AUN NO ME GUSTA
		if(empty($i_like)){
			if(db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO u_muro_likes (user_id, obj_id, obj_type) VALUES (\''.$this->User->uid.'\', \''.(int)$id.'\', \''.(int)$type.'\')')){
				// SUMAR LIKE
				if($type == 1) {
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_muro SET p_likes = p_likes + 1 WHERE pub_id = \''.(int)$id.'\'');
					$ac_type = ($exists['uid'] == $this->User->uid) ? 0 : 2; 
				}
				else {
					db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_muro_comentarios SET c_likes = c_likes + 1 WHERE cid = \''.(int)$id.'\'');
					$ac_type = ($exists['uid'] == $this->User->uid) ? 1 : 3;
				}
				// MONITOR
				$tsMonitor->setNotificacion(14, $exists['uid'], $this->User->uid, $id, $type);
				// ACTIVIDAD
				$tsActividad->setActividad(11, $id, $ac_type);
				//
			} else $status = 'error';
		}
		else {
			if(db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM u_muro_likes WHERE like_id = \''.(int)$i_like.'\'')){
				// RESTAR LIKE
				if($type == 1) db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_muro SET p_likes = p_likes - 1 WHERE pub_id = \''.(int)$id.'\'');
				else db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_muro_comentarios SET c_likes = c_likes - 1 WHERE cid = \''.(int)$id.'\'');
			}
			else $status = 'error';
		}
		// RESPUESTA
		if($type == 1){
			$t_likes = empty($i_like) ? ($total+1) : ($total-1);
			//
			$data = $this->getPubExtras($id, 'likes', $t_likes);
			$link = $data['link'];
			$text = $data['text'];
		} else {
			$t_likes = empty($i_like) ? ($total+1) : ($total-1);
			$ed_like = ($t_likes > 1) ? 's' : '';
			
			$link = empty($i_like) ? 'Ya no me gusta' : 'Me gusta';
			$text = ($t_likes > 0) ? $t_likes.' persona'.$ed_like : '';
		}
		//
		return array('status' => $status, 'link' => $link, 'text' => $text);
	}
	/*
		showLikes()
	*/
	function showLikes(){
		//
		$id = intval($_POST['id']);
		$type = ($_POST['type'] == 'com') ? 2 : 1;
		//
		$query = db_exec([__FILE__, __LINE__], 'query', 'SELECT l.user_id, u.user_name FROM u_muro_likes AS l LEFT JOIN u_miembros AS u ON l.user_id = u.user_id WHERE obj_id = \''.(int)$id.'\' AND obj_type = \''.(int)$type.'\'');
		$data = result_array($query);
		
		//
		if(empty($data)) return array('status' => 0, 'data' => 'La publicaci&oacute;n no existe.');
		//
		return array('status' => 1, 'data' => $data);
	}
}