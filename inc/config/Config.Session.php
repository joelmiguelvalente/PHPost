<?php

/**
 * @name Config.Session.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

session_start([
	'cookie_secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
	'cookie_httponly' => true,
	'cookie_samesite' => 'Lax',
	'use_strict_mode' => true
]);

if (!isset($_SESSION['created'])) {
   session_regenerate_id(true);

   $_SESSION['__initiated'] = true;
   $_SESSION['created']    = time();
}