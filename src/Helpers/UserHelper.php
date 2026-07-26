<?php

declare(strict_types=1);

/**
 * @package    Helpers
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class UserHelper {

	/**
	 * TTL reutilizado desde c_stats_cache.
	 * Nota: este valor conceptualmente NO es ideal para estado online,
	 * pero se mantiene por compatibilidad con el core actual.
	 */
	private int $cacheTTL;

	public function __construct(protected tsCore $Core) {
		$this->cacheTTL = (int)$this->Core->settings['c_stats_cache'] * 60;
	}

	public function isBlocked(int $bUser, int $bAuser): bool {
		$sentancia = "SELECT 1 FROM `u_bloqueos` WHERE b_user = :bUser AND b_auser = :bAuser LIMIT 1";
		return DB::exists($sentancia, ['bUser' => $bUser, 'bAuser' => $bAuser]);
	}

	/**
	 * Determina el estado online de un usuario.
	 *
	 * Regla de negocio:
	 * - Baneado siempre tiene prioridad
	 * - Luego Online
	 * - Luego Inactive
	 * - Luego Offline
	 */
	public function getStatusCode(int $lastactive, ?int $baneado = 0): array {
		$time = time();
		// IS ONLINE?
		$onlineLimit   = $time - $this->cacheTTL;
		$inactiveLimit = $time - ($this->cacheTTL * 2);

		return match (true) {
			(int)$baneado === 1 => [
				't'   => 'Suspendido',
				'css' => 'banned'
			],
			$lastactive > $onlineLimit => [
				't'   => 'Online',
				'css' => 'online'
			],
			$lastactive > $inactiveLimit => [
				't'   => 'Inactive',
				'css' => 'inactive'
			],
			default => [
				't'   => 'Offline',
				'css' => 'offline'
			],
		};
	}

}
