<?php

/**
 * ------------------------------------------------------------
 * LockManager
 * ------------------------------------------------------------
 * Gestiona el archivo .lock que impide re-instalaciones.
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

final class LockManager
{
    private string $lockPath;

    public function __construct(string $rootDir)
    {
        $this->lockPath = rtrim($rootDir, '/') . '/.lock';
    }

    public function isLocked(): bool
    {
        return file_exists($this->lockPath);
    }

    public function lock(): void
    {
        if (file_put_contents($this->lockPath, 'Install success ' . date('Y-m-d H:i:s'), LOCK_EX) === false) {
            throw new RuntimeException('No se pudo crear el archivo de bloqueo.');
        }
    }

    public function unlock(): void
    {
        if ($this->isLocked()) {
            unlink($this->lockPath);
        }
    }
}
