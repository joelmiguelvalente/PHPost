<?php

/**
 * @name c.smarty.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_LIBS . '/smarty/autoload.php';
require_once TS_LIBS . '/extensiones/SmartyExtensiones.php';

class tsSmarty extends \Smarty\Smarty {

	private string $cache;

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

		// Agrega directorio de plugins Smarty
		$this->loadPlugins();

		$this->addExtension(new SmartyExtensiones());

		// Suprime advertencias de variables indefinidas o nulas
		$this->muteUndefinedOrNullWarnings();
	}

	public function setTheme(string $theme): void {
		$this->theme = $theme;
		$this->setCompileDir(TS_STORAGE . '/cache/' . $theme);
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
			'function' => TS_LIBS . '/plugins/function.*.php',
			'modifier' => TS_LIBS . '/plugins/modifier.*.php'
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

	private function resolvePage(string $page, bool $useExtension): string {
		$file = match ($page) {
			'registro', 'login' => 'base.tpl',
			'admin', 'moderacion' => 'main.tpl',
			'saliendo' => 'themes/html/saliendo.html',
			default => ($useExtension ? "t.$page.tpl" : "$page.tpl")
		};
		return $this->templateExists($file) ? $file : $this->templateError;
	}

	private function recursiveDirectories(string $path): array {
		$iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
		$iterators = [];
		foreach ($iterator as $item) {
		   if ($item->isDir()) {
		      $iterators[$item->getFilename()] = $item->getPathname();
		   }
		}
		return $iterators;
	}

	/**
	 * Mapea rutas del sistema y módulos.
	 */
	private function mapDirectories(): array {
		$directories = [
			'root'		 => TS_ROOT,
			'auth'		 => TS_VIEWS . '/auth',
			'api'			 => TS_VIEWS . '/api',
			'error'		 => TS_VIEWS . '/error',
			'components' => TS_VIEWS . '/components',
			'dashboard'  => TS_VIEWS . '/dashboard',
		];
		return $directories;
	}

	/**
	 * Carga todos los directorios utilizados por el tema.
	 */
	private function loadAllTemplates(): void {
		$templates = TS_THEMES . "/{$this->theme}/templates";
		$map = array_merge([
			'tema'        => TS_THEMES . "/{$this->theme}",
			'templates'   => $templates
		], $this->recursiveDirectories($templates), $this->mapDirectories());
		$this->addTemplateDir($map);
	}

	/**
	 * Renderiza una plantilla.
	 */
	public function load(string $page = '', bool $useExtension = true): void {
		$this->loadAllTemplates();

		try {
			$template = $this->resolvePage($page, $useExtension);
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