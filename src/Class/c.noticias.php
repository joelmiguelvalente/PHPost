<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsNoticias {

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User
	) {
	}

	/**
	 * @access private
	 * @return int
	 */
	private function getID(): int {
		return (int)($_GET['nid'] ?? 0);
	}

	/**
	 * @access public
	 * @return array
	 */
	public function obtenerNoticias(): array {
		$data = DB::fetchAll('SELECT u.user_id, u.user_name, n.not_id, n.not_title, n.not_body, n.not_autor, n.not_date, n.not_expires, n.not_type, n.not_color, n.not_active FROM w_noticias AS n LEFT JOIN u_miembros AS u ON n.not_autor = u.user_id WHERE n.not_id > 0 ORDER BY n.not_id DESC, n.not_date DESC');
		return $data;
	}

	/**
	 * @access public
	 * @return array
	 */
	public function obtenerNoticia(): array {
		# Obtenemos la ID de la noticia
		$id = $this->getID();
		# Obtenemos la información
		$data = DB::fetch("SELECT * FROM w_noticias WHERE not_id = :id LIMIT 1", ['id' => $id]);
		# Retornamos los datos
		return $data;
	}

	/**
	 * @access private
	 * @return array
	 */
	private function getEntries(): array {
		$body = trim($_POST['not_body'] ?? '');
		$data = [
			'body' => Html::escape($this->Core->parseBadWords($body)),
			'active' => (int)($_POST['not_active'] ?? 0),
			'type' => (int)($_POST['not_type'] ?? 0),
			'expires' => (int)($_POST['not_expires'] ?? 0),
			'color' => Html::escape((string)($_POST['not_color'] ?? ''))
		];
		return $data;
	}

	/**
	 * @access public
	 * @return bool
	 */
	public function nuevaNoticia(): bool {
		if(isset($_POST['not_body']) && empty($_POST['not_body'])) {
			return false;
		}
		# Obtenemos datos enviados por POST
		$data = $this->getEntries();
		$time = time();
		DB::insert('w_noticias', [
			'not_body' => $data['body'],
			'not_autor' => $this->User->uid,
			'not_date' => $time,
			'not_active' => $data['active'],
			'not_type' => $data['type'],
			'not_color' => $data['color'],
			'not_expires' => $data['expires'],
		]);
		return true;
	}

	/**
	 * @access public
	 * @return bool
	 */
	public function editarNoticia(): bool {
		if(isset($_POST['not_body']) && empty($_POST['not_body'])) {
			return false;
		}
		# Obtenemos la ID de la noticia
		$id = $this->getID();
		$entries = $this->getEntries();
		$set = [];
		$params = ['id' => $id];
		foreach ($entries as $key => $value) {
			$set[] = "not_{$key} = :not_{$key}";
			$params["not_{$key}"] = $value;
		}
		$sql = 'UPDATE w_noticias SET ' . implode(', ', $set) . ' WHERE not_id = :id';
		if(!DB::query($sql, $params)) {
			return false;
		}
		return true;
	}

	/**
	 * @access public
	 * @return bool
	 */
	public function eliminarNoticia(): bool {
		# Obtenemos la ID de la noticia
		$id = $this->getID();
		if(!DB::fetch("SELECT not_id FROM w_noticias WHERE not_id = :id", ['id' => $id])) {
			return false;
		}
		DB::delete('w_noticias', 'not_id = :id', ['id' => $id]);
		return true;
	}

	public function setNoticiaInActive(): string {
		$noticia = (int)($_POST['nid'] ?? 0);

		$data = DB::fetch("SELECT not_active FROM w_noticias WHERE not_id = :id", ['id' => $noticia]);
		// COMPROBAMOS
		$notIsActive = (int)$data['not_active'] === 1;
		$active = $notIsActive ? 0 : 1;
		if (!DB::update('w_noticias', ['not_active' => $active], 'not_id = :id', ['id' => $noticia])) {
			return '0: Ocurri&oacute, un error';
	  }
	  return $notIsActive ? '2: Noticia desactivada' : '1: Noticia activada.';
	}

}
