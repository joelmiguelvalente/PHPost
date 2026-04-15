<?php

/**
 * @name src/Utils/Compat.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

class Compat
{
    /**
     * curl_close() no hace nada desde PHP 8.0 y fue eliminada en 8.5
     */
    public static function curl_close(\CurlHandle|null $handle): void
    {
        if (PHP_VERSION_ID < 80500 && $handle !== null) {
            curl_close($handle);
        }
    }

    /**
     * imagedestroy() no hace nada desde PHP 8.0 y fue eliminada en 8.5
     */
    public static function imagedestroy(\GdImage|null $image): void
    {
        if (PHP_VERSION_ID < 80500 && $image !== null) {
            imagedestroy($image);
        }
    }
}
