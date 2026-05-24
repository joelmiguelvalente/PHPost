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
      if(DB::update('w_configuracion', ['tema' => $tema], 'phpost_id = :id', ['id' => 1])) {
         if(isset($_SESSION['theme_path'])) {
            $_SESSION['theme_path'] = $tema;
         }
         return '1: Se cambio el theme correctamente.';
      }
      return '0: Hubo un error al cambiar el theme.';
   }

}
