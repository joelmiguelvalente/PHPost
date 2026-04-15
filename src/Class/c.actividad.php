<?php

/**
 * @name src/Class/c.actividad.php
 * @author PHPost Team
 * @copyright 2026
 */

declare (strict_types = 1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_HELPERS . '/UrlHelper.php';
require_once TS_UTILS . '/Avatar.php';

class tsActividad {

	private array $actividad = [];
   protected UrlHelper $UrlHelper;

   public function __construct(
      protected tsCore $Core,
      protected tsUser $User
   ) {
      $this->UrlHelper = new UrlHelper($Core);
   }

	/**
	 * @name makeActividad
	 * @access private
	 * @param none
	 * @return none
	 */
	private function makeActividad(): void {
		# ACTIVIDAD CON FORMATO | ID => array(TEXT, LINK, CSS_CLASS)
		$this->actividad = [
			// POSTS
			1  => ['text' => 'Cre&oacute; un nuevo post', 'css' => 'post'],
			2  => ['text' => 'Agreg&oacute; a favoritos el post', 'css' => 'star'],
			3  => ['text' => ['Dej&oacute;', 'puntos en el post'], 'css' => 'points'],
			4  => ['text' => 'Recomend&oacute; el post', 'css' => 'share'],
			5  => ['text' => ['Coment&oacute;', 'el post'], 'css' => 'comment_post'],
			6  => ['text' => ['Vot&oacute;', 'un comentario en el post'], 'css' => 'voto_'],
			7  => ['text' => 'Est&aacute; siguiendo el post', 'css' => 'follow_post'],
			// FOLLOWS
			8  => ['text' => 'Est&aacute; siguiendo a', 'css' => 'follow'],
			// FOTOS
			9  => ['text' => 'Subi&oacute; una nueva foto', 'css' => 'photo'],
			// MURO
			10 => [
				0 => ['text' => 'Public&oacute; en su', 'link' => 'muro', 'css' => 'status'],
				1 => ['text' => 'Coment&oacute; su', 'link' => 'publicaci&oacute;n', 'css' => 'w_comment'],
				2 => ['text' => 'Public&oacute; en el muro de', 'css' => 'wall_post'],
				3 => ['text' => 'Coment&oacute; la publicaci&oacute;n de', 'css' => 'w_comment'],
			],
			11 => ['text' => 'Le gusta', 'css' => 'w_like',
				0	=> ['text' => 'su', 'link' => 'publicaci&oacute;n'],
				1	=> ['text' => 'su comentario'],
				2	=> ['text' => 'la publicaci&oacute;n de'],
				3	=> ['text' => 'el comentario'],
			],
		];
	}

	/**
	 * @name setActividad
	 * @access public
	 * @param int(x3)
	 * @return bool
	 */
	public function setActividad(int $acType, int $objUno = 0, int $objDos = 0): bool {
		# VARIABLES LOCALES
		$acDate = time();
		# BUSCAMOS ACTIVIDADES
		$data = DB::fetchAll("SELECT `ac_id` FROM `u_actividad` WHERE user_id = :uid ORDER BY ac_date DESC", ['uid' => $this->User->uid]);
		//
		$ntotal = count($data ?? 1);
		// ID DE ULTIMA NOTIFICACION
		$delid = $data[(int) $ntotal - 1]['ac_id'] ?? null;
		// ELIMINAR ACTIVIDADES?
		if ($ntotal >= (int) $this->Core->settings['c_max_acts']) {
			DB::delete('u_actividad', 'ac_id = :acid', ['acid' => $delid]);
		}
		# SE HACE UN CONTEO PROGRESIVO SI HACE ESTA ACCION MAS DE 1 VEZ AL DIA
		if ($acType === 5) {
			$data = DB::fetch("SELECT `ac_id`, `ac_date` FROM `u_actividad` WHERE user_id = :uid AND obj_uno = :obj AND ac_type = :type LIMIT 1", ['uid' => $this->User->uid, 'obj' => $objUno, 'type' => $acType]);
			//
			if (!empty($data)) {
				$hace = $this->makeFecha((int) $data['ac_date']);
				if ($hace === 'today') {
					DB::increment('u_actividad', 'obj_dos', 'ac_id = :acid', ['acid' => $data['ac_id']]);
					return true;
				}
			}
		}
		# INSERCION DE DATOS
		if(DB::insert('u_actividad', [
			'user_id' => $this->User->uid,
			'obj_uno' => $objUno,
			'obj_dos' => $objDos,
			'ac_type' => $acType,
			'ac_date' => $acDate
		])) return true;
		return false;
	}

	/**
	 * @name getActividad
	 * @access public
	 * @param int(3)
	 * @return array
	 */
	public function getActividad(int $userId = 0, int $acType = 0, int $start = 0): array {
		# CREAR ACTIVIDAD
		$this->makeActividad();
		# VARIABLES LOCALES
		$param = ['uid' => $userId, 'start' => $start];
		$ac_type = ($acType !== 0) ? " AND ac_type = :type" : '';
		if($acType !== 0) {
			$param = [...$param, 'type' => $acType];
		}
		# CONSULTA
		$data = DB::fetchAll("SELECT ac_id, user_id, obj_uno, obj_dos, ac_type, ac_date FROM u_actividad WHERE user_id = :uid {$ac_type} ORDER BY ac_date DESC LIMIT :start, 25", $param);
		# ARMAR ACTIVIDAD
		$actividad = $this->armActividad($data);
		# RETORNAR ACTIVIDAD
		return $actividad;
	}

	/**
	 * @name getActividadFollows
	 * @access public
	 * @param int
	 * @return array|string
	 */
	public function getActividadFollows(int $start = 0): array | string {
		# CREAR ACTIVIDAD
		$this->makeActividad();
		// SOLO MOSTRAREMOS LAS ULTIMAS 100 ACTIVIDADES
		if ($start > 90) {
			return ['total' => '-1'];
		}
		// SEGUIDORES
		$follows = DB::fetchAll("SELECT f_id FROM u_follows WHERE f_user = :uid AND f_type = 1", ['uid' => $this->User->uid]);
		// ORDENAMOS
		foreach ($follows as $key => $val) {
			$amigos[] = "'{$val['f_id']}'";
		}
		// ME AGREGO A LA LISTA DE AMIGOS
		$amigos[] = $this->User->uid;
		// CONVERTIMOS EL ARRAY EN STRING
		$amigos = implode(', ', $amigos);
		// OBTENEMOS LAS ULTIMAS PUBLICACIONES
		$data = DB::fetchAll("SELECT a.*, u.user_name AS usuario FROM u_actividad AS a LEFT JOIN u_miembros AS u ON a.user_id = u.user_id WHERE a.user_id IN(:amigos) ORDER BY ac_date DESC LIMIT :start, 25", ['amigos' => $amigos, 'start' => $start]);
		# ARMAR ACTIVIDAD
		if (empty($data)) {
			return 'No hay actividad o no sigues a ning&uacute;n usuario.';
		}
		$actividad = $this->armActividad($data);
		# RETORNAR ACTIVIDAD
		return $actividad;
	}

	/**
	 * @name delActividad
	 * @access public
	 * @param none
	 * @return string
	 */
	public function delActividad(): string {
		$acId = (int) ($_POST['acid'] ?? 0);
		# CONSULTAS
		$data = DB::fetch("SELECT user_id FROM u_actividad WHERE ac_id = :acid LIMIT 1", ['acid' => $acId]);
		# COMPROBAMOS
		if ((int)$data['user_id'] === $this->User->uid) {
			if (DB::delete('u_actividad', 'ac_id = :acid', ['acid' => $acId])) {
				return '1: Actividad borrada';
			}
		}
		return '0: No puedes borrar esta actividad.';
	}

	/**
	 * @name armActividad
	 * @access private
	 * @params array
	 * @return array
	 */
	private function armActividad(array $data = []): array {
		# VARIABLES LOCALES
		$actividad = [
			'total' => count($data ?? 0),
			'data'  => [
				'today'     => ['title' => 'Hoy', 'data' => []],
				'yesterday' => ['title' => 'Ayer', 'data' => []],
				'week'      => ['title' => 'D&iacute;as Anteriores', 'data' => []],
				'month'     => ['title' => 'Semanas Anteriores', 'data' => []],
				'old'       => ['title' => 'Actividad m&aacute;s antigua', 'data' => []],
			],
		];
		# PARA CADA VALOR CREAR UNA CONSULTA
		foreach ($data as $key => $val) {
			// CREAR CONSULTA
			$array = $this->makeConsulta($val);
			// CONSULTAMOS
			$dato = DB::fetch($array['sql'], $array['param']);
			//
			if (!empty($dato)) {
				// AGREGAMOS AL ARRAY ORIGINAL
				$dato = array_merge($dato, $val);
				// ARMAMOS LOS TEXTOS
				$oracion = $this->makeOracion($dato);
				// DONDE PONERLO?
				$acDate = $this->makeFecha((int)$val['ac_date']);
				// PONER
				$actividad['data'][$acDate]['data'][] = $oracion;
			}
		}
		#RETORNAMOS LOS VALORES
		return $actividad;
	}

	/**
	 * @name makeConsulta
	 * @access private
	 * @params array
	 * @return array
	 */
	private function makeConsulta(array $data = []): array {
		foreach (['obj_uno', 'obj_dos'] as $obj) {
			$data[$obj] = (int) $data[$obj];
		}
		switch ((int) $data['ac_type']) {
			// DEL TIPO 1 al 7 USAMOS LA MISMA CONSULTA
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
				$sentencia = "SELECT p.post_id, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_id = :obj LIMIT 1";
			break;
			// SIGUIENDO A...
			case 8:
				$sentencia = "SELECT user_id AS avatar, user_name FROM u_miembros WHERE user_id = :obj LIMIT 1";
			break;
			// SUBIO UNA FOTO
			case 9:
				$sentencia = "SELECT f.foto_id, f.f_title, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :obj LIMIT 1";
			break;
			// PUBLICACION EN EL MURO & LE GUSTA
			case 10:
			case 11:
				if ($data['obj_dos'] === 0 || $data['obj_dos'] === 2) {
					$sentencia = "SELECT p.pub_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE p.pub_id = :obj LIMIT 1";
				} else {
					$sentencia = "SELECT c.pub_id, c.c_body, u.user_name FROM u_muro_comentarios AS c LEFT JOIN u_muro AS p ON c.pub_id = p.pub_id LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE cid = :obj LIMIT 1";
				}
			break;
		}
		return [
			'sql' => $sentencia,
			'param' => ['obj' => $data['obj_uno']]
		];
	}

	/**
	 * @name makeOracion
	 * @access private
	 * @params array
	 * @return array
	 */
	private function makeOracion(array $data = []): array {
		# VARIABLES LOCALES
		$acType           = $data['ac_type'];
		$siteUrl          = $this->Core->settings['url'];
		$oracion['id']    = $data['ac_id'];
		$oracion['style'] = $this->actividad[$acType]['css'] ?? '';
		$oracion['date']  = $data['ac_date'];
		$oracion['user']  = $data['usuario'] ?? '';
		$oracion['uid']   = $data['user_id'];
		# CON UN SWITCH ESCOGEMOS QUE ORACION CONSTRUIR
		switch ($acType) {
		# DEL TIPO 1-2, 4 y 7 USAMOS LA MISMA
		case 1:
		case 2:
		case 4:
		case 7:
			$oracion['text']  = $this->actividad[$acType]['text'];
			$oracion['link']  = $this->UrlHelper->buildPostUrl($data);
			$oracion['ltext'] = $data['post_title'];
			break;
		# DEL TIPO 3, 5 y 6 USAMOS EL MISMO
		case 3:
		case 5:
		case 6:
			$extra_text = match (true) {
				($acType === 3) => $data['obj_dos'],
				($acType === 5) => ($data['obj_dos'] === 0) ? '' : ($data['obj_dos'] + 1) . ' veces',
				default         =>($data['obj_dos'] === 0) ? 'negativo' : 'positivo'
			};
			//
			$oracion['text']  = $this->actividad[$acType]['text'][0] . " <strong>{$extra_text}</strong> " . $this->actividad[$acType]['text'][1];
			$oracion['link']  = $this->UrlHelper->buildPostUrl($data);
			$oracion['ltext'] = $data['post_title'];
			// ESTILO
			$oracion['style'] = ($acType === 6) ? 'voto_' . $extra_text : $oracion['style'];
			break;
		# ESTA SIGUIENDO A..
		case 8:
			$Avatar = new Avatar;
			$AvatarUno = $Avatar->get((int)$data['user_id']);
			$AvatarDos = $Avatar->get((int)$data['avatar']);
			// AVATARES
			$ImgUno = "<img alt=\"Avatar del usuario\" width=\"16\" height=\"16\" src=\"{$AvatarUno}\"/>";
			$ImgDos = "<img alt=\"Avatar del usuario\" width=\"16\" height=\"16\" src=\"{$AvatarDos}\"/>";
			// ORACION
			$oracion['text']  = $ImgUno . ' ' . $this->actividad[$acType]['text'] . ' ' . $ImgDos;
			$oracion['link']  = $this->UrlHelper->buildPerfilUrl($data['user_name']);
			$oracion['ltext'] = $data['user_name'];
			$oracion['style'] = '';
			break;
		# SUBIO NUEVA FOTO
		case 9:
			$oracion['text']  = $this->actividad[$acType]['text'];
			$oracion['link']  = $this->UrlHelper->buildFotoUrl($data);
			$oracion['ltext'] = $data['f_title'];
			break;
		# MURO POSTS
		case 10:
			// SEC TYPE
			$sec_type  = $data['obj_dos'];
			$link_text = $this->actividad[$acType][$sec_type]['link'];
			//
			$oracion['text']  = $this->actividad[$acType][$sec_type]['text'];
			$oracion['link']  = $this->UrlHelper->buildPerfilUrl($data['user_name'], (string)$data['pub_id']);
			$oracion['ltext'] = empty($link_text) ? $data['user_name'] : $link_text;
			$oracion['style'] = $this->actividad[$acType][$sec_type]['css'];
			break;
		# LIKES
		case 11:
			// SEC TYPE
			$sec_type  = (int) $data['obj_dos'];
			$link_text = $this->actividad[$acType][$sec_type]['link'];
			//
			$oracion['text'] = "{$this->actividad[$acType]['text']} {$this->actividad[$acType][$sec_type]['text']}";
			$oracion['link'] = $this->UrlHelper->buildPerfilUrl($data['user_name'], "?pid={$data['pub_id']}");
			//
			if ($data['obj_dos'] === 0 || $data['obj_dos'] === 2) {
				$oracion['ltext'] = $link_text ?? $data['user_name'];
			} else {
				$end_text         = (strlen($data['c_body']) > 35) ? '...' : '';
				$oracion['ltext'] = substr($data['c_body'], 0, 30) . $end_text;
			}
			break;
		}
		//
		return $oracion;
	}

	/**
	 * @name makeFecha
	 * @access private
	 * @params int
	 * @return string
	 */
	private function makeFecha(int $time = 0): string {
		$tiempo = time() - $time;
		$dias   = round($tiempo / 86400);
		//
		return match (true) {
			($dias < 1)   => 'today',
			($dias < 2)   => 'yesterday',
			($dias <= 7)  => 'week',
			($dias <= 30) => 'month',
			default       => 'old',
		};
	}
}
