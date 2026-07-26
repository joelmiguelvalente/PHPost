<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

require_once TS_LIBS . '/smarty/functions.php';
require_once TS_LIBS . '/extensiones/SmartyExtensiones.php';

class tsSmarty extends \Smarty\Smarty {

	protected Themes $Themes;

	private string $cache;

	private string $theme;

	private string $page;

	public string $templateError = 't.error.tpl';

	/**
	 * Constructor de la clase tsSmarty.
	 * Configura las opciones predeterminadas de Smarty y establece directorios.
	*/
	public function __construct() {
		// Trae el constructor directamente de Smarty
		parent::__construct();

		// Habilita la comprobación de compilación para un rendimiento óptimo
		$compiled = Config::app('app.development') ? TRUE : \Smarty\Smarty::COMPILECHECK_OFF;
		$this->setCompileCheck($compiled);

		// Agrega directorio de plugins Smarty
		$this->loadPlugins();

		$this->addExtension(Container::get(SmartyExtensiones::class));

		// Suprime advertencias de variables indefinidas o nulas
		$this->muteUndefinedOrNullWarnings();

		// Auto-escape
		$this->setEscapeHtml(true);
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
	 * @param bool $loadFilter Determina si aplicar filtro de eliminación de espacios en blanco
	 */
	public function output(bool $loadFilter = false): void {
		if ($loadFilter) {
			$this->registerFilter('output', [new \Smarty\Filter\Output\TrimWhitespace(), 'filter']);
		}

		// Agregar filtro CSP nonce a la salida HTML
		$this->registerFilter('output', fn($content) => $this->injectCspNonce($content));
	}

	/**
	 * Inyecta el nonce CSP en las etiquetas HTML
	 */
	private function injectCspNonce(string $content): string {
		$nonce = CSP_NONCE;

		// Inyectar nonce en <script> (solo opening tag)
		$content = preg_replace(
			'/(<script\b(?![^>]*\bnonce=)[^>]*)(>)/i',
			'$1 nonce="' . $nonce . '"$2',
			$content
		);

		return $content;
	}

	private function resolvePage(string $page, bool $useExtension): string {
		$file = match ($page) {
			'registro', 'login', 'reset_password' => 'base.tpl',
			'admin', 'moderacion' 	 => 'main.tpl',
			'logs' 			 		 => 'views/output/logs.tpl',
			'suspension' 			 => 'views/output/suspension.tpl',
			'mantenimiento' 		 => 'views/output/mantenimiento.tpl',
			'saliendo' 				 => 'themes/html/saliendo.html',
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
			'api'		 => TS_VIEWS . '/api',
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
		$theme = isset($_SESSION['theme_path']) ? $_SESSION['theme_path'] : $this->theme;
		$templates = TS_THEMES . "/{$theme}/templates";
		$map = array_merge([
			'tema'        => TS_THEMES . "/{$theme}",
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
			$mensaje = preg_replace_callback("/'([^']+)'/", fn ($message) => "'<strong>{$message[1]}</strong>'", $e->getMessage());
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
	public function clearCompiled(string $template): void {
		$this->clearCompiledTemplate($template);
	}

}
