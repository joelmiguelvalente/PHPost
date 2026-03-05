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
   
   protected Themes $Themes;
   private string $path;

   public function __construct(
      protected tsCore $Core
   ) {
      $this->path = (string)($_GET['path'] ?? $_POST['path'] ?? '');
      //
      $this->Themes = new Themes;
   }

   /**
    * Ya no requerimos que este instalado
    */
   public function getTemas() {
      return $this->Themes->getTemas();
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
   	$tema = $this->path;
      if(!db_exec([__FILE__, __LINE__], "query", "UPDATE w_configuracion SET tema = '{$tema['t_path']}' WHERE phpost_id = 1")) {
      	return '0: Hubo un error al cambiar el theme.';
      }
      return '1: Se cambio el theme correctamente.';
   }

   private function checkTheme(string $path) {
      list($total) = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT t_path FROM w_temas WHERE t_path = '$path'"));
      if($total > 0) {
      	return '0: El theme ya esta instalado';
      }
   }
}