<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Theme
 * @author     Miguel92
 * @copyright  2026
 */

/**
 * Contenedor de inyección de dependencias
 * 
 * Implementa autowiring y singleton pattern.
 * Los métodos son estáticos para mantener compatibilidad con el código existente.
 */
final class Container
{

	/** @var array<string, object> Instancias singleton */
	private static array $instances = [];

	/** @var array<string, class-string> Bindings (abstract → concreto) */
	private static array $bindings = [];

	/** @var array<string, callable> Factories */
	private static array $factories = [];

	/**
	 * Registra un binding.
	 * Container::set(tsUser::class) — registra como singleton
	 * Container::set(tsUser::class, tsUser::class) — explícito
	 */
	public static function set(string $abstract, ?string $concrete = null): void
	{
		self::$bindings[$abstract] = $concrete ?? $abstract;
		unset(self::$instances[$abstract]);
	}

	/**
	 * Obtiene o crea una instancia singleton con autowiring.
	 *
	 * Container::get(tsUser::class)
	 *   → autodetecta: __construct(tsCore $Core)
	 *   → resuelve tsCore recursivamente (sin dependencias → new tsCore())
	 *   → pasa tsCore al constructor de tsUser
	 *   → cachea y retorna
	 *
	 * Container::get(Paginator::class, [$this->Core->settings['url']])
	 *   → autodetecta: __construct(string $url)
	 *   → usa el valor pasado en $args para el parámetro $url
	 *   → cachea y retorna
	 *
	 * @param string $class Nombre de la clase a instanciar
	 * @param array $args Argumentos adicionales para el constructor (opcional)
	 * @return object Instancia de la clase solicitada
	 * @throws RuntimeException Si no se puede resolver un parámetro
	 */
	public static function get(string $class, array $args = []): object
	{
		$key = $class . ($args !== [] ? ':' . md5(json_encode($args)) : '');
		if (isset(self::$instances[$key])) {
			return self::$instances[$key];
		}
		return self::$instances[$key] = self::build($class, $args);
	}

	/**
	 * Obtiene el servicio identificado por la clave.
	 *
	 * @param string $id Clave única del servicio para buscar
	 * @return mixed Servicio solicitado
	 * @throws \RuntimeException Si no se pudo encontrar la clave
	 * @throws \RuntimeException Si no se pudo crear la instancia
	 */
	public static function psrGet(string $id): mixed
	{
		if (!Container::has($id)) {
			throw new \RuntimeException("Service '{$id}' not found in container.");
		}
		try {
			return Container::get($id);
		} catch (\Throwable $e) {
			throw new \RuntimeException("Failed to create service '{$id}': " . $e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * Verifica si el contenedor puede proveer un servicio para una clave.
	 *
	 * @param string $id Clave única del servicio a buscar
	 * @return bool True si el servicio está disponible
	 */
	public static function psrHas(string $id): bool
	{
		return isset(self::$bindings[$id]) || class_exists($id);
	}

	/**
	 * Verifica si el contenedor puede proveer un servicio.
	 *
	 * @param string $id Clave única del servicio a buscar
	 * @return bool True si el servicio está disponible
	 */
	public static function has(string $id): bool
	{
		return self::psrHas($id);
	}

	/**
	 * Elimina una instancia cacheada.
	 *
	 * La próxima llamada a get() creará una nueva instancia.
	 *
	 * @param string $class Clase registrada.
	 */
	public static function forget(string $class): void
	{
	    foreach (array_keys(self::$instances) as $key) {
	        if (str_starts_with($key, $class . ':') || $key === $class) {
	            unset(self::$instances[$key]);
	        }
	    }
	}

	/**
	 * Limpia todas las instancias almacenadas.
	 *
	 * No elimina bindings ni factories.
	 */
	public static function clear(): void
	{
	    self::$instances = [];
	}

	/**
	 * Crea una instancia nueva siempre (sin cache).
	 */
	public static function fresh(string $class, array $args = []): object
	{
		return self::build($class, $args);
	}

	/**
	 * Registra una fábrica para crear instancias
	 * Container::factory(PDO::class, function() {
	 *     return new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
	 * });
	 */
	public static function factory(string $abstract, callable $factory): void
	{
		self::$factories[$abstract] = $factory;
		unset(self::$instances[$abstract]);
	}

	/**
	 * Resuelve una clase y sus dependencias recursivamente.
	 *
	 * @param string $class Nombre de la clase a construir
	 * @param array $args Argumentos posicionales para el constructor
	 * @return object Instancia de la clase construida
	 * @throws ContainerExceptionInterface Si no se puede resolver un parámetro
	 */
	private static function build(string $class, array $args = []): object
	{
		$concrete = self::$bindings[$class] ?? $class;

		if (isset(self::$factories[$concrete])) {
			return (self::$factories[$concrete])();
		}

		if (!class_exists($concrete)) {
			throw new \RuntimeException("Class '{$concrete}' is not registered in the container.");
		}

		$reflection = new ReflectionClass($concrete);
		$constructor = $reflection->getConstructor();

		if ($constructor === null) {
			return new $concrete();
		}

		$deps = [];
		$positionalArgs = array_values(
			array_filter($args, static fn($k) => is_int($k), ARRAY_FILTER_USE_KEY)
		);
		foreach ($constructor->getParameters() as $param) {
		    $name = $param->getName();
		    $type = $param->getType();
		    // Argumento por nombre
		    if (array_key_exists($name, $args)) {
		        $deps[] = $args[$name];
		        continue;
		    }
		    // Argumento posicional
		    if ($positionalArgs !== []) {
		        $deps[] = array_shift($positionalArgs);
		        continue;
		    }
		    // Autowiring
		    if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
		        $deps[] = self::get($type->getName());
		        continue;
		    }
		    // Valor por defecto
		    if ($param->isDefaultValueAvailable()) {
		        $deps[] = $param->getDefaultValue();
		        continue;
		    }
		    throw new \RuntimeException("Cannot resolve parameter \${$name} while building {$concrete}.");
		}
		return $reflection->newInstanceArgs($deps);
	}
}
