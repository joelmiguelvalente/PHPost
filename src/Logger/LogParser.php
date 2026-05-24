<?php

declare(strict_types=1);

/**
 * @package    PHPost/Logger
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class LogParser {

   /**
    * Parsea un archivo .log y devuelve un array de entradas
    */
   public static function parseFile(string $filepath): array {
      if (!file_exists($filepath)) {
         return [];
      }
      $content = file_get_contents($filepath);
      return self::parseContent($content);
   }

   /**
    * Devuelve todos los archivos .log disponibles en el directorio
    */
   public static function getAvailableFiles(string $logsDir): array {
      if (!is_dir($logsDir)) {
         return [];
      }
      $files = glob($logsDir . '/*.log');
      if (!$files) {
         return [];
      }
      $result = [];
      foreach ($files as $path) {
         $filename = basename($path, '.log');
         if (preg_match('/^(.+)-(\d{2}_\d{2}_\d{4})$/', $filename, $m)) {
            $dateFormatted = str_replace('_', '/', $m[2]);
            $result[] = [
               'channel'  => $m[1],
               'date'     => $m[2],
               'label'    => strtoupper($m[1]) . ' — ' . $dateFormatted,
               'filename' => basename($path),
               'path'     => $path,
               'size'     => filesize($path),
            ];
         }
      }
      usort($result, fn($a, $b) => strcmp($b['date'], $a['date']));
      return $result;
   }

   /**
    * Elimina una entrada del archivo .log identificándola por su línea de fecha.
    * $datetime viene como "11:37:00 | 08.03.26" — se reconstruye a " > 08.03.26 11:37:00"
    */
   public static function deleteEntry(string $filepath, string $datetime): bool {
      if (!file_exists($filepath)) {
         return false;
      }

      $parts = explode(' | ', $datetime);
      if (count($parts) !== 2) {
         return false;
      }
      $searchLine = ' > ' . trim($parts[1]) . ' ' . trim($parts[0]);

      $content  = file_get_contents($filepath);
      $blocks   = preg_split('/^_{48,}\n?/m', $content);
      $filtered = [];

      foreach ($blocks as $block) {
         $block = trim($block);
         if (empty($block)) continue;
         if (str_contains($block, $searchLine)) continue;
         $filtered[] = $block;
      }

      $separator = "\n___________________________________________________\n";
      $result    = implode($separator, $filtered);
      if (!empty($result)) {
         $result .= "\n___________________________________________________\n";
      }

      return file_put_contents($filepath, $result) !== false;
   }

   /**
    * Parsea el contenido crudo del log en entradas estructuradas
    */
   private static function parseContent(string $content): array {
      $entries = [];
      $blocks  = preg_split('/^_{48,}\n/m', $content);
      foreach ($blocks as $block) {
         $block = trim($block);
         if (empty($block)) continue;
         $entry = self::parseBlock($block);
         if ($entry) {
            $entries[] = $entry;
         }
      }
      return array_reverse($entries);
   }

   private static function parseBlock(string $block): ?array {
      $lines = explode("\n", $block);
      $entry = [
         'datetime' => '',
         'level'    => 'INFO',
         'message'  => '',
         'file'     => null,
         'line'     => null,
         'severity' => null,
         'trace'    => null,
         'extra'    => [],
      ];

      $inTrace    = false;
      $traceLines = [];

      foreach ($lines as $raw) {
         // Header: ===| <SYSTEM_LOGGER /> |===
         if (preg_match('/^===\| <SYSTEM_LOGGER \/> \|===$/', $raw)) {
            $inTrace = false;
            continue;
         }

         // Fecha: " > 08.03.26 11:37:00"
         if (preg_match('/^ > ([\d.]+) ([\d:]+)$/', $raw, $m)) {
            $entry['datetime'] = $m[2] . ' | ' . $m[1];
            $inTrace = false;
            continue;
         }

         // Nivel: ERROR: mensaje
         if (preg_match('/^(INFO|ERROR|EXCEPTION|FATAL|WARNING|DEBUG):\s*(.+)$/', $raw, $m)) {
            $entry['level']   = $m[1];
            $entry['message'] = $m[2];
            $inTrace = false;
            continue;
         }

         // Trace inicio
         if (trim($raw) === 'trace:') {
            $inTrace = true;
            continue;
         }

         if ($inTrace) {
            $traceLines[] = ltrim($raw, "\t");
            continue;
         }

         // file:, line:, severity:, etc.
         if (preg_match('/^(\w+):\s*(.*)$/', $raw, $m)) {
            $key = $m[1];
            $val = $m[2];
            match($key) {
               'file'     => $entry['file']     = $val,
               'line'     => $entry['line']     = (int)$val,
               'severity' => $entry['severity'] = $val,
               default    => $entry['extra'][$key] = $val,
            };
         }
      }

      if (!empty($traceLines)) {
         $entry['trace'] = implode("\n", array_filter($traceLines));
      }

      if (empty($entry['datetime']) && empty($entry['message'])) {
         return null;
      }

      if ($entry['file'] && $entry['line']) {
         $entry['snippet'] = self::resolveCodeSnippet($entry['file'], $entry['line']);
      } else {
         $entry['snippet'] = null;
      }

      return $entry;
   }

   /**
    * Reconstruye la ruta absoluta desde la relativa y extrae el fragmento de código
    */
   private static function resolveCodeSnippet(string $relPath, int $errorLine, int $context = 1): ?array {
      $normalized   = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relPath);
      $normalized   = ltrim($normalized, '.' . DIRECTORY_SEPARATOR);
      $absolutePath = TS_ROOT . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);

      if (!file_exists($absolutePath)) {
         return null;
      }

      $allLines = file($absolutePath, FILE_IGNORE_NEW_LINES);
      if ($allLines === false) {
         return null;
      }

      $total = count($allLines);
      $from  = max(0, $errorLine - 1 - $context);
      $to    = min($total - 1, $errorLine - 1 + $context);

      $snippet = [];
      for ($i = $from; $i <= $to; $i++) {
         $snippet[] = [
            'number'  => $i + 1,
            'code'    => $allLines[$i],
            'isError' => ($i + 1) === $errorLine,
         ];
      }

      return $snippet;
   }
}
