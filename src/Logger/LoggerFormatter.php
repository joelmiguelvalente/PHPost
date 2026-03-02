<?php

/**
 * @name LoggerFormatter.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class LoggerFormatter {

   public static function format(LogLevel $level, string $message, array $context = []): string {
      $date = date('H:i:s | d.m.Y');
      $output  = "--------------------------------------------------\n";
      $output  = "Logger by Miguel92\n";
      $output  = "--------------------------------------------------\n";
      $output .= "[{$date}]\n";
      $output .= "{$level->value}: {$message}\n";
      foreach ($context as $key => $value) {
         if ($key === 'trace') {
            $output .= "trace:\n";
            foreach (explode("\n", (string)$value) as $line) {
               $output .= "\t{$line}\n";
            }
            continue;
         }
         $output .= "{$key}: " . self::normalize($value) . "\n";
      }
      return $output;
   }

   private static function normalize(mixed $value): string {
      if (is_scalar($value) || $value === null) {
         return (string)$value;
      }
      return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
   }
}