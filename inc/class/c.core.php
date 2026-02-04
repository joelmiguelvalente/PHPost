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

require_once TS_UTILS . '/Extras.php';
require_once dirname(__DIR__, 1) . '/extras/bbcode.inc.php';

class tsCore extends Extras {
	 
	public array $settings;

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
			'domain'    => str_replace($this->getSSLProtocol(true), '', $this->settings['url']),
			//'canonical' => $this->currentUrl(true),
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
		return db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM w_configuracion'));
	}
	
	/**
	 * @access public
	 * @name getNovemods()
	 * @return array
	*/
	public function getNovemods(): array {
		$datos = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT 
			(SELECT count(post_id) FROM p_posts WHERE post_status = \'3\') as revposts, 
			(SELECT count(cid) FROM p_comentarios WHERE c_status = \'1\' ) as revcomentarios, 
			(SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'1\') as repposts, 
			(SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'2\') as repmps, 
			(SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'3\') as repusers, 
			(SELECT count(DISTINCT obj_id) FROM w_denuncias  WHERE d_type = \'4\') as repfotos, 
			(SELECT count(susp_id) FROM u_suspension) as suspusers, 
			(SELECT count(post_id) FROM p_posts WHERE post_status = \'2\') as pospelera, 
			(SELECT count(foto_id) FROM f_fotos WHERE f_status = \'2\') as fospelera'));
		$datos['total'] = $datos['repposts'] + $datos['repfotos'] + $datos['repmps'] + $datos['repusers'] + $datos['revposts'] + $datos['revcomentarios'];
		return $datos;  
	}

	/**
	 * @access public
	 * @name getCategorias()
	 * @return array
	*/
	public function getCategorias(): array {
		return result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT cid, c_orden, c_nombre, c_seo, c_img FROM p_categorias ORDER BY c_orden'));
	}
	
	/**
	 * @access public
	 * @name getTema()
	 * @return array
	*/
	public function getTema(): array {
		$data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT tid, t_name, t_path, t_copy FROM w_temas WHERE t_path = '{$this->settings['tema']}' LIMIT 1"));
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

	   $query = db_exec([__FILE__, __LINE__], 'query', "SELECT not_body, not_date, not_expires, not_type, not_color FROM w_noticias WHERE not_active = 1 AND (not_expires = 0 OR not_expires > $now) ORDER BY not_type DESC, not_date DESC LIMIT 10");

	   while ($row = db_exec('fetch_assoc', $query)) {
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
		$query = result_array(db_exec([__FILE__, __LINE__], 'query', $query));
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
	 * @return string|array|bool
	 */
	public function setLevel(int $tsLevel = 0, bool $message = false): string|array|bool {
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
			3 => $tsUser->is_admod || (!empty($tsUser->permisos) && isset($tsUser->permisos['moacp']) && $tsUser->permisos['moacp']), // SOLO MODERADORES
			4 => $tsUser->is_admod === 1 // SOLO ADMIN
		];
		$tsLevel = $tsLevel ?? 0;
		
		if (isset($conditions[$tsLevel]) && $conditions[$tsLevel]) return true;
		// Manejo de mensajes de error
		$msg = $setMessages[$tsLevel];
		return ($message) ? $msg : ['titulo' => 'Error', 'mensaje' => $msg ?? 'Error desconocido.'];   
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
	public function currentUrl(): string {
	   $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
	   $host   = $_SERVER['HTTP_HOST'] ?? '';
	   $uri    = $_SERVER['REQUEST_URI'] ?? '';

	   return urlencode($scheme . $host . $uri);
	}

	/*
		setPagesLimit($tsPages, $start = false)
	*/
	function setPageLimit($tsLimit, $start = false, $tsMax = 0){
		if($start == false)
		$tsStart = empty($_GET['page']) ? 0 : (int) (($_GET['page'] - 1) * $tsLimit);
		else {
			$tsStart = isset($_GET['s']) ? (int)$_GET['s']: 0;
			$continue = $this->setMaximos($tsLimit, $tsMax);
			if($continue == true) $tsStart = 0;
		}
		//
		return $tsStart.','.$tsLimit;
	}
	/*
		setMaximos() :: MAXIMOS EN LAS PAGINAS
	*/
	function setMaximos($tsLimit, $tsMax){
		// MAXIMOS || PARA NO EXEDER EL NUMERO DE PAGINAS
		$page = isset($_GET['page']) ? (int)$_GET['page']: 0;
		$ban1 = ($page * $tsLimit);
		if($tsMax < $ban1){
			$ban2 = $ban1 - $tsLimit;
			if($tsMax < $ban2) return true;
		} 
		//
		return false;
	}
	/*
		getPages($tsTotal, $tsLimit)
		: PAGINACION
	*/
	function getPages($tsTotal, $tsLimit){
		//
		$tsPages = ceil($tsTotal / $tsLimit);
		// PAGINA
		$tsPage = empty($_GET['page']) ? 1 : $_GET['page'];
		// ARRAY
		$pages['current'] = $tsPage;
		$pages['pages'] = $tsPages;
		$pages['section'] = $tsPages + 1;
		$pages['prev'] = $tsPage - 1;
		$pages['next'] = $tsPage + 1;
		  $pages['max'] = $this->setMaximos($tsLimit, $tsTotal);
		// RETORNAMOS HTML
		return $pages;
	}
	 /*
		  getPagination($total, $per_page)
	 */
	 function getPagination($total, $per_page = 10){
		  // PAGINA ACTUAL
		  $page = empty($_GET['page']) ? 1 : (int) $_GET['page'];
		  // NUMERO DE PAGINAS
		  $num_pages = ceil($total / $per_page);
		  // ANTERIOR
		  $prev = $page - 1;
		  $pages['prev'] = ($page > 0) ? $prev : 0;
		  // SIGUIENTE 
		  $next = $page + 1;
		  $pages['next'] = ($next <= $num_pages) ? $next : 0;
		  // LIMITE DB
		  $pages['limit'] = (($page - 1) * $per_page).','.$per_page; 
		  // TOTAL
		  $pages['total'] = $total;
		  //
		  return $pages;
	 }
	 /**/
	// Constructs a page list.
	// $pageindex = constructPageIndex($scripturl . '?board=' . $board, $_REQUEST['start'], $num_messages, $maxindex, true);
	function pageIndex($base_url, &$start, $max_value, $num_per_page, $flexible_start = false){
		  // QUITAR EL S de la base_url
		  $base_url = explode('&s=',$base_url);
		  $base_url = $base_url[0];
		// Save whether $start was less than 0 or not.
		$start_invalid = $start < 0;
	
		// Make sure $start is a proper variable - not less than 0.
		if ($start_invalid)
			$start = 0;
		// Not greater than the upper bound.
		elseif ($start >= $max_value)
			$start = max(0, (int) $max_value - (((int) $max_value % (int) $num_per_page) == 0 ? $num_per_page : ((int) $max_value % (int) $num_per_page)));
		// And it has to be a multiple of $num_per_page!
		else
			$start = max(0, (int) $start - ((int) $start % (int) $num_per_page));
	
		$base_link = '<a class="navPages" href="' . ($flexible_start ? $base_url : strtr($base_url, array('%' => '%%')) . '&s=%d') . '">%s</a> ';
	
			// If they didn't enter an odd value, pretend they did.
			$PageContiguous = (int) (5 - (5 % 2)) / 2;
	
			// Show the first page. (>1< ... 6 7 [8] 9 10 ... 15)
			if ($start > $num_per_page * $PageContiguous)
				$pageindex = sprintf($base_link, 0, '1');
			else
				$pageindex = '';
	
			// Show the ... after the first page.  (1 >...< 6 7 [8] 9 10 ... 15)
			if ($start > $num_per_page * ($PageContiguous + 1))
				$pageindex .= '<b> ... </b>';
	
			// Show the pages before the current one. (1 ... >6 7< [8] 9 10 ... 15)
			for ($nCont = $PageContiguous; $nCont >= 1; $nCont--)
				if ($start >= $num_per_page * $nCont)
				{
					$tmpStart = $start - $num_per_page * $nCont;
					$pageindex.= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
				}
	
			// Show the current page. (1 ... 6 7 >[8]< 9 10 ... 15)
			if (!$start_invalid)
				$pageindex .= '[<b>' . ($start / $num_per_page + 1) . '</b>] ';
			else
				$pageindex .= sprintf($base_link, $start, $start / $num_per_page + 1);
	
			// Show the pages after the current one... (1 ... 6 7 [8] >9 10< ... 15)
			$tmpMaxPages = (int) (($max_value - 1) / $num_per_page) * $num_per_page;
			for ($nCont = 1; $nCont <= $PageContiguous; $nCont++)
				if ($start + $num_per_page * $nCont <= $tmpMaxPages)
				{
					$tmpStart = $start + $num_per_page * $nCont;
					$pageindex .= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
				}
	
			// Show the '...' part near the end. (1 ... 6 7 [8] 9 10 >...< 15)
			if ($start + $num_per_page * ($PageContiguous + 1) < $tmpMaxPages)
				$pageindex .= '<b> ... </b>';
	
			// Show the last number in the list. (1 ... 6 7 [8] 9 10 ... >15<)
			if ($start + $num_per_page * $PageContiguous < $tmpMaxPages)
				$pageindex .= sprintf($base_link, $tmpMaxPages, $tmpMaxPages / $num_per_page + 1);
	
		return $pageindex;
	}

	/**
	 * @access public
	 * @name setSecure
	 * @param string $value
	 * @param bool $xss
	 * @return string
	 */
	public function setSecure(string $value, bool $xss = false): string {
	   // Normalizar
	   $value = trim($value);
	   // Escapar para SQL (legacy)
	   $value = db_exec('real_escape_string', $value);
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
	   $limit = (int) ($tsUser->permisos['goaf'] ?? 0);
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
	public function parseBBCode(string $bbcode, string $type = 'normal') {
		// Class BBCode
		$parser = new BBCode();
		// Seleccionar texto
		$parser->setText($bbcode);
		//
		$buttons = [
			'normal' => ['url', 'code', 'quote', 'font', 'size', 'color', 'img', 'b', 'i', 'u', 's', 'align', 'spoiler', 'video', 'hr', 'sub', 'sup', 'table', 'td', 'tr', 'ul', 'li', 'ol', 'notice', 'info', 'warning', 'error', 'success'],
		  'firma' => ['url', 'font', 'size', 'color', 'img', 'b', 'i', 'u', 's', 'align', 'spoiler'],
		  'news' => ['url', 'b', 'i', 'u', 's']
		];
		// Determinar si el tipo es 'normal' o 'smiles', en cuyo caso usar� los botones de 'normal'
		$allowed_buttons = ($type === 'normal' || $type === 'smiles') ? $buttons['normal'] : $buttons[$type];
		$parser->setRestriction($allowed_buttons);
		// Parsear menciones si el tipo es 'normal' o 'smiles'
		if ($type === 'normal' || $type === 'smiles') {
			$parser->parseMentions();
		}
		// Parsear smiles si el tipo es 'normal', 'smiles' o 'news'
		$parser->parseSmiles();
		// Retornar resultado en HTML
		return $parser->getAsHtml();
	}
	
	/*
		 getIP
	*/
	function getIP(){
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) $ip = getenv('HTTP_CLIENT_IP');	
		elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) $ip = getenv('HTTP_X_FORWARDED_FOR');
		elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) $ip = getenv('REMOTE_ADDR');
		elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) $ip = $_SERVER['REMOTE_ADDR'];
		else $ip = 'unknown';
		return $this->setSecure($ip);
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
            default            => "$field = '" . $this->setSecure((string)$value) . "'",
        };
	   }
	   return implode(', ', $sets);
	}
	
}