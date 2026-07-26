<?php

declare(strict_types=1);

/**
 * @package    Helpers
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class AdminHelper {

   # Extensiones para imagenes
   CONST EXTENSIONS = ["jpg", "png", "gif", "bmp", "svg"];

	public function optionsRange(array $post): string {
      $perms = Permissions::DEFINITIONS;
      foreach (Permissions::fieldMap() as $field => $meta) {
         $perms[$meta['code']] = ($meta['type'] === 'bool') ? isset($post[$field]) : (int)($post[$field] ?? 0);
      }
      return json_encode($perms, JSON_THROW_ON_ERROR);
   }

	/** 
    * Agregamos esta función ya que se repite 2 veces,
    * extraemos las imagenes
   */
   public function getExtraIcons(string $folder = 'cat', int $size = 16): array {
      # Accedemos a la carpeta de icons
      $carpeta = opendir( TS_ASSETS . "/images/icons/{$folder}" );
      # Recorremos la carpeta
      while ($archivo = readdir($carpeta)) {
         # Obtenemos la extension
         $ext = substr($archivo, -3);
         # Es una imagen?
         if (in_array($ext, self::EXTENSIONS)) {
            if ($size != 16) {
               $im_size = substr($archivo, -6, 2);
               if ($size == $im_size) $icons[] = substr($archivo, 0, -7);
            } else $icons[] = $archivo;
         }
      }
      # Retornamos las imagenes
      return $icons;
   }

}
