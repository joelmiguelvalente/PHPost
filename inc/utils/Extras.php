<?php

/**
 * @package     PHPost
 * @author      Miguel92
 * @copyright   2026
 * @version     2.0.0
 */

declare(strict_types=1);

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

}