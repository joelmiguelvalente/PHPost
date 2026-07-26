<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsMensajes {
	
	public $mensajes; // SIN LEER

	private string $myIP;

	// INSTANCIA DE LA CLASE
	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User,
		protected Paginator $Paginator,
		protected IP $IP
	) {
		// VISITANTE?
		if(empty($this->User->is_member)) return false;
		$this->mensajes = $this->countMessages();
		$this->myIP = $this->IP->getIPBinary();
	}

	private function countMessages(): int {
		// RECIBIDOS
		$recibidos = (int) DB::value("SELECT COUNT(mp_id) FROM u_mensajes WHERE mp_to = :uid AND mp_read_mon_to < 2 AND mp_del_to = 0", ['uid' => $this->User->uid]);
		// RESPUESTAS
		$respuestas = (int) DB::value("SELECT COUNT(mp_id) AS total FROM u_mensajes WHERE mp_answer = 1 AND mp_from = :uid AND mp_read_mon_from < 2 AND mp_del_from = 0", ['uid' => $this->User->uid]);
		return $recibidos + $respuestas;
	}

	private function mensajePara(bool $lower = true): string {
		$para = Html::escape(trim($_POST['para'] ?? ''));
		if($lower) {
			$para = strtolower($para);
		}
		return $para;
	}

	private function mensajeContenido(int $limit = 0, string $type = 'mensaje'): string {
		$mensaje = Html::escape($_POST[$type] ?? '', true);
		if($limit > 0) {
			$mensaje = substr($mensaje, 0, $limit);
		}
		return $mensaje;
	}

	private function mensajeAsunto(): string {
		return Html::escape(trim($_POST['asunto'] ?? 'Sin asunto expresado!'));
	}

	/*
		getValid() // Comprobamos si el usuario ingresado es válido para enviar el mensaje.
	*/
	public function getValid(): string {
		$para = $this->mensajePara();
		if($para === strtolower($this->User->nick)) return '1';
		//
		$exists = DB::exists("SELECT 1 FROM u_miembros WHERE LOWER(user_name) = LOWER(:para) LIMIT 1", ['para' => $para]);
		return !$exists ? '2' : '0';
	}

	private function newMensajeAntiflood(string $asunto = ''): void {
		$antiflood = $this->User->permiso('limites.antiflood') * 5;
		$mensaje = $this->mensajeContenido(75);
		//
		$newtime = time() - $antiflood;
		$nexttime = $newtime * 3600;
		$exists = DB::exists("SELECT 1 FROM u_mensajes WHERE (mp_date > :newtime AND mp_from = :uid) OR (mp_date > :nexttime AND mp_from = :uid AND mp_preview = :mensaje AND mp_subject = :asunto) ORDER BY mp_id DESC LIMIT 1", [
			'uid' => $this->User->uid, 
			'mensaje' => $mensaje, 
			'asunto' => $asunto,
			'newtime' => $newtime,
			'nexttime' => $nexttime
		]);
		if($exists) die('Espere '.$antiflood.' segundos para continuar'); 
		$this->User->antiFlood(true, 'mps');
	}

	private function mensajeComprobar(int $user_id, string $para): string {
		$comp = DB::fetch("SELECT 
			(SELECT COUNT(follow_id) FROM u_follows WHERE f_id = :userid AND f_user = :uid AND f_type = 1 LIMIT 1) as lesigo, 
			(SELECT COUNT(follow_id) FROM u_follows WHERE f_id = :uid AND f_user = :userid AND f_type = 1 LIMIT 1) as mesigue, 
			(SELECT COUNT(user_id) FROM u_miembros WHERE user_id = :userid AND user_rango < 2 LIMIT 1) as noesunadmin", [
			'userid' => $user_id,
			'uid' => $this->User->uid
		]);
		// SI EL RECEPTOR ES DEL GRUPO ADMINISTRADORES PRINCIPALES SALTAMOS LA COMPROBACIÓN
		if(!$comp['noesunadmin']) {
			// COMPROBACIONES DE LA PRIVACIDAD
			$data = DB::fetch("SELECT p_mensajes_privados FROM u_perfil WHERE user_id = :userid LIMIT 1", ['userid' => $user_id]);

			switch($data['p_mensajes_privados']) {
				case 'nobody':
				case 'off':
					if($data['p_mensajes_privados'] === 'nobody' && !$this->User->is_admod) {
						return '0: Lo sentimos, pero '.$para.' no permite recibir mensajes';
					} elseif($data['p_mensajes_privados'] === 'off' && !$this->User->is_admod) {
						return '0: Lo sentimos, pero '.$para.' no puede utilizar la mensajería privada en estos momentos ';
					}
				break;
				case 'friends_mutual':
				case 'friends_any':
				case 'followers':
				case 'following':
					$lesigoomesigue = ((int)$comp['mesigue'] === 0 && (int)$comp['lesigo'] === 0) ? false : true;
					$lesigoymesigue = ((int)$comp['mesigue'] === 1 && (int)$comp['lesigo'] === 1) ? true : false;
					if($data['p_mensajes_privados'] === 'friends_mutual' && !$lesigoymesigue && !$this->User->is_admod) {
						return '0: Debes seguir a '.$para.' y éste debe seguirte para poder enviarle un mensaje.';
					} elseif($data['p_mensajes_privados'] === 'friends_any' && !$lesigoomesigue && !$this->User->is_admod) {
						return '0: Debes seguir a '.$para.' o éste debe seguirte para poder enviarle un mensaje.';
					} elseif($data['p_mensajes_privados'] === 'followers' && !$comp['lesigo'] && !$this->User->is_admod) {
						return '0: Debes seguir a '.$para.' para poder enviarle un mensaje.';
					} elseif($data['p_mensajes_privados'] === 'following' && !$comp['mesigue'] && !$this->User->is_admod) {
						return '0: '.$para.' debe seguirte para que puedas enviarle un mensaje';
					}
				break;
			}
		}
		return '';
	}

	/**
	 * @name newMensaje
	 * @access public
	 * @param string
	 * @return string;
	 * @info ENVIA UN NUEVO MENSAJE
	*/
	public function newMensaje(): string {
		if(!$this->User->is_member && (int)$this->User->info['user_baneado'] === 1 || (int)$this->User->info['user_activo'] === 0) {
			return 'Debe tener una cuenta activa para realizar esta operación';
		}
		$asunto = $this->mensajeAsunto();
		//ANTI FLOOD 
		$this->newMensajeAntiflood($asunto);
		//
		$para = $this->mensajePara();
		$mensaje = $this->mensajeContenido();
		if(str_replace(array("\n","\t",' '),'',$mensaje) === '') {
			return 'Debes ingresar el contenido de tu mensaje.';
		}
		//
		$user_id = $this->User->getUserID($para);
		if (empty($user_id)) {
			return 'El usuario no existe. Inténtalo nuevamente.';
		}
		//BLOQUEADO
		if (!$this->User->is_admod) {
			$exists = DB::exists("SELECT 1 FROM u_bloqueos WHERE (b_user = :userid AND b_auser = :uid) OR (b_user = :uid AND b_auser = :userid) LIMIT 1", [
				'uid' => $this->User->uid,  
				'userid' => $user_id
			]);
			if ($exists) return 'No puedes enviarle mensajes a ' . $para;
		}
		//VISTA PREVIA
		$preview = substr($mensaje, 0, 75);
		$com = DB::fetch("SELECT user_activo, user_baneado FROM u_miembros WHERE LOWER(user_name) = LOWER(:para)", ['para' => $para]);
		if((int)$com['user_activo'] === 0 && (int)$com['user_baneado'] === 1) {
			return 'El usuario no puede recibir nuevos mensajes.';
		}
		
		$compError = $this->mensajeComprobar($user_id, $para);
		if ($compError !== '') return $compError;

		$mp_id = DB::insert('u_mensajes', [
			'mp_to' => $user_id,
			'mp_from' => $this->User->uid,
			'mp_subject' => $asunto,
			'mp_preview' => $preview,
			'mp_date' => time()
		]);
		if(!$mp_id) {
			return 'Ocurrió un error. Inténtalo nuevamente.';
		}
		if(!DB::insert('u_respuestas', [
			'mp_id' => $mp_id,
			'mr_from' => $this->User->uid,
			'mr_body' => $mensaje,
			'mr_ip' => $this->myIP,
			'mr_date' => time()
		])) {
			return show_error('Error al ejecutar la consulta de la línea '.__LINE__.' de '.__FILE__.'.', 'db');
		}
		return "El mensaje ha sido enviado a <a href=\"{$this->Core->settings['url']}/@{$para}\">{$para}</a>. <br /><br /> <center><a class=\"btn btn-success resp\" href=\"{$this->Core->settings['url']}/mensajes/leer/$mp_id\">Ver el mensaje enviado</a></center>";
	}

	/*
		newRespuesta()
	*/
	public function newRespuesta(): string|array {
		$mp_id = (int)($_POST['id'] ?? 0);

		$mensaje = $this->mensajeContenido(0, 'body');
		if(str_replace(array("\n","\t",' '),'',$mensaje) === '') {
			return '0: Debes ingresar tu respuesta.';
		}
	
		$isAdmod = $this->User->is_admod ? '' : "AND mp_del_to = 0 AND mp_del_from = 0";
		$msg = DB::fetch("SELECT mp_to, mp_from, mp_answer FROM u_mensajes WHERE mp_id = :mpid $isAdmod LIMIT 1", ['mpid' => $mp_id]);
		
		// 
		if(empty($msg)) {
			return '0: El mensaje no existe.';
		}
		$this->User->antiFlood(true, 'mps');
		// BLOQUEADO
		if(!$this->User->is_admod) {
			$exists = DB::exists("SELECT 1 FROM u_bloqueos WHERE (b_user = :to AND b_auser = :from) OR (b_user = :from AND b_auser = :to) LIMIT 1", [
				'from' => $msg['mp_from'],  
				'to' => $msg['mp_to']
			]);
			if($exists || ($this->User->uid !== (int)$msg['mp_from'] && $this->User->uid !== (int)$msg['mp_to'])) return '0: No puedes contestar este mensaje';
		}
		// VISTA PREVIA
		$preview = substr($mensaje, 0, 75);
		$mr_id = DB::insert('u_respuestas', [
			'mp_id' => $mp_id,
			'mr_from' => $this->User->uid,
			'mr_body' => $mensaje,
			'mr_ip' => $this->myIP,
			'mr_date' => time()
		]);
		if(!$mr_id) {
			return 'Ocurrió un error. Inténtalo nuevamente.';
		}
		// CUANDO RESPONDA EL DESTINATARIO...
		$update = [];
		if((int)$msg['mp_from'] !== $this->User->uid){
			if((int)$msg['mp_answer'] === 0) $update = ['mp_answer' => 1];
			$update += [
				'mp_read_to' => 1,
				'mp_read_mon_to' => 2,
				'mp_read_from' => 0,
				'mp_read_mon_from' => 0,
				'mp_del_from' => 0
			];
		} else {
			$update = [
				'mp_read_to' => 0,
				'mp_read_mon_to' => 0,
				'mp_read_from' => 1,
				'mp_read_mon_from' => 2,
				'mp_del_to' => 0
			];
		}
		// ACTUALIZAMOS EL MENSAJE
		DB::update('u_mensajes', [
			'mp_preview' => $preview,
			'mp_date' => time(),
			...$update
		], 'mp_id = :mpid', ['mpid' => $mp_id]);
		//
		$return['mp_date'] = time();
		$return['mp_ip'] = $this->myIP;
		$return['mp_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($mensaje), true);
		//
		return $return;
	}

	/*
		getMensajes($type)
		:: FALTA LA PAGINACION :/
	*/
	public function getMensajes(int $type = 1, bool $unread = false, string $where = 'normal'): array {
	   $data = [];
	   $uid = $this->User->uid;

	   $sqlRecibidos = fn(string $extra = '') => "SELECT mp_id, mp_to, mp_from, mp_read_to, mp_read_mon_to, mp_subject, mp_preview, mp_date, user_id, user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON mp_from = user_id WHERE mp_to = :uid AND mp_del_to = 0 {$extra}";
	   //
	   $sqlEnviados = fn(string $extra = '') => "SELECT mp_id, mp_to, mp_from, mp_read_from, mp_read_mon_from, mp_subject, mp_preview, mp_date, user_id, user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON mp_to = user_id WHERE mp_from = :uid AND mp_del_from = 0 AND mp_answer = 1 {$extra}";

	   if ($type === 1) {
	      $limit = '';
	      $funread = $sunread = '';
	      if ($this->mensajes > 0 || $unread) {
	         $funread = "AND mp_read_mon_to " . ($where !== 'live' ? '< 2' : '= 0');
	         $sunread = "AND mp_read_mon_from " . ($where !== 'live' ? '< 2' : '= 0');
	      } else {
	         $limit = 'LIMIT 5';
	      }
	      $sql = $sqlRecibidos($funread) . " UNION (" . $sqlEnviados($sunread) . ") ORDER BY mp_id DESC {$limit}";
	      $rows = DB::fetchAll($sql, ['uid' => $uid]);
	      $data['total'] = 0;
	      foreach ($rows as $row) {
	         $row['mp_from'] = ($row['mp_from'] == $uid) ? $row['mp_to'] : $row['mp_from'];
	         $data['data'][$row['mp_date']] = $row;
	         $monField = ($uid == $row['mp_to']) ? 'mp_read_mon_to' : 'mp_read_mon_from';
	         $monValue = ($where === 'live') ? 1 : 2;
	         DB::update('u_mensajes', [$monField => $monValue], 'mp_id = :mpid', ['mpid' => $row['mp_id']]);
	         $data['total']++;
	      }
	   } elseif ($type === 2) {
	      $funread = $unread ? 'AND mp_read_to = 0' : '';
	      $sunread = $unread ? 'AND mp_read_from = 0' : '';
	      $sql = $sqlRecibidos($funread) . " UNION (" . $sqlEnviados($sunread) . ") ORDER BY mp_id DESC";
	      $total = count(DB::fetchAll($sql, ['uid' => $uid]));
	      $pages = $this->Paginator->getPagination($total, 12);
	      $data['pages'] = $pages;
	      $rows = DB::fetchAll($sql . ' LIMIT ' . $pages['limit'], ['uid' => $uid]);
	      foreach ($rows as $row) {
	         $row['mp_type'] = ($row['mp_from'] != $uid) ? 1 : 2;
	         $row['mp_from'] = ($row['mp_from'] == $uid) ? $row['mp_to'] : $row['mp_from'];
	         $data['data'][$row['mp_date']] = $row;
	      }
	   } elseif ($type === 3) {
	      $sql = "SELECT m.mp_id, m.mp_to, m.mp_read_to, m.mp_subject, m.mp_preview, m.mp_date, u.user_id, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_to = u.user_id WHERE m.mp_from = :uid ORDER BY m.mp_id DESC";
	      $total = count(DB::fetchAll($sql, ['uid' => $uid]));
	      $pages = $this->Paginator->getPagination($total, 12);
	      $data['pages'] = $pages;
	      $rows = DB::fetchAll($sql . ' LIMIT ' . $pages['limit'], ['uid' => $uid]);
	      foreach ($rows as $row) {
	         $row['mp_type'] = 2;
	         $row['mp_from'] = $row['mp_to'];
	         $row['mp_read_to'] = 1;
	         $data['data'][$row['mp_date']] = $row;
	      }
	   } elseif ($type === 4) {
	      $sql = "SELECT m.mp_id, m.mp_from, m.mp_read_from, m.mp_subject, m.mp_preview, m.mp_date, u.user_id, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid AND m.mp_answer = 1 ORDER BY m.mp_id DESC";
	      $total = count(DB::fetchAll($sql, ['uid' => $uid]));
	      $pages = $this->Paginator->getPagination($total, 12);
	      $data['pages'] = $pages;
	      $rows = DB::fetchAll($sql . ' LIMIT ' . $pages['limit'], ['uid' => $uid]);
	      foreach ($rows as $row) {
	         $row['mp_type'] = 1;
	         $row['mp_read_to'] = 1;
	         $data['data'][$row['mp_date']] = $row;
	      }
	   } elseif ($type === 5) {
	      $qm = Html::escape($_GET['qm'] ?? '');
	      $sql = "SELECT mp_id, mp_to, mp_from, mp_read_to, mp_subject, mp_preview, mp_date, user_id, user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON mp_from = user_id WHERE mp_to = :uid AND mp_del_to = 0 AND mp_subject LIKE :qm ORDER BY mp_id DESC";
	      $params = ['uid' => $uid, 'qm' => '%' . $qm . '%'];
	      $total = count(DB::fetchAll($sql, $params));
	      $pages = $this->Paginator->getPagination($total, 12);
	      $data['pages'] = $pages;
	      $rows = DB::fetchAll($sql . ' LIMIT ' . $pages['limit'], $params);
	      foreach ($rows as $row) {
	         $row['mp_type'] = ($row['mp_from'] != $uid) ? 1 : 2;
	         $row['mp_from'] = ($row['mp_from'] == $uid) ? $row['mp_to'] : $row['mp_from'];
	         $data['data'][$row['mp_date']] = $row;
	      }
	      $data['texto'] = $qm;
	   }
	   if (isset($data['data']) && is_array($data['data'])) krsort($data['data']);
	   return $data;
	}

	/*
		readMensaje()
	*/
	public function readMensaje(): array {
	  	$mp_id = (int)($_GET['id'] ?? 0);
	  	if (!$mp_id) $this->Core->redirectTo($this->Core->settings['url'] . '/mensajes/');

	  	$isAdmod = $this->User->is_admod ? '' : "AND ((m.mp_to = :uid AND m.mp_del_to = 0) OR (m.mp_from = :uid AND m.mp_del_from = 0))";
	  	$isAdmodParam = $this->User->is_admod ? [] : ['uid'  => $this->User->uid];
	  	$data = DB::fetch("SELECT m.*, u.user_id, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_id = :mpid {$isAdmod} LIMIT 1", [
	  	   'mpid' => $mp_id,
	  	   ...$isAdmodParam
	  	]);
	  	if (empty($data)) $this->Core->redirectTo($this->Core->settings['url'] . '/mensajes/');

	   $canView = $this->User->is_admod && DB::exists("SELECT 1 FROM w_denuncias WHERE obj_id = :mpid AND d_type = 'mensaje' LIMIT 1", ['mpid' => $mp_id]);
	   if ($data['mp_to'] != $this->User->uid && $data['mp_from'] != $this->User->uid && !$canView && !$this->User->is_admod) {
	      $this->Core->redirectTo($this->Core->settings['url'] . '/mensajes/');
	   }

	   $history['msg'] = $data;
	   $rows = DB::fetchAll("SELECT r.*, u.user_id, u.user_name FROM u_respuestas AS r LEFT JOIN u_miembros AS u ON r.mr_from = u.user_id WHERE r.mp_id = :mpid ORDER BY mr_id", ['mpid' => $mp_id]);
	   foreach ($rows as $row) {
	      $row['mr_body'] = $this->Core->parseBadWords($this->Core->parseBBCode($row['mr_body']), true);
	      $history['res'][] = $row;
	   }

	   $resp = count($history['res']);
	   $from = $history['res'][$resp - 1]['mr_from'];
	   $update = null;
	   if ($this->User->uid == $data['mp_to']) {
	      $update = ['mp_read_to' => 1, 'mp_read_mon_to' => 2];
	      $history['msg']['mp_type'] = 1;
	   } elseif ($from == $data['mp_to'] && $data['mp_from'] == $this->User->uid) {
	      $update = ['mp_read_from' => 1, 'mp_read_mon_from' => 2];
	      $history['msg']['mp_type'] = 2;
	   } elseif ($from == $data['mp_from']) {
	      $update = ['mp_read_from' => 1, 'mp_read_mon_from' => 2];
	      $history['msg']['mp_type'] = 2;
	   }
	   if ($update) DB::update('u_mensajes', $update, 'mp_id = :mpid', ['mpid' => $mp_id]);
	   $user_id = ($data['mp_from'] != $this->User->uid) ? $data['mp_from'] : $data['mp_to'];
	   $history['ext']['can_read'] = (!$this->User->is_admod && DB::exists("SELECT 1 FROM u_bloqueos WHERE (b_user = :to AND b_auser = :from) OR (b_user = :from AND b_auser = :to) LIMIT 1", [
	      'to'   => $data['mp_to'],
	      'from' => $data['mp_from']
	   ])) ? 0 : 1;
	   $history['ext']['uid']  = $user_id;
	   $history['ext']['user'] = $this->User->getUserName($user_id);
	   return $history;
	}

	public function editMensajes(): bool {
	   $ids = explode(',', Html::escape($_POST['ids'] ?? ''));
	   $nids = [];
	   foreach ($ids as $nid) {
	      $id = explode(':', $nid);
	      if (isset($id[0], $id[1])) $nids[(int)$id[1]][] = (int)$id[0];
	   }
	   if (empty($nids)) return false;

	   $act = htmlspecialchars($_POST['act'] ?? '');
	   $uid = $this->User->uid;
	   $in1 = !empty($nids[1]) ? implode(',', $nids[1]) : '0';
	   $in2 = !empty($nids[2]) ? implode(',', $nids[2]) : '0';

	   $estados = [
	      'read'   => ['to' => [1, 2], 'from' => [1, 2]],
	      'unread' => ['to' => [0, 1], 'from' => [0, 1]],
	   ];

	   if (isset($estados[$act])) {
	      $v = $estados[$act];
	      DB::raw("UPDATE u_mensajes SET mp_read_to = {$v['to'][0]}, mp_read_mon_to = {$v['to'][1]} WHERE mp_id IN({$in1}) AND mp_to = :uid", ['uid' => $uid]);
	      DB::raw("UPDATE u_mensajes SET mp_read_from = {$v['from'][0]}, mp_read_mon_from = {$v['from'][1]} WHERE mp_id IN({$in2}) AND mp_from = :uid", ['uid' => $uid]);
	      return true;
	   }

	   if ($act === 'delete') {
	      DB::raw("UPDATE u_mensajes SET mp_del_to = 1 WHERE mp_id IN({$in1}) AND mp_to = :uid", ['uid' => $uid]);
	      DB::raw("UPDATE u_mensajes SET mp_del_from = 1 WHERE mp_id IN({$in2}) AND mp_from = :uid", ['uid' => $uid]);
	      $allIds = implode(',', array_merge($nids[1] ?? [], $nids[2] ?? []));
	      if ($allIds) {
	         $rows = DB::fetchAll("SELECT mp_id FROM u_mensajes WHERE mp_id IN({$allIds}) AND mp_del_to = 1 AND mp_del_from = 1");
	         foreach ($rows as $row) {
	            DB::delete('u_mensajes', 'mp_id = :mpid', ['mpid' => $row['mp_id']]);
	            DB::delete('u_respuestas', 'mp_id = :mpid', ['mpid' => $row['mp_id']]);
	         }
	      }
	      return true;
	   }
	   return false;
	}
}
