<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * InstallerValidator
 * ------------------------------------------------------------
 * Centraliza todas las validaciones del instalador.
 * Métodos estáticos puros: no dependen de estado externo.
 *
 * @package    PHPost
 * @subpackage Install
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class InstallerValidator
{
    // ─── SMTP ────────────────────────────────────────────────

    public static function smtpHost(string $host): bool
    {
        if ($host === '') return false;
        if (filter_var($host, FILTER_VALIDATE_IP)) return true;
        return (bool) preg_match(
            '/^(?=.{1,253}$)(?!-)([a-zA-Z0-9\-]{1,63}\.)+[a-zA-Z]{2,63}$/',
            $host
        );
    }

    public static function smtpUser(string $user): bool
    {
        if ($user === '') return false;
        if (str_contains($user, '@')) {
            return filter_var($user, FILTER_VALIDATE_EMAIL) !== false;
        }
        return (bool) preg_match('/^[a-zA-Z0-9._\-]{2,64}$/', $user);
    }

    public static function smtpPass(string $pass): bool
    {
        return strlen($pass) >= 6;
    }

    public static function smtpFrom(string $name): bool
    {
        return $name !== '' && mb_strlen($name) <= 100;
    }

    // ─── BASE DE DATOS ───────────────────────────────────────

    /**
     * Valida los campos de conexión a la base de datos.
     * En entornos locales, la contraseña es opcional.
     *
     * @param  array{hostname:string,username:string,password:string,database:string} $db
     * @param  bool $isLocal
     * @return string[] Lista de errores (vacía si todo es válido)
     */
    public static function dbFields(array $db, bool $isLocal = false): array
    {
        $errors = [];
        $required = ['hostname', 'username', 'database'];

        if (!$isLocal) {
            $required[] = 'password';
        }

        foreach ($required as $field) {
            if (($db[$field] ?? '') === '') {
                $errors[$field] = 'Este campo es obligatorio.';
            }
        }

        return $errors;
    }

    // ─── USUARIO ADMIN ───────────────────────────────────────

    public static function username(string $name): bool
    {
        return $name !== '' && ctype_alnum($name) && strlen($name) >= 3;
    }

    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function passwordsMatch(string $pass, string $confirm): bool
    {
        return hash_equals($pass, $confirm);
    }

    public static function passwordLength(string $pass, int $min = 8): bool
    {
        return strlen($pass) >= $min;
    }

    // ─── SITIO ───────────────────────────────────────────────

    /**
     * @param  array{titulo:string,slogan:string,email:string,url:string} $site
     * @return string[]
     */
    public static function siteFields(array $site): array
    {
        $errors = [];

        if (($site['titulo'] ?? '') === '') {
            $errors['titulo'] = 'El nombre del sitio es obligatorio.';
        }
        if (($site['slogan'] ?? '') === '') {
            $errors['slogan'] = 'El lema es obligatorio.';
        }
        if (!self::email($site['email'] ?? '')) {
            $errors['email'] = 'Ingresá un email válido.';
        }
        if (filter_var($site['url'] ?? '', FILTER_VALIDATE_URL) === false) {
            $errors['url'] = 'La URL no es válida. Asegurate de incluir http:// o https://';
        }

        return $errors;
    }

    // ─── HELPERS ─────────────────────────────────────────────

    /**
     * Escapa para mostrar en HTML (evita XSS en el formulario).
     */
    public static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Limpia y trimea un valor POST.
     */
    public static function post(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }
}
