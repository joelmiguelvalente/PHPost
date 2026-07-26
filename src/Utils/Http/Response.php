<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Http
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class Response
{
    private bool $sent = false;

    public function status(int $code): self
    {
        if (!$this->sent) {
            http_response_code($code);
        }
        return $this;
    }

    public function header(string $name, string $value, bool $replace = true): self
    {
        if (!$this->sent) {
            header("$name: $value", $replace);
        }
        return $this;
    }

    public function contentType(string $type, string $charset = 'UTF-8'): self
    {
        return $this->header('Content-Type', "{$type}; charset={$charset}");
    }

    public function noCache(): self
    {
        $this->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->header('Pragma', 'no-cache');
        $this->header('Expires', '0');
        return $this;
    }

    public function json(array|object $data, int $status = 200): never
    {
        $this->status($status)->contentType('application/json');
        $this->send(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function html(string $content, int $status = 200): never
    {
        $this->status($status)->contentType('text/html');
        $this->send($content);
    }

    public function text(string $content, int $status = 200): never
    {
        $this->status($status)->contentType('text/plain');
        $this->send($content);
    }

    public function redirect(string $url, int $status = 302): never
    {
        $this->status($status)->header('Location', $url);
        exit;
    }

    public function download(string $file, ?string $name = null): never
    {
        if (!is_file($file)) {
            $this->status(404);
            exit;
        }

        $name ??= basename($file);

        $this->header('Content-Type', 'application/octet-stream');
        $this->header('Content-Disposition', "attachment; filename=\"$name\"");
        $this->header('Content-Length', (string) filesize($file));

        readfile($file);

        exit;
    }

    public function send(string $content): never
    {
        $this->sent = true;
        echo $content;
        exit;
    }

    public function csp(?string $nonce = null): void
    {
        if (!$this->sent) {
            $nonce ??= $GLOBALS['csp_nonce'] ?? '';
            if (!empty($nonce)) {
                #$this->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-eval' 'wasm-unsafe-eval' 'unsafe-inline' 'nonce-{$nonce}' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https://www.google.com https://www.gstatic.com; img-src 'self' data: blob: https://api.dicebear.com; frame-src https://www.youtube.com https://www.youtube-nocookie.com; object-src 'none';", false);
            }
        }
    }
}
