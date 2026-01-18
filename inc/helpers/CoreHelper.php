<?php

/**
 * @name CoreHelper.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class CoreHelper {

	public function getUrlContent(string $tsUrl): ?string {
		// Usamos cURL si está disponible (más seguro y configurable)
		if (function_exists('curl_init')) {
			// User-Agent del cliente (fallback a uno genérico si no existe)
			$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL            => $tsUrl,
				CURLOPT_USERAGENT      => $userAgent,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_FOLLOWLOCATION => true,  // Permite redirecciones
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => true,  // Seguridad habilitada
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_CONNECTTIMEOUT => 10,
			]);
			$result = curl_exec($ch);
			// Si ocurrió algún error, devolver null
			if ($result === false) {
				curl_close($ch);
				return null;
			}
			curl_close($ch);
			return $result;
		}
		// Fallback sin cURL (menos seguro, pero útil en hosting muy limitado)
		$context = stream_context_create([
			'http' => [
				'timeout' => 30,
				'header'  => "User-Agent: Mozilla/5.0\r\n"
			]
		]);
		$result = @file_get_contents($tsUrl, false, $context);
		return $result !== false ? $result : null;
	}
	
}