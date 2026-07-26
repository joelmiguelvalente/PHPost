<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Misc
 * @author     Miguel92
 * @copyright  2026
 */

(defined('TS_HEADER') && PHP_SAPI !== 'cli') || exit('No se permite el acceso directo al script.');

final class Generator
{
	private string $name;
	private string $nameUpper;
	private array $options;
	private string $root;
	private string $theme;
	private array $created = [];
	private array $skipped = [];
	private array $errors  = [];

	// Archivos que puede generar
	private const FILES = [
		'php'   => 'src/Php/{name}.php',
		'class' => 'src/Class/c.{name}.php',
		'api'   => 'src/Api/api.{name}.php',
		'tpl'   => 'themes/{theme}/templates/t.{name}.tpl',
		'css'   => 'themes/{theme}/css/{name}.css',
		'js'    => 'themes/{theme}/js/{name}.js',
	];

	public function __construct(string $name, array $options, string $root, string $theme)
	{
		$this->name      = strtolower(trim($name));
		$this->nameUpper = ucfirst($this->name);
		$this->options   = $options;
		$this->root      = rtrim($root, '/');
		$this->theme     = $theme;
	}

	/**
	 * Valida el nombre de la sección
	 */
	public static function validateName(string $name): bool
	{
		return (bool) preg_match('/^[a-z][a-z0-9_]{1,30}$/', strtolower(trim($name)));
	}

	/**
	 * Ejecuta la generación
	 */
	public function run(): array
	{
		// Siempre obligatorios
		$toGenerate = ['php', 'class', 'tpl'];

		// Opcionales según configuración
		if (!empty($this->options['api']))  $toGenerate[] = 'api';
		if (!empty($this->options['css']))  $toGenerate[] = 'css';
		if (!empty($this->options['js']))   $toGenerate[] = 'js';

		foreach ($toGenerate as $type) {
			$this->generateFile($type);
		}

		return [
			'created' => $this->created,
			'skipped' => $this->skipped,
			'errors'  => $this->errors,
		];
	}

	/**
	 * Genera un archivo del tipo dado
	 */
	private function generateFile(string $type): void
	{
		$relativePath = str_replace(
			['{name}', '{theme}'],
			[$this->name, $this->theme],
			self::FILES[$type]
		);
		$fullPath = $this->root . '/' . $relativePath;

		// No sobreescribir
		if (file_exists($fullPath)) {
			$this->skipped[] = $relativePath;
			return;
		}

		// Crear directorio si no existe
		$dir = dirname($fullPath);
		if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
			$this->errors[] = "No se pudo crear el directorio: $dir";
			return;
		}

		$content = $this->getTemplate($type);

		if (file_put_contents($fullPath, $content) === false) {
			$this->errors[] = "No se pudo escribir: $relativePath";
			return;
		}

		$this->created[] = $relativePath;
	}

	/**
	 * Devuelve el contenido del template según tipo
	 */
	private function getTemplate(string $type): string
	{
		$year = date('Y');
		$name = $this->name;
		$upper = $this->nameUpper;

		return match ($type) {

			'php' => <<<PHP
			<?php

			/**
			 * @name {$name}.php
			 * @author PHPost Team
			 * @copyright {$year}
			 */

			declare(strict_types=1);

			require_once dirname(__DIR__, 2) . "/header.php";
			\$tsTitle = \$tsCore->settings['titulo'];

			// Ver permisos en src/Utils/Controller.php
			\$ctx = Controller::page('{$name}')->everybody();
			\$ctx->exportLegacy();

			\$tsLevelMsg = \$tsUser->setLevel(\$ctx->getLevel(), true);
			if (is_array(\$tsLevelMsg)) {
			   \$ctx->changePage('aviso');
			   \$ctx->stop();
			   \$smarty->assign("tsAviso", \$tsLevelMsg);
			   \$ctx->exportLegacy();
			}

			if (\$ctx->continue()) {
			   // TODO: lógica de la sección {$name}
			}

			if (\$tsAjax) {
			   \$smarty->assign("tsTitle", \$tsTitle);
			   require_once TS_ROOT . "/footer.php";
			}
			PHP,

			'class' => <<<PHP
			<?php

			/**
			 * @name c.{$name}.php
			 * @author PHPost Team
			 * @copyright {$year}
			 */

			declare(strict_types=1);

			defined('TS_HEADER') || exit('No se permite el acceso directo al script.');
			   exit('No se permite el acceso directo al script');
			}

			class ts{$upper}
			{
			   public function __construct(
				  protected tsCore \$Core,
				  protected tsUser \$User
			   ) {}

			   // TODO: métodos de la sección {$name}
			}
			PHP,

			'api' => <<<PHP
			<?php

			/**
			 * @name api.{$name}.php
			 * @author PHPost Team
			 * @copyright {$year}
			 */

			declare(strict_types=1);

			defined('TS_HEADER') || exit('No se permite el acceso directo al script.');
			   exit('No se permite el acceso directo al script');
			}

			// Nivel: admin > 4 | moderador > 3 | miembros > 2 | invitados > 1 | todos > 0
			const ACTIONS = [
			   // '{$name}-accion' => ['nivel' => 2, 'template' => 'accion', 'ajax' => true],
			];

			if (!array_key_exists(\$action, ACTIONS)) {
			   http_response_code(403);
			   exit('Acción inválida');
			}

			\$config  = ACTIONS[\$action];
			\$tsLevel = \$config['nivel'];
			\$tsAjax  = (int) \$config['ajax'];
			\$tsPage  = sprintf('p.{$name}.%s', \$config['template']);

			\$tsLevelMsg = \$tsUser->setLevel(\$tsLevel, true);
			if (!\$tsLevelMsg) {
			   echo '0: ' . \$tsLevelMsg;
			   die();
			}

			switch (\$action) {
			   // case '{$name}-accion':
			   // break;
			}
			PHP,

			'tpl' => <<<TPL
			{include "main_header.tpl"}

			<div class="page-{$name}">
			   {* TODO: contenido de la sección {$name} *}
			</div>

			{include "main_footer.tpl"}
			TPL,

			'css' => <<<CSS
			/**
			 * @section {$name}
			 * @author  PHPost Team
			 * @year    {$year}
			 */

			.page-{$name} {
			   /* TODO */
			}
			CSS,

			'js' => <<<JS
			/**
			 * @section {$name}
			 * @author  PHPost Team
			 * @year    {$year}
			 */

			(function () {
			   'use strict';

			   // TODO: lógica JS de la sección {$name}

			})();
			JS,

		};
	}
}
