<?php

declare(strict_types=1);

/**
 * @package    Logger
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class LogWidget {

	/**
	 * Genera los datos del widget para asignar a Smarty.
	 * Retorna null si el usuario no es admin.
	 */
	public static function getData(): ?array {
		$logsDir = Config::app('paths.logs.full_path');
		$today   = date('d_m_Y');

		// Buscar archivos de hoy
		$todayFiles = glob($logsDir . "/*-{$today}.log") ?: [];
		$hasToday   = !empty($todayFiles);

		// Contar entradas y nivel más grave del día
		$totalErrors  = 0;
		$worstLevel   = null;
		$lastDatetime = null;

		$levelPriority = [
			'FATAL'     => 5,
			'EXCEPTION' => 4,
			'ERROR'     => 3,
			'WARNING'   => 2,
			'INFO'      => 1,
			'DEBUG'     => 0,
		];

		$levelMeta = [
			'FATAL'     => ['color' => '#ff2d55', 'bg' => '#2a0a10', 'icon' => '💀'],
			'EXCEPTION' => ['color' => '#ff6b35', 'bg' => '#2a1200', 'icon' => '🔥'],
			'ERROR'     => ['color' => '#ff453a', 'bg' => '#25090a', 'icon' => '✖'],
			'WARNING'   => ['color' => '#ffd60a', 'bg' => '#252000', 'icon' => '⚠'],
			'INFO'      => ['color' => '#30d158', 'bg' => '#0a2010', 'icon' => 'ℹ'],
			'DEBUG'     => ['color' => '#64d2ff', 'bg' => '#001a25', 'icon' => '⚙'],
		];

		foreach ($todayFiles as $file) {
			$entries = LogParser::parseFile($file);
			foreach ($entries as $e) {
				$lvl = $e['level'] ?? 'INFO';

				// Contar errores graves
				if (in_array($lvl, ['FATAL', 'EXCEPTION', 'ERROR'])) {
					$totalErrors++;
				}

				// Nivel más grave
				$currentPriority = $worstLevel !== null ? ($levelPriority[$worstLevel] ?? -1) : -1;
				$newPriority     = $levelPriority[$lvl] ?? 0;
				if ($newPriority > $currentPriority) {
					$worstLevel = $lvl;
				}

				// Fecha más reciente
				if (!empty($e['datetime'])) {
					$lastDatetime = $e['datetime'];
				}
			}
		}

		$worstMeta = $worstLevel ? ($levelMeta[$worstLevel] ?? null) : null;

		return [
			'hasToday'     => $hasToday,
			'totalErrors'  => $totalErrors,
			'worstLevel'   => $worstLevel,
			'worstColor'   => $worstMeta['color'] ?? '#555',
			'worstBg'      => $worstMeta['bg']    ?? '#111',
			'worstIcon'    => $worstMeta['icon']  ?? '·',
			'lastDatetime' => $lastDatetime,
			'url'          => Config::app('url') . '/logs/',
			'date'         => str_replace('_', '/', $today),
		];
	}
}
