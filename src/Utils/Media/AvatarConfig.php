<?php

declare(strict_types=1);

/**
 * @package    PHPost/Utils
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class AvatarConfig
{
    /**
     * Variantes de avatar disponibles.
     * Clave = nombre variante, Valor = tamaño en píxeles (cuadrado).
     */
    public const VARIANTS = [
        'avatar' => 200,
        'thumb'  => 60,
    ];

    /**
     * Formatos de salida soportados (orden de preferencia).
     * AVIF requiere PHP 8.1+ con GD; WebP requiere PHP 5.5+ con GD.
     */
    public const FORMATS = [
        'avif',
        'webp',
        'png',
    ];

    /**
     * Calidad por formato (0-100 para webp/avif, 0-9 para png).
     * Valores alineados con el uso real en Avatar.php.
     */
    public const QUALITY = [
        'avif' => 80,
        'webp' => 85,
        'png'  => 9,
    ];

    /**
     * Color por defecto para avatars generados (hex sin #).
     * Usado en registro de usuarios cuando no hay preferencia.
     */
    public const DEFAULT_COLOR = '171717';

    /**
     * URL base de la API de DiceBear v10.
     */
    public const AVATAR_API = 'https://api.dicebear.com/10.x';

    /**
     * Si tiene un estilo definido, solo usara este.
     */
    public const AVATAR_STYLE = 'rings';

    /**
     * Estilos de avatar soportados por DiceBear.
     * El estilo se selecciona de forma determinística según el usuario.
     * https://www.dicebear.com/styles/
     */
    public const STYLES = [
        'adventurer-neutral',
        'avataaars-neutral',
        'bottts',
        'bottts-neutral',
        'fun-emoji',
        'glyphs',
        'identicon',
        'initials',
        'micah',
        'notionists-neutral',
        'personas',
        'pixel-art',
        'pixel-art-neutral',
        'rings',
        'shape-grid',
        'shapes',
        'thumbs',
    ];

    /**
     * TTL de caché local para avatars descargados (segundos).
     * 30 días = 2592000
     */
    public const CACHE_TTL = 2592000;

    /** @var array<string, bool> Cache de soporte de formatos */
    private static array $formatSupportCache = [];

    /**
     * Selecciona un estilo de avatar para un usuario.
     * Si AVATAR_STYLE tiene contenido, todos los usuarios usan ese estilo.
     * Si está vacío, selecciona de forma determinística según el username.
     *
     * @param int    $uid      ID del usuario
     * @param string $username Nombre de usuario
     * @return string Nombre del estilo de DiceBear
     */
    public static function getStyle(int $uid, string $username): string
    {
        if (self::AVATAR_STYLE !== '') {
            return self::AVATAR_STYLE;
        }

        $index = crc32(strtolower($username)) % count(self::STYLES);
        return self::STYLES[$index];
    }

    /**
     * Directorio base de almacenamiento para un usuario.
     */
    public static function baseDir(int $userId, string $filename = ''): string
    {
        return TS_STORAGE . "/avatar/user_{$userId}/{$filename}";
    }

    /**
     * Nombre de archivo estándar para una variante y formato.
     */
    public static function variantFilename(string $variant, string $format): string
    {
        if (!array_key_exists($variant, self::VARIANTS)) {
            throw new InvalidArgumentException("Variante de avatar inválida: {$variant}");
        }
        return $variant === 'avatar' ? "avatar.{$format}" : "thumb_avatar.{$format}";
    }

    /**
     * Ruta completa (filesystem) para una variante/formato de un usuario.
     */
    public static function fullPath(int $userId, string $variant, string $format): string
    {
        return self::baseDir($userId) . self::variantFilename($variant, $format);
    }

    /**
     * Verifica si un formato está soportado por la instalación actual de GD.
     * Usa caché estática para evitar llamadas repetidas a function_exists().
     */
    public static function supportsFormat(string $format): bool
    {
        if (!isset(self::$formatSupportCache[$format])) {
            self::$formatSupportCache[$format] = match ($format) {
                'avif' => function_exists('imageavif'),
                'webp' => function_exists('imagewebp'),
                'png'  => true,
                default => false,
            };
        }
        return self::$formatSupportCache[$format];
    }

    /**
     * Devuelve solo los formatos soportados por el entorno actual,
     * en orden de preferencia (AVIF > WebP > PNG).
     */
    public static function getSupportedFormats(): array
    {
        return array_filter(self::FORMATS, [self::class, 'supportsFormat']);
    }

    /**
     * Devuelve el formato preferido disponible (el primero soportado).
     * Ideal para guardar una sola copia "best effort".
     */
    public static function getPrimaryFormat(): string
    {
        foreach (self::FORMATS as $format) {
            if (self::supportsFormat($format)) {
                return $format;
            }
        }
        return 'png'; // fallback garantizado
    }

    /**
     * Obtiene el tamaño en píxeles de una variante.
     */
    public static function getVariantSize(string $variant): int
    {
        return self::VARIANTS[$variant] ?? throw new InvalidArgumentException("Variante desconocida: {$variant}");
    }

    /**
     * Verifica si una variante existe.
     */
    public static function hasVariant(string $variant): bool
    {
        return array_key_exists($variant, self::VARIANTS);
    }

    /**
     * Limpia la caché de soporte de formatos (útil en tests).
     */
    public static function clearFormatCache(): void
    {
        self::$formatSupportCache = [];
    }
}
