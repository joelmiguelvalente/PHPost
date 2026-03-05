<?php

/**
 * @name Themes.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class Themes {

	public function __construct() {}

   /**
    * Extrae metadatos del comentario de estilo.css
    * @param string $content Contenido del archivo CSS
    * @return array|null Metadatos encontrados o null si no hay coincidencia
    */
   private function extractThemeMetadata(string $themeName, string $content): ?array {
      // Patrón para capturar todos los metadatos posibles en cualquier orden
      $pattern = '/\/\*\*\s*' .
      '(?:(?:\s*\*\s*@name\s+([^\n\r]+))?)' .
      '(?:(?:\s*\*\s*@path\s+([^\n\r]+))?)' .
      '(?:(?:\s*\*\s*@copy\s+([^\n\r]+))?)' .
      '(?:(?:\s*\*\s*@link\s+([^\n\r]+))?)' .
      '(?:(?:\s*\*\s*@name\s+([^\n\r]+))?' .
      '(?:\s*\*\s*@path\s+([^\n\r]+))?' .
      '(?:\s*\*\s*@copy\s+([^\n\r]+))?' .
      '(?:\s*\*\s*@link\s+([^\n\r]+))?)*' .
      '\s*\*\//is';

      // Alternativa más limpia: buscar cada etiqueta individualmente
      $nameMatch = [];
      $pathMatch = [];
      $copyMatch = [];
      $linkMatch = [];

      preg_match('/\*\s*@name\s+([^\n\r]+)/i', $content, $nameMatch);
      preg_match('/\*\s*@path\s+([^\n\r]+)/i', $content, $pathMatch);
      preg_match('/\*\s*@copy\s+([^\n\r]+)/i', $content, $copyMatch);
      preg_match('/\*\s*@link\s+([^\n\r]+)/i', $content, $linkMatch);

      $metadata = [];
      if (isset($nameMatch[1])) {
         $metadata['t_name'] = trim($nameMatch[1]);
      }
      $pathValue = isset($pathMatch[1]) ? trim($pathMatch[1]) : $themeName;
      $metadata['t_path'] = $pathValue;

      if (isset($copyMatch[1])) {
         $metadata['t_copy'] = trim($copyMatch[1]);
      }
      if (isset($linkMatch[1])) {
         $metadata['t_link'] = trim($linkMatch[1]);
      }
      $metadata['t_screen'] = "/themes/{$metadata['t_path']}/screenshot.png";

      // Si no se encontró al menos un campo obligatorio (name o copy), retornar valores por defecto
      if (empty($metadata) || (!isset($metadata['t_name']) && !isset($metadata['t_copy']))) {
         // Valores por defecto
         return [
            't_name' => ucfirst($themeName),
            't_path' => $themeName, // Fallback: nombre del directorio
            't_copy' => 'unknown',
            't_link' => '#',
            't_screen' => "/themes/{$themeName}/screenshot.png"
         ];
      }

      // Retornar el array con los metadatos extraídos o generados
      return $metadata;
   }

   /**
    * Ya no requerimos que este instalado
    */
   public function getTemas() {
      $themes = scandir(TS_THEMES);
      $resultados = [];
      foreach ($themes as $themeName) {
         if (in_array($themeName, ['.', '..'], true)) continue;
         //
         $themePath = TS_THEMES . '/' . $themeName;
         if (!is_dir($themePath)) continue;
         // Obtenemos el archivo que necesitamos
         $cssFile = $themePath . '/estilo.css';
         // Verificar que el archivo exista y sea legible
         if (!is_readable($cssFile)) continue;
         // Leer contenido del archivo
         $content = file_get_contents($cssFile);
         if ($content === false) {
            error_log("No se pudo leer el archivo CSS: {$cssFile}");
            continue;
         }
         // Extraer metadatos del comentario
         $metadata = $this->extractThemeMetadata($themeName, $content);
         if ($metadata !== null) {
            $resultados[] = $metadata;
         }
      }
      return $resultados;
   }

	public function getAllThemes(): array {
		return $this->getTemas();
	}

	public function getUserThemeUse(?int $uid = 0, string $theme = 'default') {
		$data = DB::fetch("SELECT user_theme FROM u_miembros_sets WHERE user_id = :uid", ['uid' => $uid]);
		return $data['user_theme'] ?? $theme;
	}

}