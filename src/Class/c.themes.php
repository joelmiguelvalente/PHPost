<?php

/**
 * @name c.themes.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class tsThemes {
   
   private int $tid;

   public function __construct(
      protected tsCore $Core, 
      protected tsUser $User
   ) {
      $this->tid = (int)($_GET['tid'] ?? $_POST['tid'] ?? 0);
   }

   public function getTemas() {
      # Obtenemos la lista de temas
      $data = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT tid, t_name, t_path, t_copy FROM w_temas WHERE tid > 0'));
      foreach($data as $tid => $theme) {
         $data[$tid]['t_screen'] = $this->Core->route('url') . "/themes/{$theme['t_path']}/screenshot.png";
      }
      # Retornamos datos
      return $data;
   }

   public function getTema(): array {
      # Obtenemos la información
      $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT tid, t_name, t_path, t_copy FROM `w_temas` WHERE tid = {$this->tid} LIMIT 1"));
      # Retornamos los datos
      return $data;
   }

   public function saveTema(): bool {
      $path = $this->Core->setSecure($_POST['path']);
      # Actualizamos la tabla w_temas
      return (db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_temas` SET t_path = '$path' WHERE tid = {$this->tid}"));
   }

   private function stateTheme(): array|bool {
   	$tema = $this->getTema();
      # Obtenemos los datos desde la funcion creada
      if(empty($tema)) {
      	return false;
      }
      return $tema;
   }

   public function changeTema(): string {
   	$tema = $this->stateTheme();
      if(!db_exec([__FILE__, __LINE__], "query", "UPDATE w_configuracion SET tema = '{$tema['t_path']}' WHERE phpost_id = 1")) {
      	return '0: Hubo un error al cambiar el theme.';
      }
      return '1: Se cambio el theme correctamente.';
   }

   public function deleteTema(): bool {
   	$tema = $this->stateTheme();
      if(!db_exec([__FILE__, __LINE__], 'query', "DELETE FROM `w_temas` WHERE tid = {$tema['tid']}")) {
      	return false;
      }
      return true;
   }

   private function checkTheme(string $path) {
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT t_path FROM w_temas WHERE t_path = '$path'"));
      if($total > 0) {
      	return '0: El theme ya esta instalado';
      }
   }

   public function newTema() {
      # Obtenemos el nombre de la carpeta a instalar por POST
      $path = str_replace(' ', '', $this->Core->setSecure($_POST['path']));
      if(!file_exists(TS_THEMES . "/$path/install.php")) {
      	return '0: La carpeta no existe o mal escrita. (no debe contener espacios)';
      }
      $this->checkTheme($path);
      $theme = require_once TS_THEMES . "/$path/install.php";
      foreach($theme as $key => $t) {
      	$theme[$key] = $this->Core->setSecure($t);
      }
      if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `w_temas` (`t_name`, `t_path`, `t_copy`) VALUES ('{$theme['name']}', '$path', '{$theme['copy']}')")) {
      	return '0: No se ha podido instalar el theme';
      }
      return '1: El theme se instalo correctamente.';
   }
}