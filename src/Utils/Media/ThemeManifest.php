<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Media
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class ThemeManifest {

	private const FILE_NAME = 'info.theme';

	private const REQUIRED_KEYS = [
		'NAME',
		'PATH',
		'SCREENSHOT',
		'AUTHOR',
		'VERSION',
	];

	private const OPTIONAL_KEYS = [
		'DESCRIPTION',
		'LINK',
		'GITHUB',
		'DEMO',
	];

	/** @var array<string, array<string, string>> Caché en memoria por nombre de tema */
	private array $cache = [];

	private string $themePath;

	public function __construct() {
		$this->themePath = TS_THEMES;
	}

	private function exception(string $message, array $context = []): never {
		Logger::error($message, $context, 'themes');
		throw new RuntimeException($message);
	}

	/**
	 * Carga y devuelve los datos del manifiesto como array asociativo.
	 *
	 * @param  string $theme  Nombre del directorio del tema (ej. 'default')
	 * @return array<string, string>
	 * @throws RuntimeException
	 */
	public function load(string $theme): array {
		if (isset($this->cache[$theme])) {
			return $this->cache[$theme];
		}
		$file = "{$this->themePath}/{$theme}/" . self::FILE_NAME;
		if (!is_file($file) || !is_readable($file)) {
			$this->exception("Theme Manifest no legible: {$file}", ['file' => $file, 'theme' => $theme]);
		}
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false) {
			$this->exception("No se pudo leer el archivo: {$file}", ['file' => $file, 'theme' => $theme]);
		}
		$data = $this->parse($lines, $file, $theme);
		$this->validate($data, $theme, $file);
		$this->cache[$theme] = $data;
		return $data;
	}

	/**
	 * Devuelve el valor de una clave concreta del manifiesto.
	 *
	 * @throws RuntimeException Si la clave no existe en el manifiesto.
	 */
	public function get(string $theme, string $key): string {
		$data = $this->load($theme);
		$key  = strtoupper($key);
		if (!array_key_exists($key, $data)) {
			$this->exception("Clave '{$key}' no encontrada en el manifiesto del tema {$theme}", ['key' => $key, 'theme' => $theme]);
		}
		return $data[$key];
	}

	/**
	 * Devuelve todas las claves reconocidas (obligatorias + opcionales).
	 *
	 * @return string[]
	 */
	public static function knownKeys(): array {
		return array_merge(self::REQUIRED_KEYS, self::OPTIONAL_KEYS);
	}

	/**
	 * Elimina el caché de un tema (o de todos si no se indica ninguno).
	 */
	public function flush(?string $theme = null): void {
		if ($theme === null) {
			$this->cache = [];
		} else {
			unset($this->cache[$theme]);
		}
	}

	// -------------------------------------------------------------------------
	// Privados
	// -------------------------------------------------------------------------

	/**
	 * Parsea las líneas del archivo y devuelve un array clave => valor.
	 *
	 * @param  string[] $lines
	 * @return array<string, string>
	 * @throws RuntimeException
	 */
	private function parse(array $lines, string $file, string $theme): array {
		$data = [];
		foreach ($lines as $index => $line) {
			$line = trim($line);
			// Ignorar comentarios
			if ($line === '' || $line[0] === '#') {
				continue;
			}
			$colonPos = strpos($line, ':');
			if ($colonPos === false) {
				$this->exception("Formato inválido en {$file} línea " . ($index + 1) . ": falta separador ':'", ['file' => $file, 'line' => $index + 1, 'theme' => $theme]);
			}
			$key   = strtoupper(trim(substr($line, 0, $colonPos)));
			$value = trim(substr($line, $colonPos + 1));
			if ($key === '') {
				$this->exception("Clave vacía en {$file} línea " . ($index + 1), ['file' => $file, 'line' => $index + 1, 'theme' => $theme]);
			}
			$data[$key] = $value;
		}
		return $data;
	}

	/**
	 * Valida que estén todas las claves obligatorias y que PATH coincida con el directorio.
	 *
	 * @param array<string, string> $data
	 * @throws RuntimeException
	 */
	private function validate(array $data, string $theme, string $file): void {
		foreach (self::REQUIRED_KEYS as $key) {
			if (!isset($data[$key]) || $data[$key] === '') {
				$this->exception("Falta la clave obligatoria '{$key}' en el manifiesto: {$file}", ['key' => $key, 'file' => $file, 'theme' => $theme]);
			}
		}
		if ($data['PATH'] !== $theme) {
			$this->exception("El PATH '{$data['PATH']}' no coincide con el directorio del tema '{$theme}'", ['expected_path' => $theme, 'actual_path' => $data['PATH'], 'theme' => $theme]);
		}
	}

	/**
	 * Devuelve un array con los datos de todos los temas válidos encontrados en TS_THEMES.
	 * Las carpetas sin info.theme o con manifiesto inválido se ignoran silenciosamente.
	 *
	 * @return array<string, array<string, string>>  [ 'default' => [...], 'dark' => [...], ... ]
	 */
	public function loadAll(): array {
		$result  = [];
		$entries = scandir($this->themePath);

		if ($entries === false) {
			$this->exception("No se puede leer el directorio de temas: {$this->themePath}", ['directory' => $this->themePath]);
		}

		foreach ($entries as $entry) {
			// Ignorar . y .. y archivos sueltos
			if ($entry === '.' || $entry === '..' || !is_dir("{$this->themePath}/{$entry}")) {
				continue;
			}

			$file = "{$this->themePath}/{$entry}/" . self::FILE_NAME;

			// Si no existe info.theme, ignorar la carpeta
			if (!is_file($file) || !is_readable($file)) {
				continue;
			}

			try {
				$result[$entry] = $this->load($entry);
			} catch (RuntimeException) {
				// Manifiesto inválido o PATH no coincide → ignorar
				continue;
			}
		}

		return $result;
	}
}
