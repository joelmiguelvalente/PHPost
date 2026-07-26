<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Misc
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class Extras {

	static public function slugify(string $text, string $separator = '-'): string {
		if ($text === '') {
			return '';
		}
		$text = preg_replace('~[^\pL\d]+~u', $separator, $text) ?? '';
		$text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
		$text = preg_replace('~[^-\w]+~', '', $text) ?? '';
		$text = trim($text, $separator);
		$text = preg_replace('~-+~', $separator, $text) ?? '';
		return strtolower($text);
	}

	/**
	 * Limpia BBCode y HTML, y trunca al número de caracteres indicado.
	 * Usar en lugar de: strip_tags($row['p_descripcion'])
	 *
	 * @param string $text      Texto crudo (puede tener BBCode y/o HTML)
	 * @param int    $maxLength Máximo de caracteres (0 = sin límite)
	 */
	static public function cleanText(string $text, int $maxLength = 0): string {
		$text = preg_replace('/\[([a-z]+)[^\]]*\](.*?)\[\/\1\]/is', '$2', $text);
		$text = preg_replace('/\[([a-z]+)[^\]]*\]/i', '', $text);
		$text = strip_tags($text);
		$text = trim(preg_replace('/\s+/', ' ', $text));
		if ($maxLength > 0 && mb_strlen($text) > $maxLength) {
			$text = mb_substr($text, 0, $maxLength) . '…';
		}
		return $text;
	}

}
