<?php

/**
 * @name src/Extras/QueryString.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/**
 * Limpieza y validación básica de solicitudes HTTP
 * Clase independiente del core
 */
final class LimpiarSolicitud
{
    public function limpiar(): void
    {
        $this->protegerGlobals();
        $this->validarClavesNumericas();
        $this->limpiarCookiesInvalidas();
        $this->validarQueryString();
        $this->revertirMagicQuotes();
        $this->sanitizarInputLegacy();
        $this->reconstruirRequest();
        $this->validarReferer();
    }

    private function protegerGlobals(): void
    {
        if (isset($_REQUEST['GLOBALS']) || isset($_COOKIE['GLOBALS'])) {
            throw new RuntimeException('Variable de solicitud no válida');
        }
    }

    private function validarClavesNumericas(): void
    {
        $sources = [$_POST, $_GET, $_FILES];

        foreach ($sources as $source) {
            foreach (array_keys($source) as $key) {
                if (is_numeric($key)) {
                    throw new RuntimeException('Las claves de solicitud numéricas no son válidas');
                }
            }
        }
    }

    private function limpiarCookiesInvalidas(): void
    {
        foreach ($_COOKIE as $key => $value) {
            if (is_numeric($key)) {
                unset($_COOKIE[$key]);
            }
        }
    }

    private function validarQueryString(): void
    {
        $_SERVER['QUERY_STRING'] ??= getenv('QUERY_STRING') ?: '';

        if (str_starts_with($_SERVER['QUERY_STRING'], 'http')) {
            http_response_code(400);
            exit;
        }
    }

    private function revertirMagicQuotes(): void
    {
        if (!function_exists('get_magic_quotes_gpc') || !get_magic_quotes_gpc()) {
            return;
        }

        $_ENV    = $this->stripslashesRecursive($_ENV);
        $_POST   = $this->stripslashesRecursive($_POST);
        $_COOKIE = $this->stripslashesRecursive($_COOKIE);

        foreach ($_FILES as $k => $file) {
            if (isset($file['name'])) {
                $_FILES[$k]['name'] = stripslashes($file['name']);
            }
        }
    }

    /**
     * LEGACY: sanitización temprana (NO recomendada hoy)
     */
    private function sanitizarInputLegacy(): void
    {
        $_GET    = $this->htmlspecialcharsRecursive($_GET);
        $_POST   = $this->htmlspecialcharsRecursive($_POST);
        $_COOKIE = $this->htmlspecialcharsRecursive($_COOKIE);
    }

    private function reconstruirRequest(): void
    {
        $_REQUEST = $_POST + $_GET;
    }

    private function validarReferer(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host    = $_SERVER['HTTP_HOST'] ?? '';
        $page    = $_GET['do'] ?? '';

        if ($referer === '' || $host === '') {
            return;
        }

        $cleanReferer = preg_replace('/https?:\/\/|www\./', '', $referer);
        $cleanHost    = preg_replace('/https?:\/\/|www\./', '', $host);

        $esMio = str_starts_with($cleanReferer, $cleanHost);

        $protegidas = ['admin', 'moderacion', 'cuenta'];

        if (
            (!$esMio && in_array($page, $protegidas, true)) ||
            ($_SERVER['REQUEST_METHOD'] === 'POST' && !$esMio)
        ) {
            throw new RuntimeException('Invalid request');
        }
    }

    private function htmlspecialcharsRecursive(mixed $var, int $level = 0): mixed
    {
        if (!is_array($var)) {
            return htmlspecialchars((string)$var, ENT_QUOTES, 'UTF-8');
        }

        foreach ($var as $k => $v) {
            $var[$k] = $level > 25 ? null : $this->htmlspecialcharsRecursive($v, $level + 1);
        }

        return $var;
    }

    private function stripslashesRecursive(mixed $var, int $level = 0): mixed
    {
        if (!is_array($var)) {
            return stripslashes((string)$var);
        }

        $clean = [];
        foreach ($var as $k => $v) {
            $clean[stripslashes((string)$k)] =
                $level > 25 ? null : $this->stripslashesRecursive($v, $level + 1);
        }

        return $clean;
    }
}
