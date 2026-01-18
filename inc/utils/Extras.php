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
	 * @access public
	 * @param array
	 * @param int
	 * @return array
	*/
	public function isOnline(&$tsInfo, int $active): array {
		$time = time();
		// IS ONLINE?
		$isOnline = (int)($time - ($active * 60));
		$isInactive = (int)($isOnline * 2); // DOBLE DEL ONLINE
		//
		return match(true) {
			(int)$tsInfo['user_lastactive'] > $isOnline => ['t' => 'Online', 'css' => 'online'],
			(int)$tsInfo['user_lastactive'] > $isInactive => ['t' => 'Inactive', 'css' => 'inactive'],
			(int)$tsInfo['user_baneado'] === 1 => ['t' => 'Suspendido', 'css' => 'banned'],
			default => ['t' => 'Offline', 'css' => 'offline']
		};
	}

}