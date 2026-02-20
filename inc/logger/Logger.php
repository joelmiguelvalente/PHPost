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

   public static function log(string $message, LogLevel $level = LogLevel::INFO, array $context = [], ?string $channel = null): void {
      $channel ??= Config::app('logging.default_channel');
      $file = self::buildFilePath($channel);
      $line = LoggerFormatter::format($level, $message, $context);
      error_log($line, 3, $file);
   }

   public static function exception(Throwable $e, ?string $channel = 'php'): void {
      self::log($e->getMessage(), LogLevel::EXCEPTION, [
         'file'  => self::relativePath($e->getFile()),
         'line'  => $e->getLine(),
         'trace' => $e->getTraceAsString(),
      ], $channel );
   }

   public static function warning(string $msg, array $ctx = []): void {
      self::log($msg, LogLevel::WARNING, $ctx);
   }

   public static function error(string $msg, array $ctx = []): void {
      self::log($msg, LogLevel::ERROR, $ctx);
   }

   public static function info(string $msg, array $ctx = []): void {
      self::log($msg, LogLevel::INFO, $ctx);
   }

   public static function debug(string $msg, array $ctx = []): void {
      self::log($msg, LogLevel::DEBUG, $ctx);
   }

   private static function buildFilePath(string $channel): string {
      $now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
      $filenameDate = $now->format('d_m_Y');
      $dir = Config::app('paths.logs');
      if (!is_dir($dir)) {
         mkdir($dir, 0777, true);
      }
      return "{$dir}/{$channel}-{$filenameDate}.log";
   }

   private static function relativePath(string $path): string {
      return str_replace(TS_ROOT, '..', $path);
   }
}
