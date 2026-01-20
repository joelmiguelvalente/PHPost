<?php

/**
 * @package    PHPost
 * @author     Miguel92
 * @copyright  2026
 * @version    2.1.0
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class Avatar {

   /**
    * Variantes semánticas y sus tamaños reales
    */
   private const VARIANTS = [
      'avatar' => 200,
      'thumb'  => 60,
   ];

   /**
    * Formatos generados
    */
   private const FORMATS = ['webp', 'avif', 'png'];

   private tsCore $core;
   private string $storageBase;
   private string $publicBase;

   public function __construct(bool $install = false, string $baseUrl = '') {
      global $tsCore;

      if(!$install) $this->core        = $tsCore;
      $this->storageBase = TS_STORAGE . 'avatar/';
      $this->publicBase  = $install ? $baseUrl : $this->core->route('storage:avatar') . '/';
   }

   /**
    * Garantiza que todas las variantes del avatar existan.
    * Se usa en el registro o bootstrap del usuario.
    */
   public function ensure(int $uid, string $username, string|int $color): void {
      if ($this->hasAllVariants($uid)) {
         return;
      }
      $this->create($uid, $username, $color);
   }

   /**
    * Devuelve la URL pública del avatar solicitado.
    */
   public function get(int $uid, string $variant = 'avatar', string $format = 'webp'): string {
      $path = $this->buildPath($uid, $variant, $format);
      if (is_file($path['fs'])) {
         return $path['url'];
      }
      // Fallback por formato
      return $this->publicBase . 'default/' . $this->variantFilename($variant, 'png');
   }

   /**
    * Verifica si existen todas las variantes y formatos.
    */
   private function hasAllVariants(int $uid): bool {
      foreach (self::VARIANTS as $variant => $size) {
         foreach (self::FORMATS as $format) {
            $file = $this->storageBase . "user_{$uid}/" . $this->variantFilename($variant, $format);
            if (!is_file($file)) {
               return false;
            }
         }
      }
      return true;
   }

   /**
    * Crea todas las variantes del avatar.
    */
   private function create(int $uid, string $username, string|int $color): void {
      $userDir = $this->storageBase . 'user_' . $uid . '/';
      if (!is_dir($userDir) && !mkdir($userDir, 0755, true)) {
         return;
      }
      $baseImage = $this->downloadBase($username, $color);
      if (!$baseImage) {
         return;
      }
      foreach (self::VARIANTS as $variant => $size) {
         $resized = $this->resize($baseImage, $size, $size);
         foreach (self::FORMATS as $format) {
            $filename = $this->variantFilename($variant, $format);
            $this->save($resized, $userDir . $filename, $format);
         }
         imagedestroy($resized);
      }
      imagedestroy($baseImage);
   }

   private function normalizeHexColor(string|int $color): string {
      $color = ltrim((string)$color, '#');
      if (preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
         return strtolower($color);
      }
      // fallback seguro
      return '777777';
   }

   private function contrastColor(string $hex): string {
      $hex = ltrim($hex, '#');
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      // luminancia relativa
      $luminance = (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
      // umbral razonable
      return $luminance < 128 ? 'ffffff' : '000000';
   }

   /**
    * Descarga la imagen base desde el servicio de avatars.
    */
   private function downloadBase(string $username, string|int $color): ?GdImage {
      $background = empty($color) ? 'random' : $this->normalizeHexColor($color);
      $textColor = $background === 'random' ? 'ffffff' : $this->contrastColor($background);
      $url = 'https://ui-avatars.com/api/?' . http_build_query([
         'name'       => mb_substr(trim($username), 0, 50),
         'size'       => 200,
         'format'     => 'png',
         'length'     => 2,
         'background' => $background,
         'color'      => $textColor
      ]);
      $context = stream_context_create([
         'http' => [
            'timeout' => 5,
            'follow_location' => false,
         ],
      ]);
      $data = @file_get_contents($url, false, $context);
      if ($data === false) {
         return null;
      }
      return @imagecreatefromstring($data) ?: null;
   }

   /**
    * Redimensiona una imagen.
    */
   private function resize(GdImage $src, int $width, int $height): GdImage {
      $dst = imagecreatetruecolor($width, $height);
      imagealphablending($dst, false);
      imagesavealpha($dst, true);
      imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
      return $dst;
   }

   /**
    * Guarda una imagen según el formato.
    */
   private function save(GdImage $img, string $path, string $format): void {
      match ($format) {
         'webp' => imagewebp($img, $path, 85),
         'avif' => imageavif($img, $path, 80),
         'png'  => imagepng($img, $path),
         default => null,
      };
   }

   /**
    * Construye el nombre de archivo según la variante.
    */
   private function variantFilename(string $variant, string $format): string {
      if (!array_key_exists($variant, self::VARIANTS)) {
         throw new InvalidArgumentException(
            "Variante de avatar inválida: {$variant}"
         );
      }
      return $variant === 'avatar' ? "avatar.{$format}" : "thumb_avatar.{$format}";
   }

   /**
    * Construye paths filesystem y URL pública.
    */
   private function buildPath(int $uid, string $variant, string $format): array {
      $filename = $this->variantFilename($variant, $format);
      $relative = "user_{$uid}/{$filename}";

      return [
         'fs'  => $this->storageBase . $relative,
         'url' => $this->publicBase . $relative,
      ];
   }
}