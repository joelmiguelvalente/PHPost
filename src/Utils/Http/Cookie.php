<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Http
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class Cookie
{

    private bool $secure;

    private bool $httpOnly;

    private string $sameSite;

    private string $path;

    private string $domain;

    public function __construct()
    {
        $this->secure    = Config::app('security.cookie.secure');
        $this->httpOnly  = Config::app('security.cookie.httponly');
        $this->sameSite  = Config::app('security.cookie.samesite');
        $this->path      = Config::app('security.cookie.path');
        $this->domain    = Config::app('security.cookie.domain');
    }

    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $_COOKIE[$name] ?? $default;
    }

    public function set(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        ?bool $secure = null,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): bool {

        $secure ??= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        $result = setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        if ($result) {
            $_COOKIE[$name] = $value;
        }

        return $result;
    }

    public function delete(string $name, string $path = '/', string $domain = ''): bool {
        unset($_COOKIE[$name]);
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path'    => $path,
            'domain'  => $domain,
        ]);
    }

    public function all(): array
    {
        return $_COOKIE;
    }

    public function clear(): void
    {
        foreach (array_keys($_COOKIE) as $cookie) {
            $this->delete($cookie);
        }
    }
}
