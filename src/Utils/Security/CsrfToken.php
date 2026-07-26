<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Security
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class CsrfToken
{
    private string $SESSION_KEY;
    private int $TOKEN_LENGTH;

    public function __construct() {
        $this->SESSION_KEY  = Config::app('security.token.session_key');
        $this->TOKEN_LENGTH = Config::app('security.token.bytes');
    }

    /**
     * Genera un nuevo token CSRF
     */
    public function generate(): string
    {
        if (empty($_SESSION[$this->SESSION_KEY])) {
            $_SESSION[$this->SESSION_KEY] = bin2hex(random_bytes($this->TOKEN_LENGTH));
        }
        return $_SESSION[$this->SESSION_KEY];
    }

    /**
     * Valida un token CSRF
     */
    public function validate(string $token): bool
    {
        return isset($_SESSION[$this->SESSION_KEY]) && hash_equals($_SESSION[$this->SESSION_KEY], $token);
    }

    /**
     * Regenera el token
     */
    public function regenerate(): string
    {
        $_SESSION[$this->SESSION_KEY] = bin2hex(random_bytes($this->TOKEN_LENGTH));
        return $_SESSION[$this->SESSION_KEY];
    }

    /**
     * Limpia el token
     */
    public function clear(): void
    {
        unset($_SESSION[$this->SESSION_KEY]);
    }
}
