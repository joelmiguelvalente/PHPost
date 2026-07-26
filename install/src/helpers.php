<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
*/

function sanitizeInput(array $keys, string $method = 'POST'): array {
    $source = ($method === 'POST') ? $_POST : $_GET;
    $result = [];
    foreach ($keys as $key) {
        $value = trim($source[$key] ?? '');
        $result[$key] = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
    return $result;
}

/**
 * Genera slug a partir de texto
 */
function slugify(string $text, string $separator = '-'): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9]+/', $separator, $text);
    $text = preg_replace('/-+/', $separator, $text);
    $text = trim($text, $separator);
    return strtolower($text);
}

/**
 * Crea URL base del sitio
 */
function createURL(string $add = '', bool $withoutSlash = true): string {
    $protocol = 'http';
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
        $protocol .= 's';
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Obtener ruta base correctamente
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = dirname($scriptName, 2);
    $basePath = $basePath === DIRECTORY_SEPARATOR ? '' : $basePath;
    $basePath = str_replace(DIRECTORY_SEPARATOR, '/', $basePath);

    $siteUrl = $protocol . '://' . $host . $basePath;

    return $siteUrl . '/' . ltrim($add, '/');
}

/**
 * Genera token CSRF
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 */
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirige con mensaje de error
 */
function redirectWithError(string $message, string $step = 'bienvenida'): void {
    $_SESSION['install_error'] = $message;
    header('Location: index.php?step=' . $step);
    exit;
}

$errors = [];
$error = null;
$success = true;
