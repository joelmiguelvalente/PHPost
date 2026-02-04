<?php

/**
 * @name c.noticias.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class tsNoticias {
   
   protected tsCore $Core;
   protected tsUser $User;

   public function __construct(tsCore $Core, tsUser $User) {
      $this->Core = $Core;
      $this->User = $User;
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
      $data = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_id, u.user_name, n.not_id, n.not_title, n.not_body, n.not_autor, n.not_date, n.not_expires, n.not_type, n.not_color, n.not_active FROM w_noticias AS n LEFT JOIN u_miembros AS u ON n.not_autor = u.user_id WHERE n.not_id > 0 ORDER BY n.not_id DESC, n.not_date DESC'));
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
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM w_noticias WHERE not_id = $id LIMIT 1"));
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
   		'body' => $this->Core->setSecure($this->Core->parseBadWords($body)),
      	'active' => (int)($_POST['not_active'] ?? 0),
      	'type' => (int)($_POST['not_type'] ?? 0),
      	'expires' => (int)($_POST['not_expires'] ?? 0),
      	'color' => $this->Core->setSecure((string)($_POST['not_color'] ?? ''))
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
      db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `w_noticias` (`not_body`, `not_autor`, `not_date`, `not_active`, `not_type`, `not_color`, `not_expires`) VALUES ('{$data['body']}', {$this->User->uid}, $time, {$data['active']}, {$data['type']}, '{$data['color']}', {$data['expires']})");
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
      $columnas = $this->Core->buildSqlSet($this->getEntries(), 'not_');
      if(!db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_noticias` SET $columnas WHERE not_id = $id")) {
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
      if(!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT not_id FROM w_noticias WHERE not_id = $id"))) {
      	return false;
      }
      db_exec([__FILE__, __LINE__], 'query', "DELETE FROM w_noticias WHERE not_id = $id");
      return true;
   }

   public function setNoticiaInActive() {
      $noticia = (int)($_POST['nid'] ?? 0);

      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT not_active FROM w_noticias WHERE not_id = $noticia"));
      // COMPROBAMOS
      $notIsActive = (int)$data['not_active'] === 1;
      $active = $notIsActive ? 0 : 1;
      if (!db_exec([__FILE__, __LINE__], 'query', "UPDATE w_noticias SET not_active = $active WHERE not_id = $noticia")) {
         return '0: Ocurri&oacute, un error';
     }
     return $notIsActive ? '2: Noticia desactivada' : '1: Noticia activada.';
   }

}