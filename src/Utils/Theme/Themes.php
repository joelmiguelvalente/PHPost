<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Theme
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class Themes {

	public function __construct(
		protected ThemeManifest $Manifest
	) {
	}

	private function isGithub(string $param): string {
		$endpoint = 'https://github.com';
		$result = '';
		if (isset($param) && !empty($param)) {
		    $raw = $param;
		    if (str_starts_with($raw, $endpoint) || str_starts_with($raw, 'http://github.com')) {
		        $result = $raw;
		    } else {
		        $result = $endpoint . '/' . ltrim($raw, '/');
		    }
		}
		return $result;
	}

	/**
	 * Ya no requerimos que este instalado
	 */
	public function getTemas(): array {
		$themes = $this->Manifest->loadAll();
		$resultados = [];
		foreach ($themes as $key => $themeName) {
			$screenshot = ltrim($themeName['SCREENSHOT'], '/') ?? 'screenshot.png';

			$resultados[$key] = [
				't_name' => ucfirst($themeName['NAME'] ?? $key),
				't_path' => $themeName['PATH'] ?? $key,
				't_description' => $themeName['DESCRIPTION'] ?? '',
				't_copy' => $themeName['AUTHOR'] ?? 'unknown',
				't_link' => $themeName['LINK'] ?? '',
				't_repo' => $this->isGithub($themeName['GITHUB']),
				't_screen' => "themes/{$key}/{$screenshot}"
			];
		}
		return $resultados;
	}

	public function getAllThemes(): array {
		return $this->getTemas();
	}

	public function getUserThemeUse(?int $uid = 0, string $theme = 'default'): string {
		$data = DB::fetch("SELECT user_theme FROM u_miembros_sets WHERE user_id = :uid", ['uid' => $uid]);
		return $data['user_theme'] ?? $theme;
	}

}
