<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * PHPost - Application Configuration
 * ------------------------------------------------------------
 *
 * Configuración global de la aplicación.
 * Define políticas de comportamiento, seguridad y entorno.
 *
 * @package    Config
 * @author     Miguel92
 * @copyright  2026
 */

namespace config;

use config\AbstractConfig;

final class ConfigApplication extends AbstractConfig
{
    public function __construct()
    {
        $rootPath   = dirname(__DIR__);
        $filedev    = $rootPath . '/dev.local';
        $types      = ['development', 'staging', 'production', 'testing'];
        $APP_ENV    = $this->detectEnvironment($filedev, $types);
        $isLocalDev = ($APP_ENV === 'development' && file_exists($filedev));

        $isHttps    = $this->isHttps();
        $baseUrl    = $this->detectBaseUrl($isHttps);

        $this->items = [
            'app'           => $this->appConfig($APP_ENV, $isLocalDev),
            'php'           => $this->phpConfig(),
            'server'        => $this->serverConfig($isHttps, $baseUrl),
            'debug'         => $this->debugConfig($isLocalDev),
            'localization'  => $this->localizationConfig(),
            'logging'       => $this->loggingConfig(),
            'security'      => $this->securityConfig($APP_ENV, $types),
            'paths'         => $this->pathsConfig($rootPath),
        ];
    }

    private function appConfig(string $status, bool $develepment): array {
        return [
            'name'          => 'PHPost Risus',
            'slogan'        => 'Inteligencia recargada 2026',
            'status'        => $status,
            'development'   => $develepment,
            'minifyHTML'    => !$develepment,
            'description'   => 'Descubre nuestra plataforma completamente renovada. Actualizaciones constantes, nuevas funcionalidades y experiencia mejorada. En constante evolución para ofrecerte lo mejor.',
            // Metadatos para SEO y APIs
            'meta' => [
                'keywords' => 'plataforma, actualizada, renovada, refactorizada, desarrollo web, php, smarty',
                'author' => 'Miguel92',
                'robots' => $develepment ? 'noindex,nofollow' : 'index,follow',
            ],
            // Contacto
            'contact' => [
                'email' => 'portfoliomiguel92@gmail.com',
                'support_url' => 'https://discord.gg/StWZtrt2DE',
                'repository' => 'https://github.com/joelmiguelvalente/PHPost'
            ],
            ...$this->versions()
        ];
    }

    private function versions(): array
    {
        $version = file_get_contents(dirname(__DIR__, 1) . '/version');
        $arrayMap = array_map('intval', explode('.', $version));
        [$major, $minor, $patch] = array_pad($arrayMap, 3, 0);
        $versionCode = ($major * 10000) + ($minor * 100) + $patch;
        return [
            'version' => $version,
            'version_code' => $versionCode
        ];
    }

    private function phpConfig(): array {
        return [
            'version' => PHP_VERSION,
            'version_id' => PHP_VERSION_ID,
            'version_max' => 80500,
            'version_min' => 80300,
            'version_min_display' => '8.3.0',
            'extensions' => [
                'required' => ['curl', 'pdo', 'pdo_mysql', 'mbstring', 'json', 'gd'],
                'optional' => ['openssl', 'fileinfo', 'zip', 'exif']
            ],
        ];
    }

    private function serverConfig(bool $isHttps, string $baseUrl): array {
        $serverInfo = $this->getServerInfo();
        return [
            'os' => PHP_OS_FAMILY ?? PHP_OS,
            'hostname' => gethostname() ?: 'unknown',
            'software' => $serverInfo['software'],
            'ip' => $serverInfo['ip'],
            'https' => $isHttps,
            'base_url' => $baseUrl,
            'base_path' => dirname(__DIR__, 1),
            'doc_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
        ];
    }

    private function debugConfig(bool $develepment): array {
        return [
            // Niveles de reporte de errores - Compatible con PHP 8.4+
            'level' => [
                // Todos los errores excepto los obsoletos y notices (para producción)
                'production' => E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING,
                // Para desarrollo - Todos los errores
                'development' => E_ALL,
                // Para staging - Errores y warnings, pero no notices ni deprecated
                'staging' => E_ALL & ~E_DEPRECATED & ~E_NOTICE,
                // Sin reporte de errores
                'none' => 0
            ],
            'active' => $develepment,
            'display_errors' => $develepment,
            'log_errors' => true,
            'logs' => 'always',
            // Quiénes pueden ver errores en desarrollo
            'allowed_ips' => $develepment ? ['127.0.0.1', '::1'] : [],
            // Depuración de consultas SQL
            'sql_log' => $develepment,
            // Depuración de rendimiento
            'performance' => [
                'enabled' => $develepment,
                'slow_query_threshold' => 1.0, // segundos
                'memory_threshold' => 64 * 1024 * 1024, // 64MB
            ]
        ];
    }

