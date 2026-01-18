<?php

/**
 * @name MuroHelper.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class MuroHelper {

	private string $url;
	protected tsUser $User;

	public function __construct(string $url, tsUser $User) {
		$this->url = $url;
		$this->User = $User;
	}

	/**
	 * @name setMenciones
	 * @access public
	 * @param string
	 * @return string
	 */
	public function setMenciones(string $html = ''): string {
		$tsUser = $this->User;
		return preg_replace_callback('/\B@([a-zA-Z0-9_-]{4,16})\b/', function ($matches) use ($tsUser) {
			$username = $matches[1];
			$uid = $this->User->getUserID($username);
			if (!$uid) {
				return $matches[0]; // Mención sin reemplazo
			}
			$url = "{$this->url}/perfil/{$username}";
			return "@<a href=\"{$url}\">{$username}</a>";
		}, $html);
	}

}