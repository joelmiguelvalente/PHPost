<?php

declare(strict_types=1);

/**
 * @package    PHPost/Helpers
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class CoreHelper {

	public function isSafeHttpUrl(string $url): bool {
		$decoded = urldecode(trim($url));
		if (!filter_var($decoded, FILTER_VALIDATE_URL)) {
			return false;
		}
		$parts = parse_url($decoded);
		if (!isset($parts['scheme'], $parts['host'])) {
			return false;
		}
		if (!in_array($parts['scheme'], ['http', 'https'], true)) {
			return false;
		}
		// Resolver IP
		$ip = gethostbyname($parts['host']);
		// Bloquear IPs privadas / locales
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
			return false;
		}
		return true;
	}

	public function getUrlContent(string $tsUrl): ?string {
		$url = urldecode(trim($tsUrl));
		if (!$this->isSafeHttpUrl($url)) {
			return null;
		}

		if (function_exists('curl_init')) {
			$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 
             'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL            => $url,
				CURLOPT_USERAGENT      => $userAgent,
				CURLOPT_TIMEOUT        => 15,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_MAXREDIRS      => 3,
			]);

			$result = curl_exec($ch);
			curl_close($ch);
			
			return $result !== false ? $result : null;
		}

		$context = stream_context_create([
			'http' => [
				'timeout' => 15,
				'header'  => "User-Agent: Mozilla/5.0\r\n"
			]
		]);
		$result = @file_get_contents($url, false, $context);
		return $result !== false ? $result : null;
	}
	
}
