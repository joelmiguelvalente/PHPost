<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsCensura {
	
	private int $id;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected Paginator $Paginator
	) {
		$this->id = (int)($_GET['id'] ?? $_POST['wid'] ?? 0);
	}

	public function getBadWords(): array {
		$max = 20; // MAXIMO A MOSTRAR
		$limit = $this->Paginator->setPageLimit($max, true);
		//
		$data['data'] = DB::fetchAll("SELECT u.user_id, u.user_name, bw.wid, bw.word, bw.swop, bw.method, bw.type, bw.author, bw.reason, bw.date FROM w_badwords AS bw LEFT JOIN u_miembros AS u ON bw.author = u.user_id ORDER BY bw.wid DESC LIMIT :limit", ['limit' => $limit]);
		// PAGINAS
		$total = DB::numRows('SELECT COUNT(*) FROM w_badwords');
		$data['pages'] = $this->Paginator->pageIndex("/admin/badwords?", (int)($_GET['s']??0), (int)$total, (int)$max);
		return $data;
	}

	public function getBadWord(): array {
		return DB::fetch("SELECT wid, word, swop, method, type, author, reason, `date` FROM w_badwords WHERE wid = :wid LIMIT 1", ['wid' => $this->id]);
	}

	private function checkEntries(): string|array {
		$data['method'] = (int)($_POST['method'] ?? 0);
		$data['type'] = (int)($_POST['type'] ?? 0);
		foreach(['word', 'swop', 'reason'] as $keys) {
			$data[$keys] = Html::escape($_POST[$keys]);
		}
		if (empty($data['word']) || empty($data['swop']) || (isset($_POST['reason']) && empty($data['reason']))) {
			return 'Rellene todos los campos';
		}
		if (DB::numRows("SELECT wid FROM w_badwords WHERE LOWER(word) = :word AND LOWER(swop) = :swop", [
			'word' => strtolower($data['word']),
			'swop' => strtolower($data['swop']),
		])) {
			return 'Ya existe un filtro así';
		}
		return $data;
	}

	public function saveBadWord(): string|bool {
		$data = $this->checkEntries();
		$data['author'] = $this->User->uid;
		if (!DB::update('w_badwords', $data, 'wid = :wid', ['wid' => $this->id])) {
			return 'Error al guardar';
		}
		return true;
	}

	public function newBadWord(): string|bool {
		$data = $this->checkEntries();
		$author = $this->User->uid;
		$time = time();
		if (!DB::insert('w_badwords', [
			'word' => $data['word'],
			'swop' => $data['swop'],
			'method' => $data['method'],
			'type' => $data['type'],
			'author' => $author,
			'reason' => $data['reason'],
			'date' => $time
		])) {
			return 'Error al agregar';
		}
		return true;
	}

	public function deleteBadWord(): string {
		DB::begin();
		try {
			DB::delete('w_badwords', 'wid = :wid', ['wid' => $this->id]);
			DB::commit();
			return '1: Filtro retirado';
		} catch (Exception $e) {
			DB::rollback();
			Logger::warning('Error', ['message' => $e->getMessage()]);
			return '0: Hubo un error al borrar';
		}
	}
}
