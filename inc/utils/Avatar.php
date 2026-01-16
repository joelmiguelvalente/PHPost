<?php

/**
 * @package    PHPost
 * @author     Miguel92
 * @copyright  2026
 * @version    2.0.0
*/

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class Avatar {

   private tsCore $core;
   private string $storagePath;
   private string $publicPath;

   public function __construct() {
   	global $tsCore;
      $this->core        = $tsCore;
      $this->storagePath = TS_STORAGE . 'avatar/';
      $this->publicPath  = $this->core->route('storage:avatar') . '/';
   }
   /**
    * Obtiene la URL del avatar del usuario.
    */
   public function use(int $uid): string {
      $filename = $this->buildFilename($uid);
      return $this->publicPath . $filename;
   }

   /**
    * Obtiene la URL del avatar del usuario.
    */
   public function get(int $uid, string $username): string {
      $filename = $this->buildFilename($uid);

      if ($this->exists($filename)) {
         return $this->publicPath . $filename;
      }
      return $this->create($uid, $username);
   }

   /**
    * Crea un avatar automáticamente y devuelve su URL.
    */
   public function create(int $uid, string $username): string {
      $filename = $this->buildFilename($uid);
      $target   = $this->storagePath . $filename;
      $avatarUrl = $this->buildAvatarUrl($username);
      if (!$this->download($avatarUrl, $target)) {
         // Fallback simple: imagen por defecto
         return $this->publicPath . 'default.webp';
      }
      return $this->publicPath . $filename;
   }

   /**
    * Comprueba si el avatar existe.
    */
   private function exists(string $filename): bool {
      return is_file($this->storagePath . $filename);
   }

   /**
    * Construye el nombre del archivo.
    */
   private function buildFilename(int $uid): string {
      return sprintf('avatar_%d.webp', $uid);
   }

   /**
    * Genera la URL del servicio de avatars.
    */
   private function buildAvatarUrl(string $username): string {
      $username = trim($username);
      $username = mb_substr($username, 0, 50);

      return 'https://ui-avatars.com/api/?' . http_build_query([
         'name'       => $username,
         'background' => 'random',
         'size'       => 200,
         'font-size'  => 0.5,
         'bold'       => 'false',
         'length'     => 2,
         'format'     => 'webp',
      ]);
   }

   /**
    * Descarga el avatar de forma segura.
    */
   private function download(string $url, string $target): bool {
      $context = stream_context_create([
         'http' => [
            'timeout' => 5,
            'follow_location' => false,
         ],
      ]);

      $data = @file_get_contents($url, false, $context);
      if ($data === false) {
         return false;
      }

      return file_put_contents($target, $data) !== false;
   }
}