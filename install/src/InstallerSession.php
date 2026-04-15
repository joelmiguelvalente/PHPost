<?php

/**
 * ------------------------------------------------------------
 * InstallerSession
 * ------------------------------------------------------------
 * Gestiona el estado de la instalación en sesión.
 * Garantiza que no se pueda saltar pasos.
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

final class InstallerSession
{
    private const KEY_ACCEPTED  = 'install_license_accepted';
    private const KEY_COMPLETED = 'install_completed_steps';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ── Licencia ─────────────────────────────────────────────

    public function acceptLicense(): void
    {
        $_SESSION[self::KEY_ACCEPTED] = true;
    }

    public function hasAcceptedLicense(): bool
    {
        return ($_SESSION[self::KEY_ACCEPTED] ?? false) === true;
    }

    // ── Pasos completados ────────────────────────────────────

    public function markCompleted(string $step): void
    {
        $steps = $this->getCompleted();
        $steps[$step] = true;
        $_SESSION[self::KEY_COMPLETED] = $steps;
    }

    public function isCompleted(string $step): bool
    {
        return isset($this->getCompleted()[$step]);
    }

    /**
     * Comprueba si el paso anterior requerido fue completado.
     * Devuelve false si el usuario intenta saltarse el flujo.
     */
    public function canAccess(string $step, array $flow): bool
    {
        if (!$this->hasAcceptedLicense()) {
            return false;
        }

        $pos = array_search($step, $flow, true);

        // El primer paso siempre es accesible tras aceptar la licencia
        if ($pos === 0 || $pos === false) {
            return true;
        }

        $previous = $flow[$pos - 1];
        return $this->isCompleted($previous);
    }

    // ── Destrucción ──────────────────────────────────────────

    public function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    // ── Privado ──────────────────────────────────────────────

    private function getCompleted(): array
    {
        return $_SESSION[self::KEY_COMPLETED] ?? [];
    }
}
