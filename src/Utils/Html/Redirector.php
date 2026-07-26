<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Html
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class Redirector {

    private array $allowedHosts = [];

    private array $allowedPaths = ['/', '/login', '/registro', '/cuenta/', '/admin/', '/moderacion/', '/posts/', '/perfil/', '/mensajes/', '/portal'];

    public function __construct(private string $baseUrl) {
        $this->allowedHosts[] = parse_url($baseUrl, PHP_URL_HOST);
    }

    public function to(string $url, int $code = 302): never {
        $parsed = parse_url($url);
        $location = $this->baseUrl . '/' . ltrim($url, '/');
        // URL relativa → segura
        if (!isset($parsed['host'])) {
            $this->validatePath($url);
            Container::get(Response::class)->redirect($location, $code);
            exit;
        }

        // URL absoluta → validar host
        if (!in_array($parsed['host'], $this->allowedHosts, true)) {
            throw new \InvalidArgumentException("Redirect host not allowed: {$parsed['host']}");
        }
        $this->validatePath($parsed['path'] ?? '/');
        Container::get(Response::class)->redirect($location, $code);
        exit;
    }

    private function validatePath(string $path): void {
        $allowed = false;
        foreach ($this->allowedPaths as $allowedPath) {
            if (str_starts_with($path, $allowedPath)) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            throw new \InvalidArgumentException("Redirect path not allowed: $path");
        }
    }
}
