<?php

/**
 * Smarty plugin: load
 *
 * Carga assets CSS o JS resolviendo rutas y dependencias según el tema activo
 * y el sistema de assets global.
 *
 * Uso:
 *   {load type="css" file="main"}
 *   {load type="js" file=["cuenta","perfil"]}
 *
 * Parámetros:
 * @param array<string,mixed> $params {
 *     @type string          $type  Tipo de asset: "css" o "js" (obligatorio)
 *     @type string|array    $file  Nombre o lista de archivos sin extensión (obligatorio)
 *     @type bool            $cache Añadir timestamp para evitar cacheo (opcional)
 * }
 * @param Smarty\Template $template Instancia del template actual
 *
 * @return string HTML <link> o <script> generado
 *
 * @throws InvalidArgumentException Si los parámetros son inválidos
 * @throws RuntimeException Si tsRoutes no está definido o es inválido
 *
 * @author Miguel92
 * @version 2.2
 */
function smarty_function_load(array $params, Smarty\Template $template): string {

	// Validación inicial de parámetros
	if (empty($params['type']) || empty($params['file'])) {
		throw new InvalidArgumentException('smarty_function_load: tipo y archivo son obligatorios');
	}

	if (!in_array($params['type'], ['css', 'js'], true)) {
		throw new InvalidArgumentException('smarty_function_load: tipo debe ser "css" o "js"');
	}

	$routes = $template->getTemplateVars('tsRoutes');
	if (!is_array($routes)) {
		throw new RuntimeException('smarty_function_load: tsRoutes no está definido o es inválido');
	}

	$type = $params['type'];
	$files = is_array($params['file']) ? $params['file'] : [$params['file']];
	
	// Normalizar archivos - eliminar duplicados y valores no válidos
	$files = array_filter($files, function($file) {
		return is_string($file) && $file !== '';
	});
	$files = array_values(array_unique($files));

	// Resolver dependencias
	$files = resolveDependencies($files, $type);

	// Generar salida
	return generateAssetTags($files, $type, $routes, !empty($params['cache']));
}

/**
 * Resuelve dependencias de archivos según el tipo
 */
function resolveDependencies(array $files, string $type): array {
	$dependencyMap = [
		'js' => [
			'cuenta'  => ['cropper.min', 'upload.avatar'],
			'agregar' => ['wysibb'],
			'posts'   => ['denuncias','wysibb'],
			'perfil'  => ['denuncias','bloquear'],
		],
		'css' => [
			'cuenta'  => ['cropper.min'],
			'agregar' => ['wysibb'],
			'posts'   => ['wysibb'],
		]
	];

	$conditionalFiles = [];
	
	// Dependencias condicionales
	if ($type === 'js' && isset($_GET['action']) && $_GET['action'] === 'afs') {
		$conditionalFiles[] = 'afiliados';
	}

	if ($type === 'js' && in_array('moderacion', $files, true)) {
		// Si 'moderacion' está presente, excluirlo de la carga normal
		$files = array_diff($files, ['moderacion']);
	}

	// Procesar dependencias
	$ordered = [];
	foreach ($files as $file) {
		if (isset($dependencyMap[$type][$file])) {
			foreach ($dependencyMap[$type][$file] as $dep) {
				if (!in_array($dep, $files, true) && !in_array($dep, $ordered, true)) {
					$ordered[] = $dep;
				}
			}
		}
		$ordered[] = $file;
	}

	// Añadir archivos condicionales al final
	foreach ($conditionalFiles as $condFile) {
		if (!in_array($condFile, $ordered, true)) {
			$ordered[] = $condFile;
		}
	}

	return $ordered;
}

/**
 * Busca un archivo en las ubicaciones disponibles
 */
function findAssetFile(string $name, string $type, array $routes): ?string {
	$file = "$name.$type";
	$locations = [
		[
			'path' => TS_THEMES . '/' . TS_TEMA,
			'url'  => $routes['tema']['base'],
		],
		[
			'path' => TS_THEMES . '/' . TS_TEMA . "/$type",
			'url'  => $routes['tema'][$type],
		],
		[
			'path' => TS_ASSETS . "/$type",
			'url'  => $routes['assets'][$type],
		],
	];

	foreach ($locations as $loc) {
		if (is_file($loc['path'] . '/' . $file)) {
			return $loc['url'] . '/' . $file;
		}
	}

	return null;
}

/**
 * Genera las etiquetas HTML para los assets
 */
function generateAssetTags(array $files, string $type, array $routes, bool $useCache): string {
	$output = '';
	$cacheParam = $useCache ? '?t=' . time() : '';

	foreach ($files as $filename) {
		$fileUrl = findAssetFile($filename, $type, $routes);
		
		if (!$fileUrl) {
			continue; // Archivo no encontrado, omitir
		}

		if ($type === 'css') {
			$output .= '<link href="' . htmlspecialchars($fileUrl . $cacheParam) . 
					  '" rel="stylesheet" type="text/css">' . PHP_EOL;
		} elseif ($type === 'js') {
			$output .= '<script src="' . htmlspecialchars($fileUrl . $cacheParam) . 
					  '" defer></script>' . PHP_EOL;
		}
	}

	return trim($output);
}
