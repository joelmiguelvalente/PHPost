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

class tsCensura {
	
	protected Paginator $Paginator;
	private int $id;

	public function __construct(
      protected tsCore $Core, 
      protected tsUser $User
   ) {
		$this->Paginator = new Paginator($this->Core->settings['url']);
		$this->id = (int)($_GET['id'] ?? $_POST['wid'] ?? 0);
	}

	public function getBadWords(): array {
	 	$max = 20; // MAXIMO A MOSTRAR
	 	$limit = $this->Paginator->setPageLimit($max, true);
      //
      $data['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, bw.* FROM w_badwords AS bw LEFT JOIN u_miembros AS u ON bw.author = u.user_id ORDER BY bw.wid DESC LIMIT $limit"));
      // PAGINAS
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(*) FROM w_badwords'));
		$this->Paginator->route = $this->Core->settings['url'];

      $data['pages'] = $this->Paginator->pageIndex("/admin/badwords?", (int)($_GET['s']??0), (int)$total, (int)$max);
      return $data;
   }

   public function getBadWord(): array {
      return db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM w_badwords WHERE wid = {$this->id} LIMIT 1"));
   }

   private function checkEntries(): string|array {
   	$data['method'] = (int)($_POST['method'] ?? 0);
   	$data['type'] = (int)($_POST['type'] ?? 0);
   	$data['word'] = trim($_POST['word'] ?? '');
   	$data['swop'] = trim($_POST['swop'] ?? '');
   	$data['reason'] = trim($_POST['reason'] ?? '');
   	if (empty($data['word']) || empty($data['swop']) || (isset($_POST['reason']) && empty($data['reason']))) {
         return 'Rellene todos los campos';
      }
      $lowword = strtolower($data['word']);
      $lowswop = strtolower($data['swop']);
      if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT wid FROM w_badwords WHERE LOWER(word) = '{$lowword}' AND LOWER(swop) = '{$lowswop}'"))) {
      	return 'Ya existe un filtro as&iacute;';
      }
      return $data;
   }

   public function saveBadWord() {
   	$data = $this->checkEntries();
   	$data['author'] = $this->User->uid;
   	$columns = $this->Core->buildSqlSet($data);
   	if (!db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_badwords` SET $columns WHERE wid = {$this->id}")) {
   		return 'Error al guardar';
   	}
      return true;
   }

  	public function newBadWord() {
   	$data = $this->checkEntries();
   	$author = $this->User->uid;
   	$time = time();
  		if (!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_badwords (word, swop, method, type, author, reason, `date`) VALUES ('{$data['word']}', '{$data['swop']}', {$data['method']}, {$data['type']}, $author, '{$data['reason']}', $time)")) {
  			return 'Error al agregar';
  		}
  		return true;
   }

   public function deleteBadWord() {
		return (db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_badwords WHERE wid = {$this->id}")) ? '1: Filtro retirado' : '0: Hubo un error al borrar';
   }
}
