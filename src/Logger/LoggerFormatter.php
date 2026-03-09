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

   private static function severityLabel(int $severity): string {
      return match($severity) {
         E_ERROR             => 'E_ERROR (Fatal)',
         E_WARNING           => 'E_WARNING',
         E_NOTICE            => 'E_NOTICE',
         E_DEPRECATED        => 'E_DEPRECATED',
         E_USER_ERROR        => 'E_USER_ERROR',
         E_USER_WARNING      => 'E_USER_WARNING',
         E_USER_NOTICE       => 'E_USER_NOTICE',
         E_PARSE             => 'E_PARSE',
         default             => "Unknown ({$severity})"
      };
   }

   public static function format(LogLevel $level, string $message, array $context = []): string {
      $output  = "===| <SYSTEM_LOGGER /> |===\n";
      $output .= " > " . date('d.m.y H:i:s') . "\n";
      $output .= "{$level->value}: {$message}\n";
      foreach ($context as $key => $value) {
         if ($key === 'severity' && is_int($value)) {
            $output .= "severity: " . self::severityLabel($value) . "\n";
            continue;
         }
         if ($key === 'trace') {
            $output .= "trace:\n";
            foreach (explode("\n", (string)$value) as $line) {
               $output .= "\t{$line}\n";
            }
            continue;
         }
         if ($key === 'file') {
            $output .= "file: ";
            $output .= str_replace(TS_ROOT, "..", $value) . "\n";
            continue;
         }
         $output .= "{$key}: " . self::normalize($value) . "\n";
      }
      $output  .= "___________________________________________________\n";
      return $output;
   }

   private static function normalize(mixed $value): string {
      if (is_scalar($value) || $value === null) {
         return (string)$value;
      }
      return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
   }
}