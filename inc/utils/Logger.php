<?php

/**
 * @name Logger.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class Logger {
    
   public static function log(string $channel, string $level, string $message, array $context = []): void {
      $date = date('Y-m-d H:i:s');
      $file = Config::app('paths.logs') . "/{$channel}-" . date('Y-m-d') . ".log";
      $contextStr = $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
      $line = "[{$date}] {$level}: {$message} {$contextStr}\n";
      error_log($line, 3, $file);
   }

   public static function error(string $msg, array $ctx = []): void {
      self::log('app', 'ERROR', $msg, $ctx);
   }

   public static function info(string $msg, array $ctx = []): void {
      self::log('app', 'INFO', $msg, $ctx);
   }
}