<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsBloqueos {

	private int $id;

	private string $myIP;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User,
		protected Paginator $Paginator,
		protected IP $IP
	) {
		$this->id = (int)($_GET['id'] ?? $_POST['bid'] ?? 0);
		$this->myIP = $this->myIP = $this->IP->getIPBinary();
	}

	public function getBlackList(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		//
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, b.id, b.type, b.value, b.reason, b.author, b.date FROM w_blacklist AS b LEFT JOIN u_miembros AS u ON b.author = u.user_id ORDER BY b.date DESC, b.id DESC LIMIT :limit", [
			'limit' => 20
		]);
		// PAGINAS
		$total = DB::numRows("SELECT COUNT(*) FROM w_blacklist");
		$data['pages'] = $this->Paginator->pageIndex("/admin/blacklist?", (int)($_GET['s']??0), (int)$total, (int)$max);
		//
		return $data;
   }

	public function getBlock(): array {
		return DB::fetch("SELECT type, value, reason FROM w_blacklist WHERE id = :id LIMIT 1", [
			'id' => $this->id
		]);
	}

	private function checkEntries(): string|array {
		$type = (int)($_POST['type'] ?? 0);
		$value = Html::escape($_POST['value'] ?? '');
		$reason = Html::escape($_POST['reason'] ?? '');
		if(empty($value) || $type === 0 || (isset($_POST['reason']) && empty($reason))) {
			return 'Debe rellenar todos los campos';
		}
		if ($type === 1 && $value === $this->myIP) {
			return 'No puedes bloquear tu propia IP';
		}
		if (DB::numRows("SELECT id FROM w_blacklist WHERE type = :type AND value = :value", [
			'type' => $type,
			'value' => $value
		])) {
			return 'Ya existe un bloqueo así';
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
		if (DB::update('w_blacklist', $data, 'id = :id', ['id' => $this->id])) {
			return true;
		}
		return $data;
	}

	public function newBlock(): string|bool {
		$data = $this->checkEntries();
		$time = time();
		if (!DB::insert('w_blacklist', [
			'type' => $data['type'],
			'value' => $data['value'],
			'reason' => $data['reason'],
			'author' => $this->User->uid,
			'date' => $time
		])) {
			return 'Ya existe un bloqueo así';
		}
		return true;		
	}

	public function deleteBlock(): string {
		DB::begin();
		try {
			DB::delete('w_blacklist', 'id = :id', ['id' => $this->id]);
			DB::commit();
			return '1: Bloqueo retirado';
		} catch (Exception $e) {
			DB::rollback();
			Logger::warning('Error', ['message' => $e->getMessage()]);
			return '0: Hubo un error al borrar';
		}
	}
}
