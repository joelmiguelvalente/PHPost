<?php

/**
 * Smarty plugin: meta
 *
 * Genera meta tags y Open Graph para SEO y redes sociales
 *
 * Uso:
 *   {meta title="Mi Título" description="Descripción" url=$canonical_url}
 *   {meta og=true twitter=true json_ld=true}
 *   {meta data=$page_data}
 *
 * Parámetros:
 * @param array<string,mixed> $params {
 *     @type string    $title           Título de la página
 *     @type string    $description     Descripción de la página
 *     @type string    $url             URL canónica
 *     @type string    $image           Imagen principal (URL completa)
 *     @type string    $site_name       Nombre del sitio
 *     @type string    $locale         Idioma (por defecto: es_ES)
 *     @type bool      $og             Incluir Open Graph (por defecto: true)
 *     @type bool      $twitter        Incluir Twitter Cards (por defecto: false)
 *     @type bool      $json_ld        Incluir JSON-LD (por defecto: false)
 *     @type array     $data           Datos estructurados directos
 *     @type string    $type           Tipo de contenido (article, website, etc.)
 *     @type string    $author         Autor del contenido
 *     @type string    $keywords       Palabras clave separadas por coma
 * }
 * @param Smarty\Template $template Instancia del template actual
 *
 * @return string HTML con los meta tags generados
 *
 * @author Miguel92
 * @version 1.0
 */
function smarty_function_meta(array $params, Smarty\Template $template): string {
	$defaults = [
		'title' => '',
		'favicon' => '',
		'favicon_name' => 'favicon-#.png',
		'favicon_path' => '/images/favicon/',
		'favicon_sizes' => [16, 32, 64, 128, 512],
		'description' => '',
		'url' => '',
		'image' => '',
		'site_name' => '',
		'locale' => 'es_ES',
		'og' => true,
		'twitter' => false,
		'json_ld' => false,
		'type' => 'website',
		'author' => '',
		'keywords' => '',
		'data' => []
	];

	$config = array_merge($defaults, $params);

	if (empty($config['title'])) {
		$config['title'] = $template->getTemplateVars('tsTitle') ?: '';
	}
	if (empty($config['description'])) {
		$config['description'] = $template->getTemplateVars('tsDescription') ?: Config::app('app.description');
	}
	if (empty($config['url'])) {
		$config['url'] = $template->getTemplateVars('tsCanonical') ?: getCurrentUrl();
	}
	if (empty($config['site_name'])) {
		$config['site_name'] = $template->getTemplateVars('tsSiteName') ?: '';
	}

	$output = [];

	// Meta tags básicos
	$output[] = generateBasicMeta($config);
	
	// Open Graph
	if ($config['og']) {
		$output[] = generateOpenGraphMeta($config);
	}
	
	// Twitter Cards
	if ($config['twitter']) {
		$output[] = generateTwitterMeta($config);
	}
	
	// JSON-LD
	if ($config['json_ld']) {
		$output[] = generateJsonLd($config);
	}

	// Datos personalizados
	if (!empty($config['data'])) {
		$output[] = generateCustomMeta($config['data']);
	}

	return implode("\n", array_filter($output));
}

/**
 * Genera meta tags básicos
 */
function generateBasicMeta(array $config): string {
	$tags = [];
	
	$tags[] = '<meta charset="UTF-8">';
	$tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
	$tags[] = '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
	
	if (!empty($config['title'])) {
		$title = htmlspecialchars($config['title']);
		$tags[] = "<meta name=\"title\" content=\"$title\">";
	}
	
	if (!empty($config['description'])) {
		$desc = htmlspecialchars($config['description']);
		$tags[] = "<meta name=\"description\" content=\"$desc\">";
	}
	
	if (!empty($config['keywords'])) {
		$keywords = htmlspecialchars($config['keywords']);
		$tags[] = "<meta name=\"keywords\" content=\"$keywords\">";
	}
	
	if (!empty($config['author'])) {
		$author = htmlspecialchars($config['author']);
		$tags[] = "<meta name=\"author\" content=\"$author\">";
	}
	
	if (!empty($config['favicon'])) {
		$favicon = htmlspecialchars($config['favicon']);
		$extension = pathinfo($favicon, PATHINFO_EXTENSION);
		$extension = ($extension !== 'ico') ? $extension : 'x-icon';
		$tags[] = "<link rel=\"shortcut icon\" href=\"$favicon\" type=\"image/$extension\" />";
	}
	
	if (!empty($config['favicon_name']) && !empty($config['favicon_path'])) {
		if (!str_ends_with($config['favicon_path'], '/')) {
		   $config['favicon_path'] .= '/';
		}
		$favicon = htmlspecialchars($config['favicon_path'].$config['favicon_name']);
		foreach($config['favicon_sizes'] as $size) {
			$newFavicon = str_replace('#', $size, $favicon);
			$extension = pathinfo($newFavicon, PATHINFO_EXTENSION);
			$extension = ($extension !== 'ico') ? $extension : 'x-icon';
			$tags[] = "<link rel=\"shortcut icon\" href=\"$newFavicon\" type=\"image/$extension\" />";
		}
	}
	
	if (!empty($config['url'])) {
		$url = htmlspecialchars($config['url']);
		$tags[] = "<link rel=\"canonical\" href=\"$url\">";
	}

	return implode("\n", $tags);
}

