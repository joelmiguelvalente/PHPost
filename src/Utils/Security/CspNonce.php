<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Security
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class CspNonce
{
    private static ?string $nonce = null;

    public static function generate(): string
    {
        self::$nonce = bin2hex(random_bytes(16));
        return self::$nonce;
    }

    public static function get(): string
    {
        return self::$nonce ??= bin2hex(random_bytes(16));
    }

    public static function reset(): void
    {
        self::$nonce = null;
    }
}
