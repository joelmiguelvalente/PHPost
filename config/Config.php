<?php

declare(strict_types=1);

require_once __DIR__ . '/AbstractConfig.php';
require_once __DIR__ . '/Config.Application.php';
require_once __DIR__ . '/Config.Database.php';
require_once __DIR__ . '/Config.Mailer.php';

final class Config
{
	private static ?ConfigApplication $app = null;
	private static ?ConfigDatabase $db = null;
	private static ?ConfigMailer $mail = null;

	public static function app(string $key, mixed $default = null): mixed
	{
		self::$app ??= new ConfigApplication();
		return self::$app->get($key, $default);
	}

	public static function db(string $key, mixed $default = null): mixed
	{
		self::$db ??= new ConfigDatabase();
		return self::$db->get($key, $default);
	}

	public static function mail(string $key, mixed $default = null): mixed
	{
		self::$mail ??= new ConfigMailer();
		return self::$mail->get($key, $default);
	}
}
