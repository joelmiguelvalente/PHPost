<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsDenuncias {

	private string|int $razon;

	private string $extras;
	// tipos: 1 = post | mensaje = 2 | usuario = 3 | foto = 4

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User
	) {
		// VARS
		$this->razon = Html::escape($_POST['razon']);
		$this->extras = Html::escape($_POST['extras']);
	}

	private function howManyComplaint(int $objId, string $type): int {
		$count = DB::numRows("SELECT 1 FROM w_denuncias WHERE obj_id = :obj AND d_type = :type",['obj' => $objId, 'type' => $type]);
		return $count === false ? 0 : (int)$count;
	}

	private function addComplaint(int $objId, string $type = 'post', string $delTable = ''): string {
		// INSERTAR NUEVA DENUNCIA
		if(!DB::insert('w_denuncias', [
			'obj_id' => $objId,
			'd_user' => $this->User->uid,
			'd_razon' => $this->razon,
			'd_extra' => $this->extras,
			'd_type' => $type,
			'd_date' => time()
		])) return '0: Error, inténtalo más tarde.';
		switch($type) {
			case 'mensaje':
				DB::update('u_mensajes', [$delTable => 1], 'mp_id = :id', ['id' => $objId]);
				return '1: Has denunciado un mensaje como correo no deseado.';
			break;
			case 'usuario':
				DB::raw("UPDATE u_miembros SET user_bad_hits = user_bad_hits + 1 WHERE user_id = :uid", ['uid' => $objId]);
				return '1: Este usuario ha sido denunciado.';
			break;
			default:
				return '1: La denuncia fue enviada.';
		}
	}

	private function hasAlreadyReported(int $objId, string $type, string $message): ?string {
		// YA HA REPORTADO?
		$alreadyReported = DB::exists("SELECT 1 FROM w_denuncias WHERE obj_id = :id AND d_user = :user AND d_type = :type", ['id' => $objId, 'user' => $this->User->uid, 'type' => $type]);
		return ($alreadyReported) ? '0: ' . $message : null;
	}

	private function setDenunciaPost(int $objId): string {
		$isSticky = DB::fetch("SELECT post_id, post_user, post_sticky FROM p_posts WHERE post_id = :pid LIMIT 1", ['pid' => $objId]);
		if(empty($isSticky['post_id'])) {
			return '0: No puedes denunciar un post que no existe.';
		}
		if((int)$isSticky['post_user'] === $this->User->uid) {
			return '0: No puedes denunciar tus propios post.';
		}
		if((int)$isSticky['post_sticky'] === 1) {
			return '0: No puedes denunciar posts en sticky.';
		}
		if($this->User->is_admod) {
			return '0: No puedes denunciar siendo moderador, pero puedes atender las denuncias de los usuarios.';
		}
		if ($result = $this->hasAlreadyReported($objId, 'post', 'Ya has denunciado este post.')) {
			return $result;
		}
		$denuncias = $this->howManyComplaint($objId, 'post');
		if ($denuncias >= 2) {
			DB::update('p_posts', ['post_status' => 'publicado'], 'post_id = :id', ['id' => $objId]);
			DB::raw("UPDATE w_stats SET stats_posts = stats_posts - 1 WHERE stats_no = 1");
		}
		return $this->addComplaint($objId, 'post');
	}

	private function setDenunciaFoto(int $objId): string {
		$myPhoto = DB::fetch("SELECT foto_id, f_user, f_status FROM f_fotos WHERE foto_id = :fid LIMIT 1", ['fid' => $objId]);
		if(empty($myPhoto['foto_id'])) {
			return '0: Esta foto no existe';
		}
		if((int)$myPhoto['f_user'] === $this->User->uid) {
			return '0: No puedes denunciar tus propias fotos.';
		}
		if((int)$myPhoto['f_status'] === 1) {
			return '0: No puedes denunciar fotos ocultas.';
		}
		if ($result = $this->HasAlreadyReported($objId, 'foto', 'Ya denunciaste esta foto')) {
			return $result;
		}
		$denuncias = $this->howManyComplaint($objId, 'foto');
		if ($denuncias >= 2) {
			DB::update('f_fotos', ['f_status' => 1], 'foto_id = :id', ['id' => $objId]);
			DB::raw("UPDATE w_stats SET stats_fotos = stats_fotos - 1 WHERE stats_no = 1");
		}
		return $this->addComplaint($objId, 'foto');
	}

	private function setDenunciaMensaje(int $objId): string {
		$result = $this->hasAlreadyReported($objId, 'mensaje', 'Ya has denunciado este mensaje. Nuestros moderadores ya lo analizan.');
		if ($result !== null) {
			return $result;
		}
		// DONDE LO BORRAREMOS?
		$mensaje = DB::fetch("SELECT mp_id, mp_to, mp_from FROM u_mensajes WHERE mp_id = :mid LIMIT 1", ['mid' => $objId]);
		if (empty($mensaje)) return '0: Mensaje no existe';

		$insertResult = $this->addComplaint($objId, 'mensaje');
		if (strpos($insertResult, '0:') === 0) return $insertResult;

		$delColumn = ($mensaje['mp_to'] == $this->User->uid) ? 'mp_del_to' : 'mp_del_from';
		DB::update('u_mensajes', [$delColumn => 1], 'mp_id = :id', ['id' => $objId]);
		$redirect = $this->Core->settings['url'];
		return '1: Mensaje denunciado. <script>setTimeout(function(){location.href="'.$redirect.'/mensajes"},1500)</script>';
	}

	private function setDenunciaUsuario(int $objId): string {
		$result = $this->hasAlreadyReported($objId, 'usuario', 'Ya has denunciado a este usario.');
		if ($result !== null) {
			return $result;
		}
		$username = $this->User->getUserName($objId);
		if(empty($username)) {
			return '0: Opps... Este usuario no existe.';
		}
		// INSERTAR NUEVA DENUNCIA
		$insertResult = $this->addComplaint($objId, 'usuario');
		if (strpos($insertResult, '0:') === 0) return $insertResult;
		DB::raw("UPDATE u_miembros SET user_bad_hits = user_bad_hits + 1 WHERE user_id = :uid", ['uid' => $objId]);
		return '1: Usuario denunciado';
	}

	/*
		setDenuncia()
	*/
	public function setDenuncia(int $objId, string $type = 'post'): string {
		return match($type) {
			'post' => $this->setDenunciaPost($objId),
			'foto' => $this->setDenunciaFoto($objId),
			'mensaje' => $this->setDenunciaMensaje($objId),
			'usuario' => $this->setDenunciaUsuario($objId),
			default => '0: No hay ninguna accción'
		};
	}
}
