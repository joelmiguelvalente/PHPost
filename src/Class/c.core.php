<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

use Uri\Rfc3986\Uri;

class tsCore {

	public array $settings;

	public int $uid;

	public function __construct() {
		$this->settings = $this->getSettings();
		//
		if(isset($_GET['do']) && in_array($_GET['do'], ['portal', 'posts'])) {
			$this->settings['news'] = $this->getNews();
		}
	}


	/**
	 * @access public
	 * @name getSettings()
	 * @return array
	*/
	public function getSettings(): array {
		return DB::fetch("SELECT * FROM w_configuracion WHERE phpost_id = :id", ['id' => 1]);
	}

	/**
	 * @access public
	 * @name reCaptchaConfig()
	 * @return string|int|array
	*/
	public function reCaptchaConfig(string $type = ''): string|int|array {
		$data = DB::fetch("SELECT c_reg_active, c_reg_activate, c_reg_rango, c_met_welcome, c_message_welcome, c_allow_edad, captcha_provider, public_key, secret_key FROM w_registro WHERE reg_id = :id", ['id' => 1]);

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
			(SELECT COUNT(post_id) FROM p_posts WHERE post_status = 'revision') as revposts,
			(SELECT COUNT(cid) FROM p_comentarios WHERE c_status = 1) as revcomentarios,
			(SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'post') as repposts,
			(SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'mensaje') as repmps,
			(SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'usuario') as repusers,
			(SELECT COUNT(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 'foto') as repfotos,
			(SELECT COUNT(susp_id) FROM u_suspension) as suspusers,
			(SELECT COUNT(post_id) FROM p_posts WHERE post_status = 'eliminado') as pospelera,
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
		$data['t_path'] = $_SESSION['theme_path'] ?? $data['tema'];
		$data['t_url'] = "{$this->settings['url']}/themes/{$data['t_path']}";
		return $data;
	}

	public function getThemePath(): string {
		$path = $this->getTema();
		return $path['t_path'] ?? 'default';
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
			$replace = ((int)$badword['type'] === 1) ? '<img title="' . Html::escape($badword['word']) . '" src="' . Html::escape($badword['swop']) . '" align="absmiddle"/>' : "{$badword['swop']} ";
			$censurar = str_ireplace($search, $replace, $censurar);
		}
		return $censurar;
	}

	/**
	 * @access public
	 * @name redirectTo
	 * @param string
	 * @return never
	 */
	public function redirectTo(string $url = '/'): never {
	    Container::get(Redirector::class, [$this->settings['url']])->to($url);
	}

	/**
	 * @access public
	 * @name redirectTo
	 * @param string
	 * @return never
	 */
	public function redirectAdmin(string $action = '', string $param = 'save', string $aux = ''): never {
		$this->redirectTo("/admin/{$action}?{$param}=true{$aux}");
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
		$host = Container::get(Uri::class, [$url])->getHost();
		if (!$host) {
			return '';
		}
		$parts = explode('.', $host);
		$count = count($parts ?? []);
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
	public function currentUrl(bool $urlencode = false): string {
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
		$uri = $scheme . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

		return $urlencode ? urlencode($uri) : $uri;
	}

	/*
		parseBBCode($bbcode)
	*/
	public function parseBBCode(string $bbcode = '', string $type = 'normal', ?int $id = 0): string {
		// Class BBCode
		$parser = Container::get(BBCode::class);
		$parser->route = $this->settings['url'];
		// Seleccionar texto
		$parser->id = $id;
		$parser->setText($bbcode);
		$bbcodes = $parser->bbcodeAllow();
		//
		$restriction = match($type) {
			'firma' => array_slice($bbcodes, 0, 11),
			'news' => array_slice($bbcodes, 0, 5),
			default => $bbcodes,
		};
		$parser->setRestriction($restriction);
		// Parsear menciones si el tipo es 'normal' o 'smiles'
		if ($type === 'normal' || $type === 'smiles') {
			$parser->parseMentions();
		}
		$parser->parseSmiles();
		return $parser->getAsHtml();
	}
	
}
