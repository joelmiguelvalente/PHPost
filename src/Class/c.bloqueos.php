<?php

declare(strict_types=1);

/**
 * @package    PHPost/Class
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

class tsBloqueos {
	
	protected Paginator $Paginator;
	private int $id;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
		$this->Paginator = new Paginator($this->Core->settings['url']);
		$this->id = (int)($_GET['id'] ?? $_POST['bid'] ?? 0);
	}

	public function getBlackList(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		//
		$data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, b.* FROM w_blacklist AS b LEFT JOIN u_miembros AS u ON b.author = u.user_id ORDER BY b.date DESC, b.id DESC LIMIT $limit"));
		// PAGINAS
		list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM w_blacklist'));
		$this->Paginator->route = $this->Core->settings['url'];
		$data['pages'] = $this->Paginator->pageIndex("/admin/blacklist?", (int)($_GET['s']??0), (int)$total, (int)$max);
		//
		return $data;
   }

	public function getBlock(): array {
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT type, value, reason FROM w_blacklist WHERE id = {$this->id} LIMIT 1"));
		return $data;
	}

	private function checkEntries(): string|array {
		$type = (int)($_POST['type'] ?? 0);
		$value = $this->Core->setSecure(trim($_POST['value'] ?? ''));
		$reason = $this->Core->setSecure(trim($_POST['reason'] ?? ''));
		if(empty($value) || $type === 0 || (isset($_POST['reason']) && empty($reason))) {
			return 'Debe rellenar todos los campos';
		}
		if ($type === 1 && $value === (new IP)->getIP()) {
			return 'No puedes bloquear tu propia IP';
		}
		if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM w_blacklist WHERE type = {$data['type']} AND value = '{$data['value']}'"))) {
			return 'Ya existe un bloqueo as&iacute;';
		}
		return [
			'value' => $value,
			'type' => $type,
			'reason' => $reason
		];
	}

	public function saveBlock(): string|bool {
		$data = $this->checkEntries();
   	$data['author'] = $this->User->uid;
   	$columns = $this->Core->buildSqlSet($data);
		if (db_exec([__FILE__, __LINE__], 'query', "UPDATE w_blacklist SET $columns WHERE id = {$this->id}")) {
			return true;
		}
		return $data;
	}

	public function newBlock(): string|bool {
		$data = $this->checkEntries();
		$time = time();
		if (!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_blacklist (type, value, reason, author, date) VALUES ({$data['type']}, '{$data['value']}', '{$data['reason']}', {$this->User->uid}, $time)")) {
			return 'Ya existe un bloqueo as&iacute;';
		}
		return true;		
	}

	public function deleteBlock(): string {
		return (db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_blacklist WHERE id = {$this->id}")) ? '1: Bloqueo retirado' : '0: Hubo un error al borrar';
	}
}
