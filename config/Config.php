<?php

declare(strict_types=1);

/**
 * @package    Config
 * @author     Miguel92
 * @copyright  2026
 */

require_once __DIR__ . '/AbstractConfig.php';
require_once __DIR__ . '/Config.Application.php';
require_once __DIR__ . '/Config.Database.php';
require_once __DIR__ . '/Config.Mailer.php';

use \config\{AbstractConfig, ConfigApplication, ConfigDatabase, ConfigMailer};

final class Config
{
	private static ?\config\ConfigApplication $app  = null;
	private static ?\config\ConfigDatabase 	  $db   = null;
	private static ?\config\ConfigMailer 	  $mail = null;

	public static function app(string $key, mixed $default = null): mixed
	{
		self::$app ??= new \config\ConfigApplication();
		return self::$app->get($key, $default);
	}

	public static function db(string $key, mixed $default = null): mixed
	{
		self::$db ??= new \config\ConfigDatabase();
		return self::$db->get($key, $default);
	}

	public static function mail(string $key, mixed $default = null): mixed
	{
		self::$mail ??= new \config\ConfigMailer();
		return self::$mail->get($key, $default);
	}

}
