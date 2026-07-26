<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Security
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class IP {

	/**
	 * Proxies confiables (si existen).
	 * Ej: ['127.0.0.1', '10.0.0.1']
	 */
	private array $trustedProxies = [];

	public function __construct(array $trustedProxies = []) {
		$this->trustedProxies = $trustedProxies;
	}

	private function isValidIP(string $ip): bool {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
	}

	/**
	 * Convierte una IP a formato binario para almacenar en BD
	 * (Recomendado: columna VARBINARY(16))
	 */
	public function ipToBinary(string $ip): ?string {
		if (!$this->isValidIP($ip)) {
			return null;
		}
		$binary = inet_pton($ip);
		return $binary === false ? null : $binary;
	}

	/**
	 * Convierte IP en formato binario a legible para humanos
	 */
	public function binaryToIp(string $binary): ?string {
		if ($binary === null || $binary === '') {
			return null;
		}
		$ip = inet_ntop($binary);
		return $ip === false ? null : $ip;
	}

	/**
	 * Obtiene IP real y la retorna en formato binario
	 * (Ideal para guardar directamente en BD)
	 */
	public function getIPBinary(): ?string {
		$ip = $this->executeIP();
		if ($ip === 'unknown') {
			return null;
		}
		return $this->ipToBinary($ip);
	}

	/**
	 * Obtiene IP real en formato legible
	 * (Método principal)
	 */
	public function getIPHuman(): string {
		return $this->executeIP();
	}

	/**
	 * Compatibilidad legacy
	 */
	public function getIP(): string {
		return $this->executeIP();
	}

	/**
	 * Obtiene la IP real del cliente de forma segura.
	 */
	public function executeIP(): string {
		$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
		if (!$this->isValidIP($remoteAddr)) {
			return 'unknown';
		}
		// Si NO estamos detrás de un proxy confiable → usamos REMOTE_ADDR
		if (!$this->isTrustedProxy($remoteAddr)) {
			return $remoteAddr;
		}
		// Proxy confiable → intentamos X-Forwarded-For
		$forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
		if ($forwarded) {
			foreach (explode(',', $forwarded) as $ip) {
				$ip = trim($ip);
				if ($this->isValidIP($ip)) {
					return $ip;
				}
			}
		}
		return $remoteAddr;
	}

	private function isTrustedProxy(string $ip): bool {
		return in_array($ip, $this->trustedProxies, true);
	}
}
