<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Misc
 * @author     Miguel92
 * @copyright  2026
 */

final class Compat
{
	/**
	 * curl_close() no hace nada desde PHP 8.0 y fue eliminada en 8.5
	 */
	public static function curl_close(\CurlHandle|null $handle): void
	{
		if (PHP_VERSION_ID < 80500 && $handle !== null) {
			curl_close($handle);
		}
	}

	/**
	 * imagedestroy() no hace nada desde PHP 8.0 y fue eliminada en 8.5
	 */
	public static function imagedestroy(\GdImage|null $image): void
	{
		if (PHP_VERSION_ID < 80500 && $image !== null) {
			imagedestroy($image);
		}
	}

	/**
	 * finfo_close() fue deprecada en PHP 8.5
	 * Los objetos finfo se liberan automáticamente
	 */
	public static function finfo_close(\finfo|null $finfo): void
	{
		// Desde PHP 8.5, finfo_close() está deprecada y no hace nada
		// Los objetos finfo se liberan automáticamente al final del scope
		// No es necesario hacer nada para PHP >= 8.5
		if (PHP_VERSION_ID < 80500 && $finfo !== null) {
			finfo_close($finfo);
		}
	}

	/**
	 * Verifica si una función es deprecada o no existe
	 */
	public static function isFunctionSafe(string $functionName): bool
	{
		// Lista de funciones deprecadas o eliminadas en PHP 8.5+
		$deprecatedFunctions = [
			'finfo_close' 		=> 80500,
			'imagedestroy' 		=> 80500,
			'curl_close' 		=> 80500,
			'mysqli_commit' 	=> 80500,
			'mysqli_rollback' 	=> 80500,
		];

		if (isset($deprecatedFunctions[$functionName])) {
			return PHP_VERSION_ID < $deprecatedFunctions[$functionName];
		}

		return function_exists($functionName);
	}

}
