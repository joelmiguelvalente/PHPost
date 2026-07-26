<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Http
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

/**
 * # También soporta puntos como separador
 * $routes->route('tema.css', '.');
 *
 * # Verificar si existe una ruta
 * if ($routes->hasRoute('storage:media')) {
 *     echo "Ruta existe";
 * }
 *
 * Ruta con parámetros
 * $routes->routeWithParams('tema:css', ['v' => '1.2', 'debug' => '1']);
 * > Resultado: https://misitio.com/themes/default/css?v=1.2&debug=1
 *
 * # URL absoluta a partir de relativa
 * $routes->absoluteUrl('uploads/foto.jpg');
 * > Resultado: https://misitio.com/uploads/foto.jpg

 * # Obtener todas las rutas como array plano
 * $allRoutes = $routes->flattenRoutes();
 */

final class Routes {

    /**
     * Configuración del sitio
     */
    private array $settings;

    /**
     * Cache de rutas construidas
     */
    private ?array $routesCache = null;

    /**
     * Constructor
     *
     * @param array $settings Configuración del sitio (debe contener 'url' y 'tema')
     */
    public function __construct(protected tsCore $Core) {
        $this->settings = $this->Core->getSettings();
    }

    /**
     * Obtiene la URL base del dominio
     */
    private function getDomain(): string {
        $isSecure = (!empty(Request::server('HTTPS')) && Request::server('HTTPS') !== 'off');
        $protocol = $isSecure ? 'https://' : 'http://';
        $host = Request::server('HTTP_HOST') ?? Request::server('SERVER_NAME') ?? '';
        return $protocol . $host;
    }

    /**
     * Obtiene la URL actual completa
     *
     * @param bool $withQuery Incluir query string
     */
    private function currentUrl(bool $withQuery = true): string {
        $url = $this->getDomain() . ($_SERVER['REQUEST_URI'] ?? '');
        if (!$withQuery) {
            $url = strtok($url, '?');
        }
        return $url;
    }

    /**
     * Construye el array completo de rutas
     *
     * @return array
     */
    public function buildRoutes(): array {
        $baseUrl   = rtrim($this->settings['url'], '/');
        $theme     = "$baseUrl/themes/" . ($this->settings['tema']['t_url'] ?? $this->settings['tema'] ?? "default");

        $routes = [
            'url'        => $baseUrl,
            'domain'     => $this->getDomain(),
            'canonical'  => $this->currentUrl(false),
            'current'    => $this->currentUrl(),
            'redirectTo' => $this->currentUrl(),
            'tema'       => $this->getRouteFolders($theme, ['css','js','images','fonts']),
            'assets'     => $this->getRouteFolders("$baseUrl/assets", ['css','js','images','fonts']),
            'storage'    => $this->getRouteFolders("$baseUrl/storage", ['avatar','portadas','uploads','media','temp'])
        ];

        $this->routesCache = $routes;
        return $routes;
    }

    private function getRouteFolders(string $url, array $data): array {
        $folders['base'] = $url;
        foreach($data as $folder) {
            $folders[$folder] = "{$url}/{$folder}";
        }
        return $folders;
    }

    /**
     * Obtiene una ruta específica usando notación de puntos o dos puntos
     * Ejemplos:
     * - route('url') => http://misitio.com
     * - route('tema:css') => http://misitio.com/themes/default/css
     * - route('storage.avatar') => http://misitio.com/storage/avatar
     *
     * @param string $path Ruta a obtener (vacío para todas)
     * @param string $separator Separador de segmentos (':' o '.')
     * @return string|array|null
     */
    public function route(string $path = '', string $separator = ':'): string|array|null {
        $routes = $this->routesCache ?? $this->buildRoutes();

        if ($path === '') {
            return $routes;
        }

        // Soporte para ambos separadores
        $separator = in_array($separator, [':', '.']) ? $separator : ':';
        $segments = explode($separator, $path);
        $current = $routes;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        // Si es un array pero se pidió una ruta específica, retornar null
        if (is_array($current)) {
            return null;
        }

        return (string) $current;
    }

    public function avatarPath(): string {
        return $this->route('storage:avatar') . '/';
    }

    /**
     * Obtiene una ruta y la combina con parámetros adicionales
     *
     * @param string $path Ruta base
     * @param array $params Parámetros query string
     * @param string $separator Separador de segmentos
     * @return string|null
     */
    public function routeWithParams(string $path, array $params = [], string $separator = ':'): ?string {
        $baseUrl = $this->route($path, $separator);

        if ($baseUrl === null) {
            return null;
        }

        if (empty($params)) {
            return $baseUrl;
        }

        $queryString = http_build_query($params);
        return $baseUrl . '?' . $queryString;
    }

    /**
     * Genera una URL absoluta a partir de una relativa
     *
     * @param string $relative Ruta relativa (ej: 'uploads/foto.jpg')
     * @return string
     */
    public function absoluteUrl(string $path, string $relative): string {
        $baseUrl = $this->route($path);
        $relative = ltrim($relative, '/');
        return $baseUrl . '/' . $relative;
    }

    /**
     * Verifica si una ruta existe
     *
     * @param string $path Ruta a verificar
     * @param string $separator Separador de segmentos
     * @return bool
     */
    public function hasRoute(string $path, string $separator = ':'): bool {
        return $this->route($path, $separator) !== null;
    }

    /**
     * Obtiene todas las rutas como array plano (clave => url)
     *
     * @param string $prefix Prefijo para las claves
     * @return array
     */
    public function flattenRoutes(string $prefix = ''): array {
        $routes = $this->routesCache ?? $this->buildRoutes();
        $result = [];

        $flatten = function($array, $currentKey = '') use (&$flatten, &$result) {
            foreach ($array as $key => $value) {
                $newKey = $currentKey === '' ? $key : $currentKey . ':' . $key;

                if (is_array($value)) {
                    $flatten($value, $newKey);
                } else {
                    $result[$newKey] = (string) $value;
                }
            }
        };

        $flatten($routes);
        return $result;
    }

    /**
     * Método mágico para acceder a rutas como propiedades
     * Ejemplo: $routes->url, $routes->tema->css
     */
    public function __get(string $name) {
        // Soporte para acceso directo a rutas de primer nivel
        $routes = $this->routesCache ?? $this->buildRoutes();

        if (array_key_exists($name, $routes) && !is_array($routes[$name])) {
            return $routes[$name];
        }

        // Retornar un objeto para acceso anidado
        if (array_key_exists($name, $routes) && is_array($routes[$name])) {
            return new class($routes[$name], $this) {
                private array $data;
                private Routes $parent;

                public function __construct(array $data, Routes $parent) {
                    $this->data = $data;
                    $this->parent = $parent;
                }

                public function __get(string $name) {
                    if (array_key_exists($name, $this->data)) {
                        return $this->data[$name];
                    }
                    return null;
                }

                public function __toString() {
                    return '';
                }
            };
        }

        return null;
    }
}
