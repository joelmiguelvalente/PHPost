<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * InstallerLogger
 * ------------------------------------------------------------
 * Logging estructurado durante la instalación.
 * Escribe en un archivo local dentro del directorio install/.
 *
 * @package    PHPost
 * @subpackage Install\src
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class InstallerLogger
{
    private string $logFile;

    public function __construct(string $logDir)
    {
        $this->logFile = rtrim($logDir, '/') . '/install.log';
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    // ── Privado ──────────────────────────────────────────────

    private function write(string $level, string $message, array $context): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ctx       = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line      = "[{$timestamp}] [{$level}] {$message}{$ctx}" . PHP_EOL;

        // Silencioso: el logger no debe interrumpir la instalación
        @file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
