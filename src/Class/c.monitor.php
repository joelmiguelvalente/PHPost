<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsMonitor {

	/**
	 * @name notificaciones 
	 * @access public
	 * @info NUMERO DE NOTIFICACIONES NUEVAS
	 **/
	public $notificaciones = 0;

	/**
	 * @name avisos
	 * @access public
	 * @info NUMERO DE AVISOS/ALERTAS
	 */
	public $avisos = 0;

	/**
	 * @name monitor
	 * @access private
	 * @info ORACIONES PARA CADA NOTIFICACION
	 **/
	private $monitor = [];

	/**
	 * @name show_type
	 * @access public
	 * @info COMO MOSTRAREMOS LAS NOTIFICACIONES -> AJAX/NORMAL
	 **/
	public $show_type = 1;

	private const NOTIF_FOLLOW = 4;

	/*
		constructor()
	*/
	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected UrlHelper $UrlHelper,
		protected Paginator $Paginator,
		protected Avatar $Avatar
	) {
		// VISITANTE?
		if($this->User->is_member === 0) {
			return false;
		}
		// NOTIFICACIONES
		$this->notificaciones = DB::value("SELECT COUNT(not_id) FROM u_monitor WHERE user_id = :uid AND not_menubar > 0", ['uid' => $this->User->uid]);
		// AVISOS
		$this->avisos = DB::value("SELECT COUNT(av_id) FROM u_avisos WHERE user_id = :uid AND av_read = 0", ['uid' => $this->User->uid]);
	}

	/**
	 * @name makeMonitor
	 * @access private
	 * @param none
	 * @return none
	 */
	private function makeMonitor(): void {
		$this->monitor = [
			1 => ['text' => 'agreg� a favoritos tu', 'ln_text' => 'post', 'css' => 'star'],
			2 => ['text' => ['coment� tu','@FLAG nuevos comentarios en tu'], 'ln_text' => 'post', 'css' => 'comment_post'],
			3 => ['text' => 'dej� @FLAG puntos en tu', 'ln_text' => 'post', 'css' => 'points'],
			4 => ['text' => 'te est� siguiendo', 'ln_text' => 'Seguir a este usuario', 'css' => 'follow'],
			5 => ['text' => 'cre� un nuevo', 'ln_text' => 'post', 'css' => 'post'],
			6 => ['text' => ['te recomienda un', '@FLAG usuarios te recomiendan un'], 'ln_text' => 'post', 'css' => 'share'],
			7 => ['text' => ['coment� en un', '@FLAG nuevos comentarios en el'], 'ln_text' => 'post', 'extra' => 'que sigues', 'css' => 'blue_ball'],
			8 => ['text' => ['vot� @FLAG tu', '@FLAG nuevos votos a tu'], 'ln_text' => 'comentario', 'css' => 'voto_'],
			9 => ['text' => ['respondi� tu', '@FLAG nuevas respuestas a tu'], 'ln_text' => 'comentario', 'css' => 'comment_resp'],
			10 => ['text' => 'subi� una nueva', 'ln_text' => 'foto', 'css' => 'photo'],
			11 => ['text' => ['coment� tu','@FLAG nuevos comentarios en tu'], 'ln_text' => 'foto', 'css' => 'photo'],
			12 => ['text' => 'public� en tu', 'ln_text' => 'muro', 'css' => 'wall_post'],
			13 => ['text' => ['coment� ', '@FLAG nuevos comentarios en'], 'ln_text' => 'publicaci�n', 'extra' => 'coment�', 'css' => 'w_comment'],
			14 => ['text' => ['le gusta tu', 'A @FLAG personas les gusta tu'], 'ln_text' => ['publicaci�n','comentario'], 'css' => 'w_like'],
			15 => ['text' => 'Recibiste una medalla', 'css' => 'medal'],
			16 => ['text' => 'Tu post recibi� una medalla', 'css' => 'medal'],
			17 => ['text' => 'Tu foto recibi� una medalla', 'css' => 'medal'],
		];
	}

	/**
	 * @name setAviso
	 * @access public
	 * @param int, string, string
	 * @return bool
	 * @info ENVIA UN AVISO/ALERTA
	*/
	public function setAviso(int $userId = 0, string $subject = '(sin asunto)', string $body = '', int $type = 0): bool {
		$this->User->userIsBan($userId);
		# INSERTAMOS EL AVISO
		$subject = Html::escape($subject);
		$body = Html::escape($body);
		return (DB::insert('u_avisos', [
			'user_id' => $userId,
			'av_subject' => $subject,
			'av_body' => $body,
			'av_date' => time(),
			'av_type' => $type
		]));
	}

	/**
	 * @name getAvisos
	 * @access public
	 * @param none
	 * @return array
	 * @info OBTIENE LOS MENSAJES Y ALERTAS DEL USUARIO
	 */
	public function getAvisos(): array {
		return DB::fetchAll("SELECT * FROM u_avisos WHERE user_id = :uid", ['uid' => $this->User->uid]);
	}

	private function getAviso(int $avId): array|false {
	   $data = DB::fetch("SELECT av_id, user_id, av_subject, av_body, av_date, av_read, av_type FROM u_avisos WHERE av_id = :aid LIMIT 1", ['aid' => $avId]);
	   if (empty($data['av_id'])) return false;
	   if ((int)$data['user_id'] !== $this->User->uid && !$this->User->is_admod) return false;
	   return $data;
	}

	public function readAviso(int $avId = 0): array|string {
	   $data = $this->getAviso($avId);
	   if ($data === false) return 'El aviso no existe';

	   DB::update('u_avisos', ['av_read' => 1], 'av_id = :aid', ['aid' => $avId]);
	   $this->avisos--;
	   return $data;
	}

	public function delAviso(int $avId = 0): bool {
	   if ($this->getAviso($avId) === false) return false;
	   DB::delete('u_avisos', 'av_id = :aid', ['aid' => $avId]);
	   return true;
	}

	/**
	 * @name setNotificacion
	 * @access public
	 * @param int $type       Tipo de notificaci�n
	 * @param int $userId     Usuario que recibe
	 * @param int $objUser    Usuario origen
	 * @param int $objUno     ID del objeto principal (post, comment, etc.)
	 * @param int $objDos     ID secundario (opcional)
	 * @param int $objTres    ID terciario (opcional)
	 * @return void
	 */
	public function setNotificacion(int $type, int $userId, int $objUser, int $objUno = 0, int $objDos = 0, int $objTres = 0): void {
	   if ($userId === $this->User->uid) return;
	   if (!$this->allowNotifi($type, $userId)) return;

	   $time   = time();
	   $tiempo = $time - 3600;

	   $notData = DB::fetch("SELECT not_id FROM u_monitor WHERE user_id = :uid AND obj_uno = :ouno AND obj_dos = :odos AND not_type = :type AND not_date > :time AND not_menubar > 0 ORDER BY not_id DESC LIMIT 1", ['uid' => $userId, 'ouno' => $objUno, 'odos' => $objDos, 'type' => $type, 'time' => $tiempo]);

	   // LIMITE DE NOTIFICACIONES
	   $data = DB::fetchAll("SELECT not_id FROM u_monitor WHERE user_id = :uid ORDER BY not_id DESC", ['uid' => $userId]);
	   $ntotal = count($data);
	   if ($ntotal > (int)$this->Core->settings['c_max_nots']) {
	      DB::delete('u_monitor', 'not_id = :nid', ['nid' => $data[$ntotal - 1]['not_id']]);
	   }

	   // ACTUALIZAR O INSERTAR
	   if (!empty($notData['not_id']) && $type !== self::NOTIF_FOLLOW) {
	      DB::raw("UPDATE u_monitor SET obj_user = :user, not_date = :time, not_total = not_total + 1 WHERE not_id = :nid", ['user' => $objUser, 'time' => $time, 'nid' => $notData['not_id']]);
	   } else {
	      DB::insert('u_monitor', [
	         'user_id'  => $userId,
	         'obj_user' => $objUser,
	         'obj_uno'  => $objUno,
	         'obj_dos'  => $objDos,
	         'obj_tres' => $objTres,
	         'not_type' => $type,
	         'not_date' => $time
	      ]);
	   }
	}

	/**
	 * @name setFollowNotificacion
	 * @access public
	 * @params int
	 * @return void
	 * @info Envia notificaciones a los usuarios que siguen a un post o usuario.
	*/
	public function setFollowNotificacion(int $notType = 0, int $fType = 0, int $userId = 0, int $objUno = 0, int $objDos = 0, array $excluir = []):bool {
		# TIPO DE FOLLOW USER o POST
		$fId = match($fType) {
		   1 => $userId,
		   2 => $objUno,
		   default => $objUno
		};
		# BUSCAMOS LOS Q SIGAN A ESTE POST/ USER
		$data = DB::fetchAll("SELECT f_user FROM u_follows WHERE f_id = :fid AND f_type = :type", ['fid' => $fId, 'type' => $fType]);
		//
		foreach($data as $key => $val) {
			// A CADA USUARIO LE NOTIFICAMOS SI NO ESTA EN LAS EXCLUSIONES
			if(!in_array($val['f_user'],$excluir)){
				$this->setNotificacion($notType, (int)$val['f_user'], $userId, $objUno, $objDos);
			}
		}
		//
		return true;
	}

	/**
	 * @name setMuroRepost
	 * @access public
	 * @params int
	 * @return void
	 * @info NOTIFICA CUANDO ALGUIEN RESPONDE UNA PUBLICACION EN UN MURO
	 */
	public function setMuroRepost(int $pubId, int $pUser, int $pUserPub): void {
		$data = DB::fetchAll("SELECT c_user FROM u_muro_comentarios WHERE pub_id = :pid AND c_user NOT IN (:uid, :puser)", ['pid' => $pubId, 'uid' => $this->User->uid, 'puser' => $pUser]);
		// ENVIAMOS NOTIFICACION A LOS QUE HAYAN COMENTADO
		$enviados = [];
		foreach($data as $key => $val){
			if(!in_array($val['c_user'], $enviados)) {
				$this->setNotificacion(13, (int)$val['c_user'], $this->User->uid, $pubId, 3);
				$enviados[] = $val['c_user'];
			}
		}
		// ENVIAMOS AL DUE�O DEL MURO
		$this->setNotificacion(13, (int)$pUser, $this->User->uid, $pubId, 1);
		// ENVIAMOS AL QUE PUBLICO SI NO FUE EL DUE�O DEL MURO
		if(($pUser !== $pUserPub) && !in_array($pUserPub, $enviados)){
			$this->setNotificacion(13, (int)$pUserPub, $this->User->uid, $pubId, 2);    
		}
	}

	/**
	 * @name getNotificaciones
	 * @access public
	 * @param int
	 * @return array
	 * @info CREAR UN ARRAY CON LAS NOTIFICAIONES DEL USUARIO
	 */
	public function getNotificaciones(bool $unread = false): array {
	  	$dataDos = [];
	  	$uid     = $this->User->uid;

	  	if ($this->show_type === 1) {
	  	   $notView   = $unread ? '= 2' : '> 0';
	  	   $notDel    = $unread ? 1 : 0;
	  	   $showAll   = ($this->notificaciones > 5 || $unread);
	  	   $sqlFilter = $showAll ? " AND m.not_menubar {$notView}" : '';
	  	   $sqlLimit  = $showAll ? '' : 'LIMIT 5';
	  	} else {
	      $sqlFilter = '';
	      $sqlLimit  = '';
	      $dataDos['stats'] = [
	         'posts' => DB::numRows("SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_type = 3", ['uid' => $uid]),
	         'seguidores' => DB::numRows("SELECT follow_id FROM u_follows WHERE f_id = :uid AND f_type = 1",   ['uid' => $uid]),
	         'siguiendo' => DB::numRows("SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_type = 1", ['uid' => $uid]),
	      ];
	      $filtros = explode(',', DB::value("SELECT c_monitor FROM u_portal WHERE user_id = :uid LIMIT 1", ['uid' => $uid]) ?? '');
	      foreach ($filtros as $val) $dataDos['filtro'][$val] = true;
	   }
	   $data = DB::fetchAll("SELECT m.*, u.user_name AS usuario FROM u_monitor AS m LEFT JOIN u_miembros AS u ON m.obj_user = u.user_id WHERE m.user_id = :uid {$sqlFilter} ORDER BY m.not_id DESC {$sqlLimit}", ['uid' => $uid]);

	   if ($this->show_type === 1) {
	      DB::raw("UPDATE u_monitor SET not_menubar = :del WHERE user_id = :uid AND not_menubar > 0", ['del' => $notDel, 'uid' => $uid]);
	   } else {
	      DB::raw("UPDATE u_monitor SET not_menubar = 0, not_monitor = 0 WHERE user_id = :uid AND not_monitor = 1", ['uid' => $uid]);
	   }
	   $dataDos['data']  = $this->armNotificaciones($data);
	   $dataDos['total'] = count($dataDos['data']);
	   return $dataDos;
	}

	/**
	 * @name armarNotificacion
	 * @access private
	 * @param array
	 * @return array
	*/
	private function armNotificaciones(array $items): array {
	   $this->makeMonitor();
	   $result = [];
	   foreach ($items as $item) {
	      $queryOrData = $this->makeConsulta($item);
	      $dato = is_array($queryOrData) ? $queryOrData : DB::fetch($queryOrData, ['obj' => (int)$item['obj_uno']]);
	      if (empty($dato)) continue;
	      $result[] = $this->makeOracion(array_merge($dato, $item));
	   }
	   return $result;
	}

	/**
	 * @name makeConsulta
	 * @access private
	 * @param array
	 * @return string
	*/
	private function makeConsulta(array $data): array|string {
	   $objUno = (int)$data['obj_uno'];
	   $objDos = (int)$data['obj_dos'];

	   return match((int)$data['not_type']) {
	      1, 2, 3, 5, 6, 7, 8, 9 => "SELECT p.post_id, p.post_user, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_id = :obj LIMIT 1",
	      10, 11 => "SELECT f.foto_id, f.f_title, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :obj LIMIT 1",
	      4 => ['follow' => $this->User->iFollow((int)$data['obj_user'])],
	      12 => "SELECT p.pub_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.pub_id = :obj LIMIT 1",
	      13 => $this->makeConsultaMuro($data),
	      14 => ($objDos !== 2) ? ['value' => 'hack'] : "SELECT pub_id AS obj_uno, c_body FROM u_muro_comentarios WHERE cid = :obj",
	      15 => "SELECT medal_id, m_title, m_image FROM w_medallas WHERE medal_id = :obj LIMIT 1",
	      16 => "SELECT p.post_id, p.post_title, c.c_seo, m.medal_id, m.m_title, m.m_image FROM w_medallas_assign AS a LEFT JOIN p_posts AS p ON p.post_id = a.medal_for LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id WHERE m.medal_id = :obj AND p.post_id = {$objDos} LIMIT 1",
	      17 => "SELECT f.foto_id, f.f_title, f.f_user, m.medal_id, m.m_title, m.m_image, u.user_id, u.user_name FROM w_medallas_assign AS a LEFT JOIN f_fotos AS f ON f.foto_id = a.medal_for LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id WHERE m.medal_id = :obj AND f.foto_id = {$objDos} LIMIT 1",
	      18 => "SELECT r_name FROM u_rangos WHERE rango_id = :obj LIMIT 1",
	      default => throw new RuntimeException("Tipo de notificaci�n inv�lido: {$data['not_type']}")
	   };
	}

	private function makeConsultaMuro(array $data): array {
    	$dato = DB::fetch("SELECT p.pub_id, p.p_user, p.p_user_pub, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE p.pub_id = :obj LIMIT 1", ['obj' => (int)$data['obj_uno']]);
    	$dato['p_user_resp'] = $data['obj_user'];
    	$dato['p_user_name'] = $dato['user_name'];
    	$dato['user_name']   = $this->User->getUserName((int)$data['obj_user']);
    	return $dato;
}

	private function baseOracion(array $data, int $noType): array {
		$avatar = Container::get(Avatar::class)->get((int)$data['obj_user']);
		return [
			'unread' => $data[$this->show_type === 1 ? 'not_menubar' : 'not_monitor'],
			'style'  => $this->monitor[$noType]['css'],
			'date'   => $data['not_date'],
			'user'   => $data['usuario'],
			'avatar' => $avatar,
			'total'  => (int)$data['not_total'],
		];
	}

	private function makeUrlOracion(string $type, array|string $data, string $anchor = ''): string {
		$base = rtrim($this->Core->settings['url'], '/');
		$anchor = $anchor ? "/{$anchor}" : '';

		return match ($type) {
			'post' => $this->UrlHelper->buildPostUrl($data, $anchor),
			'foto' => $this->UrlHelper->buildFotoUrl($data, $anchor),
			'perfil' => "{$base}/@{$data}{$anchor}",
			default => $base,
		};
	}

	private function highlight(string $value, string $text, string $placeholder = '@FLAG'): string {
		return str_replace($placeholder , "<strong>{$value}</strong>", $text);
	}

	/**
	 * @name makeOracion
	 * @access private
	 * @param array, int, int
	 * @return array
	 * @info RETORNA LAS ORACIONES A MOSTRAR EN EL MONITOR
	*/
	private function makeOracion(array $data): array {
		$showType  = ($this->show_type === 1);
		$noType    = (int)$data['not_type'];
		if (!isset($this->monitor[$noType])) {
			throw new RuntimeException("Tipo de monitor inv�lido: {$noType}");
		}
		$monitor = $this->monitor[$noType] ?? [];
		$lnText = $monitor['ln_text'] ?? null;
		if (is_array($lnText)) {
			$lnText = $lnText[$data['obj_dos'] - 1] ?? null;
		}
		$txt_extra = ($showType || !$lnText) ? '' : " {$lnText}";
		$message   = $lnText;
		# LOCALES
		$url 	   = $this->Core->settings['url'];
		$urlImages = Container::get(Routes::class)->route('assets:images');
		//
		$oracion = $this->baseOracion($data, $noType);
		# CON UN SWITCH ESCOGEMOS QUE ORACION CONSTRUIR
		switch($noType){
			case 1:
			case 3:
			case 5:
				// $this->buildPostAction($oracion, $data, $noType, $message, $txt_extra);
				// 
				$oracion['text'] = $this->monitor[$noType]['text'].$txt_extra;
				if($noType === 3) {
					$oracion['text'] = $this->highlight($data['obj_dos'], $oracion['text']);
				}
				$oracion['link'] = $this->makeUrlOracion('post', $data);
				$oracion['ltext'] = $showType ? $message : $data['post_title'];
				$oracion['ltit'] = $showType ? $data['post_title'] : '';
			break;
			// FOLLOW
			case 4:
				$oracion['text'] = $this->monitor[$noType]['text'];
				if($data['follow'] !== true && $this->show_type === 2) {
					$oracion['link'] = "#\" onclick=\"notifica.follow('user', {$data['obj_user']}, notifica.userInMonitorHandle, this)";
					$oracion['ltext'] = $this->monitor[$noType]['ln_text'];    
				}
			break;
			// PUEDEN SER MAS DE UNO
			case 2:
			case 6:
			case 7:
			case 8:
			case 9:
				// $this->buildMultiAction($oracion, $data, $noType, $message, $txt_extra);
				// CUANTOS
				$no_total = (int)$data['not_total'];
				$idComment = '';
				// MAS DE UNA ACCION
				if($no_total > 1) {
					$text = $this->monitor[$noType]['text'][1].$txt_extra;
					$oracion['text'] = $this->highlight($no_total, $text);
				} else $oracion['text'] = $this->monitor[$noType]['text'][0].$txt_extra;
				// �ES MI POST?
				if((int)$data['post_user'] === $this->User->uid) {
					$find = 'te recomienda un';
					$oracion['text'] = $this->highlight('ha recomendado tu', $oracion['text'], $find);
				}
				// ID COMMENT
				if($noType === 8 || $noType === 9){
					$idComment = '#comentario-' . $data['obj_dos'];
					// EXTRAS
					if($noType === 8) {
						$voto_type = ($data['obj_tres'] === 0) ? 'negativo' : 'positivo';
						$oracion['text'] = $this->highlight($voto_type, $oracion['text']);
						$oracion['style'] = 'voto_'.$voto_type;
					}
				}
				//
				$oracion['link'] = $this->makeUrlOracion('post', $data, $idComment);
				$oracion['ltext'] = $showType ? $message : $data['post_title'];
				$oracion['ltit'] = $showType ? $data['post_title'] : '';
			break;
			// PUBLICACION EN MURO
			// 12|13|14 $this->buildProfileAction($oracion, $data, $noType, $message, $txt_extra);
			case 12:
				$oracion['text'] = $this->monitor[$noType]['text'].$txt_extra;
				$oracion['link'] = $this->makeUrlOracion('perfil', $this->User->nick, $data['obj_uno']);
				$oracion['ltext'] = $showType ? $message : $this->User->nick;
				$oracion['ltit'] = $showType ? $this->User->nick : '';
			break;
			case 13:
				// DE QUIEN?
				$de = match(true) {
					($this->User->uid === (int)$data['p_user']) => ' tu',
					((int)$data['p_user'] === (int)$data['p_user_resp']) => ' su',
					default => ' la publicaci�n de'
				};
				// CUANTOS
				$no_total = (int)$data['not_total'];
				if($no_total > 1) {
					$text = $this->monitor[$noType]['text'][1].$de.$txt_extra;
					$oracion['text'] = $this->highlight($no_total, $text);
				}
				else $oracion['text'] = $this->monitor[$noType]['text'][0].$de.$txt_extra;
				//
				$oracion['link'] = $this->makeUrlOracion('perfil', $data['p_user_name'], $data['pub_id']);
				$oracion['ltext'] = $showType ? $message : $this->User->nick;
				$oracion['ltit'] = $showType ? $this->User->nick : '';
			break;
			case 14:
				// CUANTOS
				$no_total = (int)$data['not_total'];
				// MAS DE UNA ACCION
				if($no_total > 1) {
					$text = $this->monitor[$noType]['text'][1].' '.$message;
					$oracion['text'] = $this->highlight($no_total, $text);
				} else $oracion['text'] = $this->monitor[$noType]['text'][0];
				//
				$oracion['text'] = $showType ? $oracion['text'] : $oracion['text'].' '.$message;
				$oracion['link'] = $this->makeUrlOracion('perfil', $this->User->nick, $data['obj_uno']);
				$oracion['ltext'] = $showType ? $message : substr($data['c_body'],0,20).'...';
				$oracion['ltit'] = $showType ? substr($data['c_body'],0,20).'...' : '';
			break;
			// 15|16|17 $this->buildMedalAction($oracion, $data, $noType);
			case 15:
				$oracion['text'] = "Recibiste una nueva <span title=\"{$data['m_title']}\"><strong>medalla</strong> <img class=\"item-image\" src=\"{$urlImages}/icons/medals/{$data['m_image']}_32.png\"/></span>";
			break;
			case 16:
				$urlPost = $this->makeUrlOracion('post', $data);
				$oracion['text'] = "Tu <a href=\"{$urlPost}\" title=\"{$data['post_title']}\"><strong>post</strong></a> tiene una nueva <span title=\"{$data['m_title']}\"><strong>medalla</strong> <img class=\"item-image\" src=\"{$urlImages}/icons/medals/{$data['m_image']}_32.png\"/></span>";
			break;
			case 17:
				$urlFoto = $this->makeUrlOracion('foto', $data);
				$oracion['text'] = "Tu <a href=\"{$urlFoto}\" title=\"{$data['f_title']}\"><strong>foto</strong></a> tiene una nueva <span title=\"{$data['m_title']}\"><strong>medalla</strong> <img class=\"item-image\" src=\"{$urlImages}/icons/medals/{$data['m_image']}_32.png\"/></span>";
			break;
		}
		# RETORNAMOS
		return $oracion;
	}

	private function resolveFollowContext(): array {
		$typeString = trim($_POST['type'] ?? '');
		$objectId   = (int) Html::escape($_POST['obj']);
		$typeData = match ($typeString) {
			'user' => ['type' => 1, 'notifyUser' => $objectId],
			'post' => ['type' => 2, 'notifyUser' => 0],
			default => ['type' => 0, 'notifyUser' => 0],
		};

		return [
			'objectId'   => $objectId,
			'type'       => $typeData['type'],
			'notifyUser' => $typeData['notifyUser'],
			'notType'    => 4,
		];
	}

	private function checkFollowFlood(int $objectId): ?string {
		$flood = $this->User->antiFlood(false, 'follow');
		if (strlen((string)$flood) <= 1) {
			return null;
		}
		$message = str_replace('0: ', '', $flood);
		return "1-{$objectId}-0-{$message}";
	}

	private function followExists(array $ctx): bool {
	   $row = DB::fetch("SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_id = :obj AND f_type = :type LIMIT 1", ['uid' => $this->User->uid, 'obj' => $ctx['objectId'], 'type' => $ctx['type']]);
	   return !empty($row['follow_id']);
	}

	private function insertFollow(array $ctx): bool {
	   return (bool) DB::insert('u_follows', [
	   	'f_user' => $this->User->uid, 
	   	'f_id' => $ctx['objectId'], 
	   	'f_type' => $ctx['type'], 
	   	'f_date' => time()
	   ]);
	}

	private function countFollows(array $ctx): int {
	   $row = DB::fetch("SELECT COUNT(follow_id) AS total FROM u_follows WHERE f_id = :object AND f_type = :type", ['object' => $ctx['objectId'], 'type' => $ctx['type']]);
	   return (int) $row['total'];
	}

	/**
	 * @name setFollow
	 * @access public
	 * @param none
	 * @return string
	 * @info MANEJA EL SEGUIR USUARIO/POST
	*/
	public function setFollow(): string {
		global $tsActividad;
		// objectId = user_id, post_id, etc
		$ctx = $this->resolveFollowContext();
		if ($error = $this->checkFollowFlood($ctx['objectId'])) {
			return $error;
		}
		if ($this->User->uid === $ctx['objectId'] && $ctx['type'] === 1) {
			return "0-{$ctx['objectId']}-0-No puedes seguirte a ti mismo.";
		}
		if ($this->followExists($ctx)) {
			return "2-{$ctx['objectId']}-0-Ya lo est�s siguiendo.";
		}
		if (!$this->insertFollow($ctx)) {
			return "0-{$ctx['objectId']}-0-No se pudo completar la acci�n.";
		}
		if ($ctx['notifyUser'] > 0) {
			$this->setNotificacion((int)$ctx['notType'], (int)$ctx['notifyUser'], $this->User->uid);
		}
		$total = $this->countFollows($ctx);
		$acType = ($ctx['type'] === 1) ? 8 : 7;
		$tsActividad->setActividad($acType, (int)$ctx['objectId']);
		return "1-{$ctx['objectId']}-{$total}";
	}

	/**
	 * @name setUnFollow
	 * @access public
	 * @param none
	 * @return string
	 * @info MANEJA EL DEJAR DE SEGUIR UN USUARIO/POST
	*/
	public function setUnFollow(): string {
		$ctx = $this->resolveFollowContext();
		if (!DB::delete('u_follows', 'f_user = :uid AND f_id = :obj AND f_type = :type', ['uid' => $this->User->uid, 'obj' => $ctx['objectId'], 'type' => $ctx['type']])) {
			return "1-{$ctx['objectId']}-0-No se pudo completar la acci�n.";
		}
		$total = $this->countFollows($ctx);
		return "0-{$ctx['objectId']}-{$total}";
	}

	/**
	 * @name getFollowVars
	 * @access private
	 * @param none
	 * @return array
	 * @info GENERA Y CREA UN ARRAY CON LA INFORMACION QUE RESIBE POR AJAX
	*/
	private function getFollowVars(): array {
		$return['sType'] = trim($_POST['type'] ?? '');
		$return['obj'] = Html::escape($_POST['obj']);
		// TIPO EN NUMERO
		return match($return['sType']) {
			'user' 	=> ['type' => 1, 'notUser' => $return['obj']],
			'post' 	=> ['type' => 2, 'notUser' => 0],
			default	=> ['type' => 0, 'notUser' => 0]
		};
	}

	/**
	 * @name getFollows
	 * @access public
	 * @param int
	 * @return array
	 * @info CARGA EN UN ARRAY LA INFORMACION DE LOS "FOLLOWs" DE UN USUARIO
	*/
	public function getFollows(string $type, int $userId = 0): array {
	   $userId = $userId ?: $this->User->uid;

	   $query = match($type) {
	      'seguidores' => "SELECT u.user_id, u.user_name, p.user_pais, p.p_mensaje, f.follow_id FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id LEFT JOIN u_follows AS f ON p.user_id = f.f_user WHERE f.f_id = :uid AND f.f_type = 1 ORDER BY f.f_date DESC",
	      'siguiendo' => "SELECT u.user_id, u.user_name, p.user_pais, p.p_mensaje, f.follow_id FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id LEFT JOIN u_follows AS f ON p.user_id = f.f_id WHERE f.f_user = :uid AND f.f_type = 1 ORDER BY f.f_date DESC",
	      'posts' => "SELECT f.f_id, p.post_user, p.post_title, u.user_name, c.c_seo, c.c_nombre, c.c_img FROM u_follows AS f LEFT JOIN p_posts AS p ON f.f_id = p.post_id LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE f.f_user = :uid AND f.f_type = 2 ORDER BY f.f_date DESC",
	      default => throw new InvalidArgumentException("Tipo de follow inv�lido: {$type}")
	   };
	   $total = DB::numRows($query, ['uid' => $userId]);
	   $pages = $this->Paginator->getPagination($total, 12);
	   $dato  = DB::fetchAll("$query LIMIT {$pages['limit']}", ['uid' => $userId]);

	   if ($type === 'seguidores') {
	      foreach ($dato as &$val) {
	         $siguiendo = DB::fetch("SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_id = :fid AND f_type = 1", ['uid' => $userId, 'fid' => $val['user_id']]);
	         $val['follow'] = empty($siguiendo['follow_id']) ? 0 : 1;
	      }
	      unset($val);
	   }
	   return [
	      'pages' => $pages,
	      'data'  => $dato,
	   ];
	}

	/**
	 * @name setSpam
	 * @access public
	 * @param none
	 * @return string
	 * @info ESTA FUNCION ES PARA REALIZAR RECOMENDACIONES
	*/
	public function setSpam(): string {
		global $tsActividad;
		$postId = (int)($_POST['postid'] ?? 0);

	   $seguidores  = DB::numRows("SELECT follow_id FROM u_follows WHERE f_id = :uid AND f_type = 1 LIMIT 1", ['uid' => $this->User->uid]);
	   $recomendado = DB::numRows("SELECT follow_id FROM u_follows WHERE f_id = :pid AND f_user = :uid AND f_type = 3 LIMIT 1", ['pid' => $postId, 'uid' => $this->User->uid]);

	   if ($seguidores < 1)  return '0-Debes tener al menos un seguidor';
	   if ($recomendado > 0) return '0-No puedes recomendar el mismo post m�s de una vez.';

	   $data = DB::fetch("SELECT post_user FROM p_posts WHERE post_id = :pid LIMIT 1", ['pid' => $postId]);
	   if ((int)$data['post_user'] === $this->User->uid) return '0-No puedes recomendar tus posts.';

	   DB::insert('u_follows', ['f_id' => $postId, 'f_user' => $this->User->uid, 'f_type' => 3, 'f_date' => time()]);
	   $this->setFollowNotificacion(6, 1, $this->User->uid, $postId);
	   $tsActividad->setActividad(4, $postId);
	   return '1-La recomendaci�n fue enviada.';
	}
	
	/**
	 * @name setFiltro
	 * @access public
	 * @param none
	 * @return bool
	 * @info GUARDA LOS FILTROS DE LA ACTIVIDAD
	 */
	public function setFiltro(): bool {
		$fid = array_map(fn($v) => 'f' . (int)$v, $_POST['fid'] ?? []);
   	DB::update('u_portal', ['c_monitor' => implode(',', $fid)], 'user_id = :uid', ['uid' => $this->User->uid]);
   	return true;
	}
	
	/**
	 * @name allowNotifi
	 * @access private
	 * @param int
	 * @return bool
	 * @info REVISA EN LA CONFIGURACION SI DESEA RESIBIR LA NOTIFICACION
	 */
	private function allowNotifi(int $type, int $userId): bool {
		$config  = DB::value("SELECT c_monitor FROM u_portal WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
    	$filtros = explode(',', $config ?? '');
    	return in_array("f{$type}", $filtros);
	}
}
