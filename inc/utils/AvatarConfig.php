<?php

/**
 * @name AvatarConfig.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

final class AvatarConfig {

	public const SIZES = [
		200,
		60,
	];

	public const FORMATS = [
		'webp',
		'png',
		'avif',
	];

	public const QUALITY = [
		'webp' => 90,
		'png'  => 9,
		'avif' => 60,
	];

   public const VARIANTS = [
      'avatar' => 200,
      'thumb'  => 60,
   ];

	public static function baseDir(int $userId, string $filename = ''): string {
		return TS_STORAGE . "/avatar/user_{$userId}/{$filename}";
	}

	public static function filename(int $size, string $format): string {
		if (!in_array($size, self::SIZES, true)) {
			throw new InvalidArgumentException('Tamaño de avatar inválido');
		}
		if (!in_array($format, self::FORMATS, true)) {
			throw new InvalidArgumentException('Formato de avatar inválido');
		}
		return "avatar_{$size}.{$format}";
	}

	public static function fullPath(int $userId, int $size, string $format): string {
		return self::baseDir($userId) . self::filename($size, $format);
	}

	public static function supportsFormat(string $format): bool {
		return match ($format) {
			'avif' => function_exists('imageavif'),
			'webp' => function_exists('imagewebp'),
			'png'  => true,
			default => false,
		};
	}
}