    private function localizationConfig(): array {
        return [
            'timezone' => 'America/Argentina/Buenos_Aires',
            'locale' => 'es_AR',
            'charset' => 'UTF-8',
            'lang' => 'es',
        ];
    }

    private function loggingConfig(): array {
        return [
            'default_channel' => 'app',
            'channels' => [
                'app'   => 'app',
                'auth'  => 'auth',
                'admin' => 'admin',
                'api'   => 'api',
                'php'   => 'php',
            ],
        ];
    }

    private function securityConfig(string $develepment, array $types): array {
        return [
            'password' => [
                'algorithm' => PASSWORD_ARGON2ID,
                'options' => [
                    'memory_cost' => 1 << 17, // 128 MB
                    'time_cost' => 4,
                    'threads' => 2,
                ],
                'min_length' => 8,
                'max_length' => 72,
                'require_mixed_case' => true,
                'require_numbers' => true,
                'require_special_chars' => false,
                'history' => 5,
                'expiry_days' => 90,
            ],
            'token' => [
                'session_key' => 'csrf_token',
                'bytes' => 32, // random_bytes(32)
                'lifetime' => 3600, // 1 hora
                'regenerate_on_login' => true,
                'validate_on' => ['POST', 'PUT', 'PATCH', 'DELETE'],
                'exempt_actions' => [], // acciones que pasan sin token (vacío por defecto)
            ],
            'session' => [
                'name'     => 'phpost_session',
                'lifetime' => 0,
                'secure'   => in_array($develepment, $types, true),
                'httponly' => true,
                'samesite' => 'Lax',
                'gc_maxlifetime' => 1440, // 24 minutos
                'validate_ip' => false,
                'validate_user_agent' => true,
                'regenerate_interval' => 1800, // 30 minutos
                'save_path' => null
            ],
            'login_throttle' => [
                'ip_max_attempts' => 20,
                'ip_window_seconds' => 900,
                'user_max_attempts' => 5,
                'user_window_seconds' => 600,
                'global_max_attempts' => 100,
                'global_window_seconds' => 3600,
                'lockout_duration_seconds' => 1800
            ]
        ];
    }

    private function pathsConfig(string $rootPath): array {
        $origin = dirname(__DIR__, 1) . '/storage';
        return [
            'avatar' => [
                'full_path' => $origin . '/avatar',
                'short_path' => '../storage/avatar',
                'chmod' => 755
            ],
            'backups' => [
                'full_path' => $origin . '/backups',
                'short_path' => '../storage/backups',
                'chmod' => 750
            ],
            'cache' => [
                'full_path' => $origin . '/cache',
                'short_path' => '../storage/cache',
                'chmod' => 755
            ],
            'logs' => [
                'full_path' => $origin . '/logs',
                'short_path' => '../storage/logs',
                'chmod' => 750
            ],
            'media' => [
                'full_path' => $origin . '/media',
                'short_path' => '../storage/media',
                'chmod' => 755
            ],
            'uploads' => [
                'full_path' => $origin . '/uploads',
                'short_path' => '../storage/uploads',
                'chmod' => 755
            ]
        ];
    }

    /**
     * Detectar el entorno automáticamente
    */
    private function detectEnvironment(string $filedev, array $types): string
    {
        // Verificar variable de entorno
        $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV');
        if (is_string($appEnv) && in_array($appEnv, $types, true)) {
            return $appEnv;
        }

        // Detectar por archivo .env o .local
        if (file_exists($filedev)) {
            return 'development';
        }

        // Detectar por dominio
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_contains($host, '::1')) {
            return 'development';
        }
        if (str_contains($host, 'dev.') || str_contains($host, 'test.')) {
            return 'development';
        }
        if (str_contains($host, 'staging.') || str_contains($host, 'stage.')) {
            return 'staging';
        }
        return 'production';
    }

    /**
     * Detectar si la conexión es HTTPS
     */
    private function isHttps(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        return false;
    }

    /**
     * Detectar URL base automáticamente
     */
    private function detectBaseUrl(bool $isHttps): string
    {
        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL');
        if (!empty($appUrl)) {
            return rtrim($appUrl, '/');
        }

        $protocol = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = ($basePath === '/' || $basePath === '\\') ? '' : $basePath;

        return $protocol . $host . $basePath;
    }

   /**
    * Obtener información del servidor
    */
    private function getServerInfo(): array
    {
        $software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        // Detectar IP del servidor
        $ip = 'Unknown';
        if (function_exists('gethostbyname')) {
            $ip = gethostbyname(gethostname() ?: 'localhost');
        }

        // Añadir soporte para IPv6
        if ($ip === gethostname() || $ip === '127.0.0.1' || $ip === '::1') {
            $ip = $_SERVER['SERVER_ADDR'] ?? $ip;
        }

        return [
            'software' => $software,
            'ip' => $ip,
        ];
    }
}
