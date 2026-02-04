<?php

/**
 * @name Config.Session.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

session_start([
	'cookie_secure'   => Config::app('security.session.secure'),
	'cookie_httponly' => Config::app('security.session.httponly'),
	'cookie_samesite' => Config::app('security.session.samesite'),
	'use_strict_mode' => true
]);

define('SESSION_NAME', Config::app('security.session.name'));

if (!isset($_SESSION[SESSION_NAME])) {
   session_regenerate_id(true);

   $_SESSION['__initiated'] = true;
   $_SESSION[SESSION_NAME]  = time();
}