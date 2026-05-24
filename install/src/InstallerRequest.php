<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * InstallerRequest
 * ------------------------------------------------------------
 * Encapsula el input del instalador, sanitizando y tipando
 * cada valor de forma explícita.
 *
 * @package    PHPost
 * @subpackage Install\src
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class InstallerRequest
{
    private readonly string $method;

    public function __construct(
        private readonly array $post = [],
        private readonly array $get  = [],
    ) {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function fromGlobals(): self
    {
        return new self($_POST, $_GET);
    }

    // ── Método ───────────────────────────────────────────────

    public function isPost(): bool { return $this->method === 'POST'; }
    public function isGet(): bool  { return $this->method === 'GET'; }

    // ── GET ──────────────────────────────────────────────────

    public function getStep(): string
    {
        $step = $this->get['step'] ?? '';
        return is_string($step) ? trim($step) : '';
    }

    // ── POST helpers ─────────────────────────────────────────

    /**
     * Devuelve un string recortado del POST, o el default.
     */
    public function string(string $key, string $default = ''): string
    {
        $val = $this->post[$key] ?? $default;
        return is_string($val) ? trim($val) : $default;
    }

    /**
     * Devuelve un string SIN trim (contraseñas).
     */
    public function raw(string $key, string $default = ''): string
    {
        $val = $this->post[$key] ?? $default;
        return is_string($val) ? $val : $default;
    }

    /**
     * Comprueba si existe una clave en el POST (botones tipo "omitir").
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->post);
    }
}
