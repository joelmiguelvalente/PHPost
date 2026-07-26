<?php

declare(strict_types=1);

/**
 * @package    PHPost/Helpers
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class MuroHelper {

	private string $url;

	private string $myIP;

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User,
		protected CoreHelper $CoreHelper,
		protected IP $IP
	) {
		$this->url = $Core->settings['url'];
		$this->myIP = $this->IP->getIPBinary();
	}

	/**
	 * @name setMenciones
	 * @access public
	 * @param string
	 * @return string
	 */
	public function setMenciones(string $html = ''): string {
		$tsUser = $this->User;
		return preg_replace_callback('/\B@([a-zA-Z0-9_-]{4,16})\b/', function ($matches) use ($tsUser) {
			$username = $matches[1];
			$uid = $this->User->getUserID($username);
			if (!$uid) {
				return $matches[0]; // Mención sin reemplazo
			}
			$url = "{$this->url}/@{$username}";
			return "@<a href=\"{$url}\">{$username}</a>";
		}, $html);
	}

	public function evaluatePrivacyRule(string $type, array $context, callable $deny, array $messages): void {
	   switch ($type) {
	   	case 'nobody':
	         $deny($messages['nobody']);
	      break;
	      case 'friends_mutual':
	         if (!$context['lesigoymesigue']) {
	            $deny($messages['friends_mutual']);
	         }
	      break;
	      case 'friends_any':
	         if (!$context['lesigoomesigue']) {
	           	$deny($messages['friends_any']);
	         }
	      break;
	      case 'following':
	        	if ($context['follow'] !== 1) {
	            $deny($messages['following']);
	         }
	      break;
	      case 'followers':
	         if ($context['yfollow'] !== 1) {
	            $deny($messages['followers']);
	         }
	      break;
	      case 'registered':
	         if (!$this->User->uid) {
	            $deny($messages['registered']);
	         }
	     	break;
	     	default:
	         $deny($messages['default']);
	      break;
	   }
	}

	public function getMuroConfig(array &$privacidad, array $context, string $type = ''): void {
   	// Excepciones globales
   	if ($context['isMe'] || $this->User->is_admod) {
   	   return;
   	}
   	$this->evaluatePrivacyRule($type, $context,
      	function(string $message) use (&$privacidad) {
            $privacidad['muro']['status'] = false;
            $privacidad['muro']['message'] = $message;
        	},
         [
            'nobody' => "Lo sentimos pero {$context['username']} no permite ver su muro a nadie.",
            'friends_mutual' => "Debes seguir a {$context['username']} y éste debe seguirte para poder ver su muro.",
            'friends_any' => "Debes seguir a {$context['username']} o éste debe seguirte para poder ver su muro.",
            'following' => "Debes seguir a {$context['username']} para poder ver su muro.",
            'followers' => "{$context['username']} debe seguirte para que puedas ver su muro.",
            'registered' => "Solo usuarios <a href=\"{$this->url}/registro/\">registrados</a> pueden ver el muro de {$context['username']}",
            'default' => 'No tenés permisos para ver este muro.',
        ]
    	);
	}

	public function getMuroPublicar(array &$privacidad, array $context, string $type = ''): void {
   	// Excepciones globales
   	if ($context['isMe'] || $this->User->is_admod) {
   	   return;
   	}
   	//string $privacy, array $context, callable $deny, array $messages
   	$this->evaluatePrivacyRule(
   	   $type,
   	   $context,
   	   function(string $message) use (&$privacidad) {
   	      $privacidad['muro_firma']['status'] = false;
   	      $privacidad['muro_firma']['message'] = $message;
   	   },
   	   [
   	      'nobody' => "Lo sentimos pero {$context['username']} no permite firmar su muro a nadie.",
   	      'friends_mutual' => "Debes seguir a {$context['username']} y éste debe seguirte para poder firmar su muro.",
   	      'friends_any' => "Debes seguir a {$context['username']} o éste debe seguirte para poder firmar su muro.",
   	      'following' => "Debes seguir a {$context['username']} para poder firmar su muro.",
   	      'followers' => "{$context['username']} debe seguirte para poder firmar su muro.",
   	      'registered' => "Solo usuarios registrados pueden firmar el muro de {$context['username']}",
   	      'default' => 'No tenés permisos para firmar este muro.',
   	   ]
   	);
	}

	public function sanitizeUrl(?string $url): string {
	   return Html::escape(trim((string)$url), true);
	}

	public function checkImage(string $url, bool $return): string {
	   if (strlen($url) > 300) {
	      return '0: La url de la imagen es demasiado larga.';
	   }
	   $data = @getimagesize($url);
	   if (!$data) {
	      return '0: La url ingresada no existe o no es una imagen válida.';
	   }
	   [$width, $height] = $data;
	   $min = 130;
	   $max = 2000;
	   if ($width < $min || $height < $min) {
	      return "0: Tu foto debe tener un tamaño superior a {$min}x{$min} pixeles.";
	   }
	   if ($width > $max || $height > $max) {
	      return "0: Tu foto debe tener un tamaño menor a {$max}x{$max} pixeles.";
	   }
	   return $return ? $url : "1: <span class=\"uiPhoto block\">
	   	<img class=\"rounded ratio 1x1\" style=\"max-width:200px!important;\" src=\"{$url}\"/>
	   </span>";
	}

	public function checkLink(string $url, bool $return): string|array {
	   if (strlen($url) > 400) {
	      return '0: La url es demasiado larga.';
	   }
	   $html =  $this->CoreHelper->getUrlContent($url);
	   if ($html === null) { return "0: No se ha podido extraer la información"; }
		$meta = $this->extractMeta($html);
		$title = $meta['title'] ?? '';
		$description = $meta['description'] ?? rawurldecode($url);
	  	if ($return) {
	      return [ 'title' => $title, 'url' => $description];
	   }
	   return "1: <a href=\"{$url}\" class=\"uiLink block\" title=\"{$title}\" rel=\"external\" target=\"_blank\">
	   	<span class=\"uiLink-title\">{$title}</span>
	   	<span class=\"uiLink-description block\">{$description}</span>
	   </a>";
	}

	private function extractMeta(string $html): array {
	   // Asegurar UTF-8 antes de procesar
	   if (!mb_check_encoding($html, 'UTF-8')) {
	      $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
	   }
	   $meta = [ 'title' => '', 'description' => '' ];
	   // Patrón robusto para meta tags (case-insensitive, soporta name/property)
	   $pattern = '/<meta\s+(?:name|property)\s*=\s*"(title|description|og:title|og:description)"\s+content\s*=\s*"([^"]+)"/i';
	   preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
	   foreach ($matches as $match) {
	      $key = $match[1];
	      $value = $match[2];
	      // Normalizar claves
	      if (in_array($key, ['title', 'og:title'], true)) {
	         $meta['title'] = $value;
	      } elseif (in_array($key, ['description', 'og:description'], true)) {
	         $meta['description'] = $value;
	      }
	   }
	   // Fallback: si no hay meta, buscar <title> (menos fiable, pero útil)
	   if (empty($meta['title']) && preg_match('/<title>([^<]+)<\/title>/i', $html, $t)) {
	      $meta['title'] = $t[1];
	   }
	   return $meta;
	}

	public function checkYoutube(string $url, bool $return): string|array {
	   if (! $this->CoreHelper->isSafeHttpUrl($url)) {
			Logger::error('checkYoutube()', [
				'message' => 'YouTube Check: URL inválida o SSRF detectado',
				'url' => $url
			], 'MuroHelper');
	      return '0: URL inválida. Formato requerido: https://youtube.com/watch?v=ID';
	   }
	   if (!preg_match('~(?:youtube\.com/(?:[^/]+/.+/|(?:v|e|embed|watch)/|\?|.*[?&]v=)|youtu\.be/)([a-zA-Z0-9_-]{11})~', $url, $matches) || strlen($matches[1]) !== 11) {
	      return '0: ID de video no válido (formato o longitud incorrecta)';
	   }
	   $videoId = $matches[1];
	   $youtubeUrl = 'https://www.youtube.com/watch?v=' . $videoId; // Sin espacios, safe para urlencode
	   // 
	   $html =  $this->CoreHelper->getUrlContent($youtubeUrl);
	   if ($html === null) { return "0: No se ha podido extraer la información"; }
		$meta = $this->extractMeta($html);
		$title = $meta['title'] ?? '';
		if (empty($title) || $title === 'YouTube') {
		   return '0: Video no encontrado';
		}
		$title = Html::escape($title, true);
		$desc  = $meta['description'] ? Html::escape(substr($meta['description'], 0, 160)) : '';
	   if ($return) {
	      return ['ID' => $videoId, 'title' => $title, 'desc' => $desc];
	   }
	   return "1: <div class=\"uiVideo rounded overflow-hidden relative\">
			<img src=\"https://i.ytimg.com/vi/{$videoId}/maxresdefault.jpg\" alt=\"{$title}\" class=\"object-fit-cover ratio ratio-4x3 rounded\">
			<div class=\"video-description absolute\">
				<span class=\"block\">{$title}</span>
				<p class=\"block\">{$desc}...</p>
			</div>
		</div>";
	}

	public function insertMuro(int $pid, string $body, int $type, int $date, string $visibility = 'everyone', int $adult = 0): int|false {
		if ($lastID = DB::insert('u_muro', [
			'p_user' => $pid,
			'p_user_pub' => $this->User->uid,
			'p_body' => $body,
			'p_date' => $date,
			'p_type' => $type,
			'p_ip' => $this->myIP,
			'p_visibility' => $visibility,
			'p_adult' => $adult
		])) {
			return $lastID;
		}
		return false;
	}

	public function baseReturn(int $pubId, int $pid, int $type, int $date): array {
		return [
			'pub_id'       => $pubId,
			'p_user'       => $pid,
			'p_user_pub'   => $this->User->uid,
			'adj_date'     => $date,
			'p_likes'      => 0,
			'p_type'       => $type,
			'likes'        => ['link' => 'Me gusta'],
			'user_name'    => $this->User->nick,
		];
	}

}
