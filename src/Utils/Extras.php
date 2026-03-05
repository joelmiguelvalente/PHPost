<?php

/**
 * @name Extras.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class Extras {

	// Obtenemos el protocolo https o http
	public function getSSLProtocol(bool $withoutSlash = false): string {
		$ssl = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') $ssl .= 's';
		return $withoutSlash ? $ssl . '://' : $ssl;
	}

	public function slugify(string $text, string $separator = '-'): string {
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
	public function cleanText(string $text, int $maxLength = 0): string {
	   // 1. BBCode con contenido: [b]texto[/b] → texto
	   $text = preg_replace('/\[([a-z]+)[^\]]*\](.*?)\[\/\1\]/is', '$2', $text);
	   // 2. BBCode sin cierre: [hr], [img=...], etc. → vacío
	   $text = preg_replace('/\[([a-z]+)[^\]]*\]/i', '', $text);
	   // 3. HTML
	   $text = strip_tags($text);
	   // 4. Espacios múltiples y saltos de línea
	   $text = trim(preg_replace('/\s+/', ' ', $text));
	   // 5. Truncar
	   if ($maxLength > 0 && mb_strlen($text) > $maxLength) {
	   	$text = mb_substr($text, 0, $maxLength) . '…';
	   }
	   return $text;
	}

}