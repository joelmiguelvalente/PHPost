<?php

declare(strict_types=1);

/**
 * @package    Config
 * @author     Miguel92
 * @copyright  2026
*/

namespace config;

use ReflectionClass;

abstract class AbstractConfig
{
	/**
	 * Contenedor interno de configuración
	 *
	 * @var array<string, mixed>
	 */
	protected array $items = [];

	/**
	 * Guardamos configuraciones ya cargadas
	 *
	 * @var array<string, mixed>
	 */
	protected array $cache = [];

	public function __construct()
	{
		$this->loadLocalOverrides();
	}

	/**
	 * Carga archivo .local.php si existe y mergea sobre defaults
	 */
	protected function loadLocalOverrides(): void
	{
		$reflect = new ReflectionClass($this);
		$currentFile = $reflect->getFileName();

		$localFile = preg_replace('/\.php$/', '.local.php', $currentFile);
		// Verificar si existe el archivo .local en la raíz del proyecto
		$rootLocalFile = dirname(__DIR__, 1) . '/dev.local';

		if (file_exists($rootLocalFile) && is_file($localFile)) {
			$local = require $localFile;

			if (!is_array($local)) {
				throw new RuntimeException('Config local inválida');
			}

			$this->items = array_replace_recursive($this->items, $local);
		}
	}

	/**
	 * Obtiene un valor de configuración
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		if ($key === '') {
            return $this->items;
        }

        // Retornar del cache si ya fue resuelto
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

		$value = $this->items;
		foreach (explode('.', $key) as $segment) {
			if (!is_array($value) || !array_key_exists($segment, $value)) {
				return $default;
			}
			$value = $value[$segment];
		}
		$this->cache[$key] = $value;
		return $value;
	}
}
