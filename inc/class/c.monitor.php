<?php

/**
 * @name c.monitor.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_UTILS . '/Avatar.php';
require_once TS_HELPERS . '/UrlHelper.php';

class tsMonitor {
	
	protected tsCore $Core;
	protected tsUser $User;
	protected Avatar $Avatar;
	protected UrlHelper $UrlHelper;

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
	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		$this->Avatar = new Avatar;
		$this->UrlHelper = new UrlHelper($Core);
		// VISITANTE?
		if($this->User->is_member === 0) return false;
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
			1 => ['text' => 'agreg&oacute; a favoritos tu', 'ln_text' => 'post', 'css' => 'star'],
			2 => ['text' => ['coment&oacute; tu','@FLAG nuevos comentarios en tu'], 'ln_text' => 'post', 'css' => 'comment_post'],
			3 => ['text' => 'dej&oacute; @FLAG puntos en tu', 'ln_text' => 'post', 'css' => 'points'],
			4 => ['text' => 'te est&aacute; siguiendo', 'ln_text' => 'Seguir a este usuario', 'css' => 'follow'],
			5 => ['text' => 'cre&oacute; un nuevo', 'ln_text' => 'post', 'css' => 'post'],
			6 => ['text' => ['te recomienda un', '@FLAG usuarios te recomiendan un'], 'ln_text' => 'post', 'css' => 'share'],
			7 => ['text' => ['coment&oacute; en un', '@FLAG nuevos comentarios en el'], 'ln_text' => 'post', 'extra' => 'que sigues', 'css' => 'blue_ball'],
			8 => ['text' => ['vot&oacute; @FLAG tu', '@FLAG nuevos votos a tu'], 'ln_text' => 'comentario', 'css' => 'voto_'],
			9 => ['text' => ['respondi&oacute; tu', '@FLAG nuevas respuestas a tu'], 'ln_text' => 'comentario', 'css' => 'comment_resp'],
			10 => ['text' => 'subi&oacute; una nueva', 'ln_text' => 'foto', 'css' => 'photo'],
			11 => ['text' => ['coment&oacute; tu','@FLAG nuevos comentarios en tu'], 'ln_text' => 'foto', 'css' => 'photo'],
			12 => ['text' => 'public&oacute; en tu', 'ln_text' => 'muro', 'css' => 'wall_post'],
			13 => ['text' => ['coment&oacute; ', '@FLAG nuevos comentarios en'], 'ln_text' => 'publicaci&oacute;n', 'extra' => 'coment&oacute;', 'css' => 'w_comment'],
			14 => ['text' => ['le gusta tu', 'A @FLAG personas les gusta tu'], 'ln_text' => ['publicaci&oacute;n','comentario'], 'css' => 'w_like'],
			15 => ['text' => 'Recibiste una medalla', 'css' => 'medal'],
			16 => ['text' => 'Tu post recibi&oacute; una medalla', 'css' => 'medal'],
			17 => ['text' => 'Tu foto recibi&oacute; una medalla', 'css' => 'medal'],
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
		# VERIFICAMOS QUE SE PUEDA ENVIAR EL AVISO
		$data = DB::fetch("SELECT user_baneado FROM u_miembros WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
		# NO PODEMOS ENVIAR A UN USUARIO BANEADO
		if((int)$data['user_baneado'] === 1) return true;
		# INSERTAMOS EL AVISO
		$subject = $this->Core->setSecure($subject);
		$body = $this->Core->setSecure($body);
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

	private function getAviso(int $avId, bool $isString = true): array|string|bool {
		# OBTENEMOS
		$data = DB::fetch('SELECT av_id, user_id FROM u_avisos WHERE av_id = :aid', ['aid' => $avId]);
		# RETURN
		if(empty($data['av_id']) || (int)$data['user_id'] !== $this->User->uid && !$this->User->is_admod) {
			return $isString ? 'El aviso no existe' : false;
		}
		return $data;
	}

	/**
	 * @name readAviso
	 * @access public
	 * @param int
	 * @return array
	 * @info ONTIENE UN AVISO
	 */
	public function readAviso(int $avId = 0): array|string {
		$data = $this->getAviso($avId);
		DB::update('u_avisos', ['av_read' => 1], 'av_id = :id', ['id' => $avId]);
		$this->avisos = $this->avisos - 1;
		return $data; 
	}

	/**
	 * @name delAviso
	 * @access public
	 * @param int
	 * @return bool
	 * @info ELIMINA UN AVISO
	 */
	public function delAviso(int $avId = 0): bool {
		$this->getAviso($avId, false);
		DB::delete('u_avisos', 'av_id = :aid', ['aid' => $avId]);
		return true;
	}

	/**
	 * @name setNotificacion
	 * @access public
	 * @param int $type       Tipo de notificación
	 * @param int $userId     Usuario que recibe
	 * @param int $objUser    Usuario origen
	 * @param int $objUno     ID del objeto principal (post, comment, etc.)
	 * @param int $objDos     ID secundario (opcional)
	 * @param int $objTres    ID terciario (opcional)
	 * @return void
	 */
	public function setNotificacion(int $type, int $userId, int $objUser, int $objUno = 0, int $objDos = 0, int $objTres = 0) {
		$time = time();
		# NO SE MOSTRARA MI PROPIA ACTIVIDAD
		if($userId !== $this->User->uid) {
			# VERIFICA SI ESTE USUARIO ADMITE NOTIFICACIONES DEL TIPO $type
			$allow = $this->allowNotifi((int)$type, (int)$userId);
			if(empty($allow)) return true;
			// VERIFICAR CUANTAS NOTIFICACIONES DEL MISMO TIPO Y EN POCO TIEMPO TENEMOS
			$tiempo = $time - 3600; //  HACE UNA HORA
			$not_data = DB::fetch("SELECT not_id FROM u_monitor WHERE user_id = :uid AND obj_uno = :ouno AND obj_dos = :odos AND not_type = :type AND not_date > :time AND not_menubar > 0 ORDER BY not_id DESC LIMIT 1", ['uid' => $userId, 'ouno' => $objUno, 'odos' => $objDos, 'type' => $type, 'time' => $tiempo]);
			// COMPROBAR LIMITE DE NOTIFICACIONES
			$data = DB::fetchAll("SELECT not_id FROM u_monitor WHERE user_id = :uid ORDER BY not_id DESC", ['uid' => $userId]);
			$ntotal = count($data ?? 0);
			$delid = (int)($data[(int)$ntotal - 1]['not_id']); // ID DE ULTIMA NOTIFICACION
			// ELIMINAR NOTIFICACIONES?
			$max = (int)$this->Core->settings['c_max_nots'];
			if($ntotal > $max) {
				DB::delete('u_monitor', "user_id = :uid ORDER BY not_id ASC LIMIT 1 OFFSET :max", ['uid' => $userId, 'max' => $max]);
			}
			// ACTUALIZAMOS / INSERTAMOS
			if(!empty($not_data['not_id']) && $type !== self::NOTIF_FOLLOW) {
				if(DB::raw("UPDATE u_monitor SET obj_user = :user, not_date = :time, not_total = not_total + 1 WHERE not_id = :nid", [
					'user' => $objUser,
					'time' => $time,
					'nid' => $not_data['not_id']
				]))
				return true;
			} else {
				if(DB::insert('u_monitor', [
					'user_id' => $userId,
					'obj_user' => $objUser,
					'obj_uno' => $objUno,
					'obj_dos' => $objDos,
					'obj_tres' => $objTres,
					'not_type' => $type,
					'not_date' => $time
				]))
				return true;   
			}
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
		$fType = match($fType) {
			1 => $userId,
			2 => $objUno,
			default => $fType
		};
		# BUSCAMOS LOS Q SIGAN A ESTE POST/ USER
		$data = DB::fetchAll("SELECT f_user FROM u_follows WHERE f_id = :fid AND f_type = :type", ['fid' => $fType, 'type' => $fType]);
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
		// ENVIAMOS AL DUEÑO DEL MURO
		$this->setNotificacion(13, (int)$pUser, $this->User->uid, $pubId, 1);
		// ENVIAMOS AL QUE PUBLICO SI NO FUE EL DUEÑO DEL MURO
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
	public function getNotificaciones(bool $unread = false) {
		# SI HAY MAS DE 5 NOTIS MOSTRAMOS TODAS LAS NO LEIDAS
		$sql = "SELECT m.*, u.user_name AS usuario FROM u_monitor AS m LEFT JOIN u_miembros AS u ON m.obj_user = u.user_id WHERE m.user_id = {$this->User->uid}";
		if($this->show_type === 1) {
			// VIEW TYPE
			$notView = $unread ? '= 2' : ' > 0';
			$notDel = $unread ? 1 : 0;
			// CONSULTA
			$sql .= ((int)$this->notificaciones > 5 || $unread) ? " AND m.not_menubar $notView ORDER BY m.not_id DESC" : " ORDER BY m.not_id DESC LIMIT 5";
		// SI VA AL MONITOR ENTONCES ACTUALIZAMOS PARA QUE YA NO SE VEAN EN EL MENUBAR
		} elseif($this->show_type === 2) {
			// DATOS
			$sql .= ' ORDER BY m.not_id DESC';
			//ESTADÍSTICAS
			$dataDos['stats'] = [
				'posts' => db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_user = {$this->User->uid} AND f_type = 3")),
				'seguidores' => db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_id = {$this->User->uid} AND f_type = 1")),
				'siguiendo' => db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_user = {$this->User->uid} AND f_type = 1"))
			];
			# CARGO LOS FILTROS
			$filtros = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT c_monitor FROM u_portal WHERE user_id = {$this->User->uid} LIMIT 1'));
			$filtros = explode(',', $filtros['c_monitor']);
			foreach($filtros as $key => $val) $dataDos['filtro'][$val] = true;
		} 
		// PROCESOS
		$data = result_array(db_exec([__FILE__, __LINE__], 'query', $sql));
		// ACTUALIZAMOS
		if($this->show_type === 1) {
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_monitor SET not_menubar = $notDel WHERE user_id = {$this->User->uid} AND not_menubar > 0");
		} else {
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_monitor SET not_menubar = 0, not_monitor = 0 WHERE user_id = {$this->User->uid} AND not_monitor = 1");
		}
		// ARMAR TEXTOS Y LINKS :)
		$dataDos['data'] = $this->armNotificaciones($data);
		// TOTAL DE NOTIDICACIONES
		$dataDos['total'] = count($dataDos['data'] ?? 0);
		//
		return $dataDos;
	}

	/**
	 * @name resolveNotificationData
	 * @access private
	 * @param array
	 * @return array
	*/
	private function resolveNotificationData(array $item): ?array {
		$queryOrData = $this->makeConsulta($item);
		if (is_array($queryOrData)) {
			$data = $queryOrData;
		} else {
			$query = db_exec([__FILE__, __LINE__], 'query', $queryOrData);
			if (!$query) {
				return null;
			}
			$data = db_exec('fetch_assoc', $query);
		}
		if (!$data) {
			return null;
		}
		return array_merge($data, $item);
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
			$notificationData = $this->resolveNotificationData($item);
			if (!$notificationData) {
				continue;
			}
			$result[] = $this->makeOracion($notificationData);
		}
		return $result;
	}

	/**
	 * @name makeConsulta
	 * @access private
	 * @param array
	 * @return string
	*/
	public function makeConsulta(array $data) {
		# CON UN SWITCH ESCOGEMOS LA CONSULTA APROPIADA
		switch((int)$data['not_type']) {
			case 1: 
			case 2: 
			case 3: 
			case 5: 
			case 6:
			case 7:
			case 8:
			case 9:
				return "SELECT p.post_id, p.post_user, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_id = {$data['obj_uno']} LIMIT 1";
			break;
			// FOLLOW
			case 4:
				// CHECAR SI YA LO SEGUIMOS
				$i_follow = $this->User->iFollow($data['obj_user']);
				return array('follow' => $i_follow);
			break;
			// PUBLICO EN TU MURO
			case 12:
				return "SELECT p.pub_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.pub_id = {$data['obj_uno']} LIMIT 1";
			break;
			case 13:
				global $tsUser;
				// HAY MAS DE UNA NOTIFICACION DEL MISMO TIPO
				$query = db_exec([__FILE__, __LINE__], 'query', "SELECT p.pub_id, p.p_user, p.p_user_pub, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE p.pub_id = {$data['obj_uno']} LIMIT 1");
				$dato = db_exec('fetch_assoc', $query);
				//
				$dato['p_user_resp'] = $data['obj_user'];
				$dato['p_user_name'] = $dato['user_name']; // // DUEÑO DEL MURO
				$dato['user_name'] = $this->User->getUserName($data['obj_user']); // QUIEN PUBLICO
				//
				return $dato;
			break;
			case 14:
				if($data['obj_dos'] !== 2) return ['value' => 'hack'];
				return 'SELECT pub_id AS obj_uno, c_body FROM u_muro_comentarios WHERE cid = ' .$data['obj_uno'];
			break;
			case 15:
				return "SELECT medal_id, m_title, m_image FROM w_medallas WHERE medal_id = {$data['obj_uno']} LIMIT 1";
			break;
			case 16:
				return "SELECT p.post_id, p.post_title, c.c_seo, m.medal_id, m.m_title, m.m_image, a.medal_for FROM w_medallas_assign AS a LEFT JOIN p_posts AS p ON p.post_id = a.medal_for LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id WHERE m.medal_id = {$data['obj_uno']} AND p.post_id = {$data['obj_dos']} LIMIT 1'";
			break;
			case 17:
				return "SELECT f.foto_id, f.f_title, f.f_user, m.medal_id, m.m_title, m.m_image, a.medal_for, u.user_id, u.user_name FROM w_medallas_assign AS a LEFT JOIN f_fotos AS f ON f.foto_id = a.medal_for LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id WHERE m.medal_id = {$data['obj_uno']} AND f.foto_id = {$data['obj_dos']} LIMIT 1";
			break;
		}
	}

	private function baseOracion(array $data, int $noType): array {
		return [
			'unread' => $data[$this->show_type === 1 ? 'not_menubar' : 'not_monitor'],
			'style'  => $this->monitor[$noType]['css'],
			'date'   => $data['not_date'],
			'user'   => $data['usuario'],
			'avatar' => $this->Avatar->get((int)$data['obj_user']),
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
			throw new RuntimeException("Tipo de monitor inválido: {$noType}");
		}
		$monitor = $this->monitor[$noType] ?? [];
		$lnText = $monitor['ln_text'] ?? null;
		if (is_array($lnText)) {
			$lnText = $lnText[$data['obj_dos'] - 1] ?? null;
		}
		$txt_extra = ($showType || !$lnText) ? '' : " {$lnText}";
		$message   = $lnText;
		# LOCALES
		$url 		  = $this->Core->settings['url'];
		$urlImages = $this->Core->route('assets:images');
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
				// ¿ES MI POST?
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
					default => ' la publicaci&oacute;n de'
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
				$oracion['text'] = "Recibiste una nueva <span title=\"{$data['m_title']}\"><strong>medalla</strong> <img class=\"item-image\" src=\"{$urlImages}/icons/med/{$data['m_image']}_32.png\"/></span>";
			break;
			case 16:
				$urlPost = $this->makeUrlOracion('post', $data);
				$oracion['text'] = "Tu <a href=\"{$urlPost}\" title=\"{$data['post_title']}\"><strong>post</strong></a> tiene una nueva <span title=\"{$data['m_title']}\"><strong>medalla</strong> <img class=\"item-image\" src=\"{$urlImages}/icons/med/{$data['m_image']}_32.png\"/></span>";
			break;
			case 17:
				$urlFoto = $this->makeUrlOracion('foto', $data);
				$oracion['text'] = "Tu <a href=\"{$urlFoto}\" title=\"{$data['f_title']}\"><strong>foto</strong></a> tiene una nueva <span title=\"{$data['m_title']}\"><strong>medalla</strong> <img class=\"item-image\" src=\"{$urlImages}/icons/med/{$data['m_image']}_32.png\"/></span>";
			break;
		}
		# RETORNAMOS
		return $oracion;
	}

	private function resolveFollowContext(): array {
		$typeString = trim($_POST['type'] ?? '');
		$objectId   = (int) $this->Core->setSecure($_POST['obj']);
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
		$flood = $this->Core->antiFlood(false, 'follow');
		if (strlen((string)$flood) <= 1) {
			return null;
		}
		$message = str_replace('0: ', '', $flood);
		return "1-{$objectId}-0-{$message}";
	}

	private function followExists(array $ctx): bool {
	   $row = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_user = {$this->User->uid} AND f_id = {$ctx['objectId']} AND f_type = {$ctx['type']} LIMIT 1"));
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
	public function setFollow() {
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
			return "2-{$ctx['objectId']}-0-Ya lo est&aacute;s siguiendo.";
		}
		if (!$this->insertFollow($ctx)) {
			return "0-{$ctx['objectId']}-0-No se pudo completar la acci&oacute;n.";
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
		if (!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM u_follows WHERE f_user = {$this->User->uid} AND f_id = {$ctx['objectId']} AND f_type = {$ctx['type']}")) {
			return "1-{$ctx['objectId']}-0-No se pudo completar la acci&oacute;n.";
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
		$return['obj'] = $this->Core->setSecure($_POST['obj']);
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
	public function getFollows(string $type, int $userId = 0) {
		$Paginator = new Paginator;
		$userId = (int)($userId ?? $this->User->uid);
		//
		$query = match($type) {
			'seguidores' => "SELECT u.user_id, u.user_name, p.user_pais, p.p_mensaje, f.follow_id FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id LEFT JOIN u_follows AS f ON p.user_id = f.f_user WHERE f.f_id = $userId AND f.f_type = 1",
			'siguiendo' => "SELECT u.user_id, u.user_name, p.user_pais, p.p_mensaje, f.follow_id FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id LEFT JOIN u_follows AS f ON p.user_id = f.f_id WHERE f.f_user = $userId AND f.f_type = 1",
			'posts' => "SELECT f.f_id, p.post_user, p.post_title, u.user_name, c.c_seo, c.c_nombre, c.c_img FROM u_follows AS f LEFT JOIN p_posts AS p ON f.f_id = p.post_id LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE f.f_user = $userId AND f.f_type = 2",
			default => null
		};
		// PAGINAR
		$total = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', $query));
		$pages = $Paginator->getPagination($total, 12);
		$data['pages'] = $pages;
		$data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "{$query} ORDER BY f.f_date DESC LIMIT {$pages['limit']}"));
		if($type === 'seguidores') {
			foreach($data['data'] as $key => $val) {
				$siguiendo = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_user = $userId AND f_id = {$val['user_id']} AND f_type = 1"));
				$val['follow'] = empty($siguiendo['follow_id']) ? 0 : 1;
				$data['data'][] = $val;
			}
		}
		//
		return $data;
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
		$time = time();
		$postId = (int)($_POST['postid'] ?? 0);
		// TIENE SEGUIDORES?
		$seguidores = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_id = {$this->User->uid} AND f_type = 1 LIMIT 1"));
		// YA LO HA RECOMENDADO?
		$recomendado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM u_follows WHERE f_id = $postId AND f_user = {$this->User->uid} AND f_type = 3 LIMIT 1"));
		if($seguidores < 1) return '0-Debes tener al menos un seguidor';
		if($recomendado > 0) return '0-No puedes recomendar el mismo post m&aacute;s de una vez.'; 
		//
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_user FROM p_posts WHERE post_id = $postId LIMIT 1"));
		//
		if($this->User->uid === (int)$data['post_user']) {
			return '0-No puedes recomendar tus posts.';
		}
		// GUARDAMOS EN FOLLOWS PUES ES LA RECOMENDACION PARA SU SEGUIDORES! xD
		db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_follows (f_id, f_user, f_type, f_date) VALUES ($postId, {$this->User->uid}, 3, $time)");
		// NOTIFICAR
		if($this->setFollowNotificacion(6, 1, (int)$this->User->uid, (int)$postId)) {
			$tsActividad->setActividad(4, (int)$postId);
			return '1-La recomendaci&oacute;n fue enviada.';
		}
	}
	
	/**
	 * @name setFiltro
	 * @access public
	 * @param none
	 * @return bool
	 * @info GUARDA LOS FILTROS DE LA ACTIVIDAD
	 */
	public function setFiltro() {
		foreach ($_POST['fid'] as $key => $value) $fid[] = 'f'.$value;
		$filtros = join(',', $fid);
		# GUARDAR
		db_exec([__FILE__, __LINE__], 'query', "UPDATE u_portal SET c_monitor = '$filtros' WHERE user_id = {$this->User->uid}");
		return true;
	}
	
	/**
	 * @name allowNotifi
	 * @access private
	 * @param int
	 * @return bool
	 * @info REVISA EN LA CONFIGURACION SI DESEA RESIBIR LA NOTIFICACION
	 */
	private function allowNotifi(int $type, int $userId) {
		# CONSULTAMOS
		$data = DB::fetch("SELECT c_monitor FROM u_portal WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
		//var_dump($data);
		# PROSESAMOS
		$filtro = "f{$type}";
		$filtros = explode(',', $data['c_monitor']);
		# VERIFICAMOS
		return (is_array($filtros) AND in_array($filtro, $filtros)) ? false : true;
	}
}