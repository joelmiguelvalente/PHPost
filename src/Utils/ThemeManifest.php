<?php

/**
 * @name ThemeManifest.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class ThemeManifest {

	private const FILE_NAME = '.theme';

	private const REQUIRED_KEYS = [
		'name',
		'slug',
		'author',
		'version',
		'description',
	];

	private string $themePath;
	private string $themeDir;

	public function __construct(string $theme) {
		$this->themePath = TS_THEMES . '/' . rtrim($theme, DIRECTORY_SEPARATOR);
		$this->themeDir  = $theme;
	}

	public function load(): array {
		$file = $this->themePath . DIRECTORY_SEPARATOR . self::FILE_NAME;
		if (!is_file($file) || !is_readable($file)) {
			throw new RuntimeException("Theme Manifest no legible: {$file}");
		}
		$data = [];
		$lines = file($file, FILE_IGNORE_NEW_LINES);
		foreach ($lines as $lineNumber => $line) {
			$line = trim($line);
			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}
			if (!str_contains($line, ':')) {
				throw new RuntimeException(
					"Formato inválido en {$file} línea " . ($lineNumber + 1)
				);
			}
			[$key, $value] = explode(':', $line, 2);
			$key   = trim($key);
			$value = trim($value);
			if ($key === '') {
				throw new RuntimeException(
					"Clave vacía en {$file} línea " . ($lineNumber + 1)
				);
			}
			$data[$key] = $value;
		}
		$this->validate($data);
		return $data;
	}

	private function validate(array $data): void {
		foreach (self::REQUIRED_KEYS as $key) {
			if (!array_key_exists($key, $data) || $data[$key] === '') {
				throw new RuntimeException("Falta la clave obligatoria '{$key}'");
			}
		}
		if ($data['slug'] !== $this->themeDir) {
			throw new RuntimeException(
				"El slug '{$data['slug']}' no coincide con el directorio '{$this->themeDir}'"
			);
		}
	}
}

/*$themePath = 'minimalist';

$manifest = new ThemeManifest($themePath);
$theme = $manifest->load();

print_r($theme);*/
