<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Http
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class RequestGuard
{
    public function check(): void
    {
        $this->rejectGlobalsInjection();
        $this->rejectInvalidKeys();
        $this->cleanInvalidCookies();
        $this->validateRequestMethod();
        $this->validateContentLength();
        $this->validateQueryString();
        $this->validateCsrfToken();
    }

    private function rejectGlobalsInjection(): void
    {
        foreach (['_GET', '_POST', '_COOKIE', '_FILES', '_SERVER', '_ENV', 'GLOBALS'] as $reserved) {
            if (isset($_REQUEST[$reserved])) {
                throw new RuntimeException('Invalid request.');
            }
        }
    }

    private function rejectInvalidKeys(): void
    {
        foreach ([&$_GET, &$_POST, &$_COOKIE, &$_FILES] as &$source) {
            foreach (array_keys($source) as $key) {

                if (!is_string($key)) {
                    throw new RuntimeException('Invalid parameter name.');
                }

                if (preg_match('/[\x00-\x1F\x7F]/', $key)) {
                    throw new RuntimeException('Invalid parameter name.');
                }
            }
        }
    }

    private function cleanInvalidCookies(): void
    {
        foreach ($_COOKIE as $key => $value) {

            if (!is_string($key)) {
                unset($_COOKIE[$key]);
                continue;
            }

            if (preg_match('/[\x00-\x1F\x7F]/', $key)) {
                unset($_COOKIE[$key]);
            }
        }
    }

    private function validateRequestMethod(): void
    {
        $allowed = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', $allowed, true)) {
            throw new RuntimeException('Method not allowed.');
        }
    }

    private function validateContentLength(): void
    {
        $length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($length > 50 * 1024 * 1024) {
            throw new RuntimeException('Request too large.');
        }
    }

    private function validateQueryString(): void
    {
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if (str_contains($query, "\0")) {
            throw new RuntimeException('Invalid query string.');
        }
    }

    private function validateCsrfToken(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $validateOn = Config::app('security.token.validate_on') ?? ['POST'];

        if (!in_array($method, $validateOn, true)) {
            return;
        }

        $action = trim($_GET['action'] ?? '');
        $exempt = Config::app('security.token.exempt_actions') ?? [];

        if ($action !== '' && in_array($action, $exempt, true)) {
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!Container::get(CsrfToken::class)->validate((string) $token)) {
            http_response_code(419);
            header('Content-Type: text/html; charset=UTF-8');
            exit('0: Token CSRF inválido o expirado.');
        }
    }
}