/**
 * Genera meta tags Open Graph
 */
function generateOpenGraphMeta(array $config): string {
	$tags = [];
	
	$ogData = [
		'og:title' => $config['title'],
		'og:description' => $config['description'],
		'og:url' => $config['url'],
		'og:type' => $config['type'],
		'og:locale' => $config['locale'],
		'og:site_name' => $config['site_name'],
	];
	
	if (!empty($config['image'])) {
		$ogData['og:image'] = $config['image'];
		$ogData['og:image:width'] = '1200';
		$ogData['og:image:height'] = '630';
		$ogData['og:image:type'] = 'image/jpeg';
	}
	
	if (!empty($config['author'])) {
		$ogData['article:author'] = $config['author'];
	}

	foreach ($ogData as $property => $content) {
		if (!empty($content)) {
			$content = htmlspecialchars($content);
			$tags[] = "<meta property=\"$property\" content=\"$content\">";
		}
	}

	return implode("\n", $tags);
}

/**
 * Genera meta tags Twitter Cards
 */
function generateTwitterMeta(array $config): string {
	$tags = [
		'<meta name="twitter:card" content="summary_large_image">',
	];
	
	if (!empty($config['site_name'])) {
		$siteName = htmlspecialchars($config['site_name']);
		$tags[] = "<meta name=\"twitter:site\" content=\"@$siteName\">";
	}
	
	if (!empty($config['title'])) {
		$title = htmlspecialchars($config['title']);
		$tags[] = "<meta name=\"twitter:title\" content=\"$title\">";
	}
	
	if (!empty($config['description'])) {
		$desc = htmlspecialchars($config['description']);
		$tags[] = "<meta name=\"twitter:description\" content=\"$desc\">";
	}
	
	if (!empty($config['image'])) {
		$image = htmlspecialchars($config['image']);
		$tags[] = "<meta name=\"twitter:image\" content=\"$image\">";
	}

	return implode("\n", $tags);
}

/**
 * Genera JSON-LD estructurado
 */
function generateJsonLd(array $config): string {
	$schema = [
		'@context' => 'https://schema.org',
		'@type' => ucfirst($config['type']),
		'name' => $config['title'],
		'description' => $config['description'],
		'url' => $config['url'],
	];
	
	if (!empty($config['image'])) {
		$schema['image'] = [
			'@type' => 'ImageObject',
			'url' => $config['image'],
			'width' => 1200,
			'height' => 630,
		];
	}
	
	if (!empty($config['author'])) {
		$schema['author'] = [
			'@type' => 'Person',
			'name' => $config['author'],
		];
	}
	
	if (!empty($config['site_name'])) {
		$schema['publisher'] = [
			'@type' => 'Organization',
			'name' => $config['site_name'],
		];
	}

	return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Genera meta tags personalizados
 */
function generateCustomMeta(array $data): string {
	$tags = [];
	
	foreach ($data as $name => $content) {
		if (is_array($content)) {
			// Soporte para meta tags con atributos múltiples
			foreach ($content as $key => $value) {
				$nameAttr = htmlspecialchars($name);
				$contentValue = htmlspecialchars($value);
				$tags[] = "<meta name=\"$nameAttr:$key\" content=\"$contentValue\">";
			}
		} else {
			$nameAttr = htmlspecialchars($name);
			$contentValue = htmlspecialchars($content);
			$tags[] = "<meta name=\"$nameAttr\" content=\"$contentValue\">";
		}
	}

	return implode("\n", $tags);
}

/**
 * Obtiene la URL actual
 */
function getCurrentUrl(): string {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
	$uri = $scheme . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
	
	return $scheme . $uri;
}
