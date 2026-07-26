<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Html
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class Html {

    public static function escape(string $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE): string {
        return htmlspecialchars($value, $flags, 'UTF-8', false);
    }

    public static function escapeAttr(string $value): string {
        return self::escape($value, ENT_QUOTES);
    }

    public static function raw(string $value): HtmlRaw {
        return new HtmlRaw($value);
    }

    public static function empty(?string $value = null, ?string $content = null): string {
        if ($content !== null) {
            return empty($value) ? $content : $value;
        }
        return ($value !== null) ? $value : '';
    }

}
