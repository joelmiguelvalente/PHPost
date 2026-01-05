<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_SMARTY . 'autoload.php';

class tsSmarty extends \Smarty\Smarty {

	private string $theme;

	private string $page;

	public $templateError = 't.error.tpl';

	/**
	 * Constructor de la clase tsSmarty.
	 * Configura las opciones predeterminadas de Smarty y establece directorios.
	*/
	public function __construct() {
		// Trae el constructor directamente de Smarty
		parent::__construct();

		// Habilita la comprobación de compilación para un rendimiento óptimo
		$this->setCompileCheck(TRUE);

		// Establece el directorio de compilación de plantillas
		$this->setCompileDir(TS_CACHE . TS_TEMA);

		// Agrega directorio de plugins Smarty
		$this->loadPlugins();

		require_once TS_LIBS . 'extensiones/SmartyExtensiones.php';
		$this->addExtension(new SmartyExtensiones());

		// Suprime advertencias de variables indefinidas o nulas
		$this->muteUndefinedOrNullWarnings();
	}

	public function setTheme(string $theme): void {
		$this->theme = $theme;
		$this->setCompileDir(TS_CACHE . $theme);
	}

	public function setPage(string $page): void {
		$this->page = $page;
	}

	/**
	 * Carga y registra dinámicamente los plugins de tipo "función" y "modificador".
	 * Utiliza la función glob para buscar archivos de plugins en los directorios
	 * correspondientes y registrar automáticamente las funciones de Smarty.
	 * 
	 * Este método permite agregar nuevos plugins simplemente añadiendo archivos PHP
	 * en las carpetas correspondientes sin necesidad de modificar este código.
	 * 
	 * @return void
	 */
	private function loadPlugins(): void {
		// Definir los directorios de plugins
		$pluginDirs = [
			'function' => TS_PLUGINS . 'function.*.php',
			'modifier' => TS_PLUGINS . 'modifier.*.php'
		];
		// Iterar sobre las categorías de plugins
		foreach ($pluginDirs as $type => $pattern) {
			// Buscar todos los archivos correspondientes en el directorio
			$files = glob($pattern);
			foreach ($files as $file) {
				require_once $file;
				// Extraer el nombre del plugin (sin la extensión .php)
				$pluginName = explode('.', basename($file, '.php'))[1];
				// Registrar el plugin de acuerdo al tipo
				$this->registerPlugin($type, $pluginName, "smarty_{$type}_{$pluginName}");
			}
		}
	}

	/**
	 * Modifica el comportamiento de salida de la plantilla, opcionalmente aplica filtro de eliminación de espacios en blanco.
	 *
	 * @param bool $loadFilter Determina si aplicar el filtro de eliminación de espacios en blanco
	*/
	public function output($loadFilter = false) {
		if ($loadFilter) $this->loadFilter('output', 'trimwhitespace');
	}

	private function resolvePage(string $page): string {
		$file = match ($page) {
			'admin', 'moderacion' => 'main.tpl',
			'saliendo' => 'views/html/saliendo.html',
			default => "t.$page.tpl"
		};
		return $this->templateExists($file) ? $file : $this->templateError;
	}

	/**
	 * Mapea rutas del sistema y módulos.
	 */
	private function mapDirectories(): array {
		$directories = [
			'root'       => TS_ROOT
		];
		return $directories;
	}

	/**
	 * Carga todos los directorios utilizados por el tema.
	 */
	private function loadAllTemplates(): void {
		$templates = TS_THEMES . "{$this->theme}/templates";
		$map = array_merge([
			'tema'        => TS_THEMES . $this->theme,
			'templates'   => $templates,
			'sections'    => "$templates/sections/",
			'modules'     => "$templates/modules/",
			'pagina'      => "$templates/modules/{$this->page}/",
			'global'      => "$templates/modules/global/",
			'php_files'   => "$templates/t.php_files/"
		], $this->mapDirectories());

		$this->addTemplateDir($map);
	}

	/**
	 * Renderiza una plantilla.
	 */
	public function load(string $page = ''): void {
		$this->loadAllTemplates();
		try {
			$template = $this->resolvePage($page);
			$this->display($template);
		} catch (Exception $e) {

			$mensaje = preg_replace_callback(
				"/'([^']+)'/",
				fn ($message) => "'<strong>{$message[1]}</strong>'",
				$e->getMessage()
			);

			$show = "
				Lo sentimos, se produjo un error al cargar la plantilla <strong>t.$page.tpl</strong>.
				<br>Debido al error:<br>
				<code style=\"font-size:1rem;line-height: 1.3rem;color: #d971ad;
				word-wrap: break-word;background: rgba(217, 113, 173, .12);
				display:block;padding:.5em;\">$mensaje</code>
			";

			show_error($show, 'plantilla');
		}
	}

	/**
	 * Borra la versión compilada del recurso de plantilla especificado.
	 *
	 * @param string $template Nombre de la plantilla compilada a borrar
	*/
	public function clearCompiled($template) {
		$this->clearCompiledTemplate($template);
	}

}