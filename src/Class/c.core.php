<?php

/**
 * @name c.core.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_EXTRA . '/bbcode.inc.php';

class tsCore {
	 
	public array $settings;
	public int $uid;

	public function __construct() {
		// CARGANDO CONFIGURACIONES
		$this->settings = $this->getSettings();
		$this->settings['tema'] = $this->getTema();
		//
		if(isset($_GET['do']) && in_array($_GET['do'], ['portal', 'posts'])) {
			$this->settings['news'] = $this->getNews();
		}
	}
	
	/**
	 * @access public
	 * @name buildRoutes()
	 * @return array
	*/
	public function buildRoutes(): array {
		$baseUrl   = rtrim($this->settings['url'], '/');
		$theme     = $this->settings['tema']['t_url'];
		$storage   = "$baseUrl/storage";
		$assets    = "$baseUrl/assets";

		$routes = [
			'url'       => $baseUrl,
			'domain'    => $this->getDomain(),
			'canonical' => $this->currentUrl(false),
			'redirectTo' => $this->currentUrl(),
			'tema' => [
				'base'   => $theme,
				'css'    => "$theme/css",
				'js'     => "$theme/js",
				'images' => "$theme/images"
			],
			'assets' => [
				'base'	=> $assets,
				'css'    => "$assets/css",
				'js'     => "$assets/js",
				'images' => "$assets/images"
			],
			'storage' => [
				'base'      => $storage,
				'avatar'    => "$storage/avatar",
				'portadas'  => "$storage/portadas",
				'uploads'   => "$storage/uploads",
				'media' 	   => "$storage/media"
			]
		];
		return $routes;
	}

	/**
	 * @access public
	 * @name getSettings()
	 * @param string
	 * @return string|array|null
	*/
	public function route(string $path = ''): string|array|null {
		$routes = $this->buildRoutes();
		if ($path === '') {
			return $routes;
		}

		$segments = explode(':', $path);
		$current  = $routes;

		foreach ($segments as $segment) {
			if (!is_array($current) || !array_key_exists($segment, $current)) {
				return null;
			}
			$current = $current[$segment];
		}
		return $current;
	}
	
	/**
	 * @access public
	 * @name getSettings()
	 * @return array
	*/
	public function getSettings(): array {
		return DB::fetch("SELECT * FROM w_configuracion WHERE phpost_id");
	}
	
	/**
	 * @access public
	 * @name reCaptchaConfig()
	 * @return string|int|array
	*/
	public function reCaptchaConfig(string $type = ''): string|int|array {
		$data = DB::fetch("SELECT c_reg_active, c_reg_activate, c_reg_rango, c_met_welcome, c_message_welcome, c_allow_edad, captcha_provider, g_project_id, g_credentials_json, public_key, secret_key FROM w_registro WHERE reg_id = :id", ['id' => 1]);

		if(!empty($type)) return $data[$type];
		return $data;
	}
	
	/**
	 * @access public
	 * @name getNovemods()
	 * @return array
	*/
	public function getNovemods(): array {
	   $datos = DB::fetch("SELECT 
	      (SELECT COUNT(post_id) FROM p_posts WHERE post_status = 3) as revposts,
	      (SELECT COUNT(cid) FROM p_comentarios WHERE c_status = 1) as revcomentarios,
	      (SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'post') as repposts,
	      (SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'mensaje') as repmps,
	      (SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'usuario') as repusers,
	      (SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'foto') as repfotos,
	      (SELECT COUNT(susp_id) FROM u_suspension) as suspusers,
	      (SELECT COUNT(post_id) FROM p_posts WHERE post_status = 2) as pospelera,
	      (SELECT COUNT(foto_id) FROM f_fotos WHERE f_status = 2) as fospelera
	   ") ?? [];
	   // Calcular total solamente de los campos relevantes
	   $keysToSum = ['repposts', 'repfotos', 'repmps', 'repusers', 'revposts', 'revcomentarios'];
	   $datos['total'] = array_sum(array_intersect_key($datos, array_flip($keysToSum)));
	   return $datos;
	}

	/**
	 * @access public
	 * @name getCategorias()
	 * @return array
	*/
	public function getCategorias(): array {
		$data = DB::fetchAll('SELECT cid, c_orden, c_nombre, c_seo, c_img, c_color, c_privada FROM p_categorias ORDER BY c_orden');
		return $data;
	}
	
	/**
	 * @access public
	 * @name getTema()
	 * @return array
	*/
	public function getTema(): array {
		$data = DB::fetch("SELECT tema FROM w_configuracion WHERE phpost_id = :tema LIMIT 1", ['tema' => 1]);
		$data['t_path'] = isset($_SESSION['theme_path']) ? $_SESSION['theme_path'] : $data['tema'];
		$data['t_url'] = "{$this->settings['url']}/themes/{$data['t_path']}";
		return $data;
	}

	/**
	 * @access private
	 * @name mapNewsType()
	 * @param int
	 * @return array
	*/
	private function mapNewsType(int $type): array {
	   return match ($type) {
	      1 => ['label' => 'Importante', 'css' => 'important'],
	      2 => ['label' => 'Cambios',    'css' => 'changes'],
	      default => ['label' => 'Normal', 'css' => 'normal'],
	   };
	}
	
	/**
	 * @access public
	 * @name getNews
	 * @return array
	 */
	public function getNews(): array {
	   $data = [];
	   $now  = time();

	   $query = DB::fetchAll("SELECT not_body, not_date, not_expires, not_type, not_color FROM w_noticias WHERE not_active = :active AND (not_expires = :expire OR not_expires > $now) ORDER BY not_type DESC, not_date DESC LIMIT 10", [
	   	'active' => 1,
	   	'expire' => 0
	   ]);

	   foreach($query as $k => $row) {
	      $row['not_body'] = $this->parseBBCode($row['not_body'], 'news');
	      $row['type']     = $this->mapNewsType((int)$row['not_type']);
	      $data[] = $row;
	   }

	   return $data;
	}
	
	/**
	 * @access public
	 * @name parseBadWords
	 * @param string
	 * @param bool
	 * @return string
	 */
	public function parseBadWords(string $censurar = '', bool $type = false): string  {
		if (empty($censurar)) {
			return $censurar; // Retornar inmediatamente si la cadena esta vacia.
		}
		// Construir la consulta
		$query = 'SELECT word, swop, method, type FROM w_badwords';
		if (!$type) {
			$query .= ' WHERE type = 0';
		}
		$query = DB::fetchAll($query);
		foreach($query AS $badword) {
			$search = ((int)$badword['method'] === 0) ? $badword['word'] : "{$badword['word']} ";
			$replace = ((int)$badword['type'] === 1) ? '<img title="' . $this->setSecure($badword['word']) . '" src="' . $this->setSecure($badword['swop']) . '" align="absmiddle"/>' : "{$badword['swop']} ";
			$censurar = str_ireplace($search, $replace, $censurar);
		}
		return $censurar;
	}       
	
	/**
	 * @access public
	 * @name setLevel
	 * @param int
	 * @param bool
	 * @return array|bool
	 */
	public function setLevel(int $tsLevel = 0, bool $message = false): array|bool {
		global $tsUser;
		// Los mensajes
		$setMessages = [
			1 => 'Esta p&aacute;gina solo es vista por los visitantes.',
			2 => 'Para poder ver esta p&aacute;gina debes iniciar sesi&oacute;n.',
			3 => 'Estas en un &aacute;rea restringida solo para moderadores.',
			4 => 'Estas intentando algo no permitido.'
		];
		// Definimos los accesos!
		$conditions = [
			0 => true, // CUALQUIERA
			1 => $tsUser->is_member === 0, // SOLO VISITANTES
			2 => $tsUser->is_member === 1, // SOLO MIEMBROS
			3 => $tsUser->is_admod || $tsUser->permiso('moderacion.panel.acceso'), // SOLO MODERADORES
			4 => $tsUser->is_admod === 1 // SOLO ADMIN
		];
		
		$tsLevel = $tsLevel ?? 0;
		if($message && !$conditions[$tsLevel]) {
			// Manejo de mensajes de error
			return [
				'titulo' => 'Error', 
				'mensaje' => $setMessages[$tsLevel] ?? 'Error desconocido.'
			];
		}
		elseif (isset($conditions[$tsLevel]) && $conditions[$tsLevel]) return true;   
	}

	/**
	 * @access public
	 * @name redirectTo
	 * @param string
	 * @return void
	 */
	public function redirectTo(string $tsDir = '/'): void {
		$reloader = $tsDir === '/' ? $this->settings['url'] : $tsDir;
		header("Location: $reloader");
		exit();
	}

	/**
	 * @access public
	 * @name redirectTo
	 * @param string
	 * @return void
	 */
	public function redirectAdmin(string $action = '', string $param = 'save', string $aux = ''): void {
		$reloader = "{$this->settings['url']}/admin/{$action}?{$param}=true{$aux}";
		header("Location: $reloader");
		exit();
	}

	/**
	 * @access public
	 * @name getDomain
	 * @return string
	 */
	public function getDomain(): string {
	   $url = $this->settings['url'] ?? '';
	   if (empty($url)) {
	      return '';
	   }
	   $host = parse_url($url, PHP_URL_HOST);
	   if (!$host) {
	      return '';
	   }
	   $parts = explode('.', $host);
	   $count = count($parts);
	   if ($count < 2) {
	      return $host;
	   }
	   return $parts[$count - 2] . '.' . $parts[$count - 1];
	}

	/**
	 * @access public
	 * @name currentUrl
	 * @return string
	 */
	public function currentUrl(bool $urlencode = true): string {
	   $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
	   $uri = $scheme . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

	   return $urlencode ? urlencode($uri) : $uri;
	}

	/**
	 * @access public
	 * @name setSecure
	 * @param string $value
	 * @param bool $xss
	 * @return string
	 */
	public function setSecure(string $value = '', bool $xss = false): string {
		if(empty($value)) return '';
	   // Normalizar
	   $value = trim($value);
	   // Escapar para SQL (legacy)
	   #$value = db_exec('real_escape_string', $value);
	   // Escapar para HTML si se solicita
	   if ($xss) {
	      $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	   }
	   return $value;
	}

	/**
	 * @access public
	 * @name antiFlood
	 * @param bool   $print Finaliza la ejecución si se excede el límite
	 * @param string $type  Tipo de acción (post, comment, vote, etc)
	 * @param string $msg   Mensaje personalizado
	 * @return bool|string
	 */
	public function antiFlood(bool $print = true, string $type = 'post', string $msg = ''): bool|string {
	   global $tsUser;

	   if (!isset($_SESSION['flood'])) {
	      $_SESSION['flood'] = [];
	   }
	   $now   = time();
	   $msg   = $msg ?: 'No puedes realizar tantas acciones en tan poco tiempo.';
	   $limit = (int) ($tsUser->permiso('limites.antiflood') ?? 0);
	   // Primera vez para este tipo
	   if (!isset($_SESSION['flood'][$type])) {
	      $_SESSION['flood'][$type] = $now;
	      return true;
	   }
	   $elapsed = $now - $_SESSION['flood'][$type];
	   if ($elapsed < $limit) {
	      $remaining = $limit - $elapsed;
	      $finalMsg  = "0: {$msg} Inténtalo en {$remaining} segundos.";
	      if ($print) {
	         exit($finalMsg);
	      }
	      return $finalMsg;
	   }
	   // Actualizamos timestamp
	   $_SESSION['flood'][$type] = $now;
	   return true;
	}

	# MAXIMA CONVERSION => URL AMIGABLES | Ya no usaremos esta funcion...
	# la dejó asi lo voy cambiando de a poco
	public function setSEO($string, $max = '-') {
		return $this->slugify($string, $max);
	}
	/*
		parseBBCode($bbcode)
	*/
	public function parseBBCode(string $bbcode = '', string $type = 'normal', ?int $id = 0) {
		// Class BBCode
		$parser = new BBCode();
		$parser->route = $this->settings['url'];
		// Seleccionar texto
		$parser->id = $id;
		$parser->setText($bbcode);
		$bbcodes = $parser->bbcodeAllow();
		//
		$restriction = match($type) {
			'firma' => array_slice($bbcodes, 0, 11),
			'news' => array_slice($bbcodes, 0, 5),
			'normal' => $bbcodes,
		};
		$parser->setRestriction($restriction);
		// Parsear menciones si el tipo es 'normal' o 'smiles'
		if ($type === 'normal' || $type === 'smiles') {
			$parser->parseMentions();
		}
		$parser->parseSmiles();
		return $parser->getAsHtml();
	}
	
	/**
	 * @param array  $data
	 * @param string $prefix
	 * @return string
	 */
	public function buildSqlSet(array $data, string $prefix = ''): string {
	   if (empty($data)) {
	      return '';
	   }
	   $sets = [];
	   foreach ($data as $field => $value) {
	   	$field = $prefix . $field;
	   	$sets[] = match (true) {
            is_int($value),
            is_float($value)   => "$field = $value",
            is_bool($value)    => "$field = " . (int) $value,
            $value === null    => "$field = NULL",
            default            => "$field = '" . (string)$value . "'",
        };
	   }
	   return implode(', ', $sets);
	}
	
}