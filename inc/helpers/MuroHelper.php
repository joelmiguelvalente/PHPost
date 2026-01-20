<?php

/**
 * @name MuroHelper.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}
require_once __DIR__ . '/CoreHelper.php';
require_once dirname(__DIR__, 1) . '/utils/IP.php';

final class MuroHelper {

	private string $url;
	protected tsCore $Core;
	protected tsUser $User;
	protected CoreHelper $CoreHelper;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->url = $Core->settings['url'];
		$this->Core = $Core;
		$this->User = $User;
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
			$url = "{$this->url}/perfil/{$username}";
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
   	$this->evaluatePrivacyRule(
      	$type,
      	$context,
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
	   return $this->Core->setSecure(trim((string)$url), true);
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
	   $max = 1024;
	   if ($width < $min || $height < $min) {
	      return "0: Tu foto debe tener un tamaño superior a {$min}x{$min} pixeles.";
	   }
	   if ($width > $max || $height > $max) {
	      return "0: Tu foto debe tener un tamaño menor a {$max}x{$max} pixeles.";
	   }
	   return $return ? $url : "1: <img src=\"{$url}\"/>";
	}

	public function checkLink(string $url, bool $return): string|array {
	   if (strlen($url) > 400) {
	      return '0: La url es demasiado larga.';
	   }
	   $html = (new CoreHelper)->getUrlContent($url);
	   if (!$html) {
	      return '0: El enlace ingresado no es válido o no está disponible.';
	   }
	   if (!preg_match('/<title>(.*?)<\/title>/is', $html, $matches)) {
	      return '0: La url ingresada no es una página web válida.';
	   }
	   $title = $this->Core->setSecure(trim($matches[1]), true);
	  	if ($return) {
	      return [ 'title' => $title, 'url' => rawurldecode($url)];
	   }
	   return "1: <a href=\"{$url}\" target=\"_blank\" class=\"big a_blue\">{$title}</a><br><span class=\"desc\">{$url}</span>";
	}

	public function checkYoutube(string $url, bool $return): string|array {
	   if (!preg_match('~v=([a-zA-Z0-9_-]{11})~', $url, $m)) {
	      return '0: La dirección del video no es válida.';
	   }
	   $videoId = $m[1];
	   $meta = @get_meta_tags("https://www.youtube.com/watch?v={$videoId}");
	   if (empty($meta['title'])) {
	      return '0: El video no existe o fue eliminado.';
	   }

	   $title = $this->Core->setSecure($meta['title'], true);
	   $desc  = isset($meta['description']) ? $this->Core->setSecure(substr(html_entity_decode($meta['description']), 0, 160)) : '';

	   if ($return) {
	     	return [
	     	   'id'    => $videoId,
	     	   'title' => $title,
	     	   'desc'  => $desc,
	     	];
	   }

	   return '1: <div class="vContent">
	   	<img src="https://img.youtube.com/vi/'.$videoId.'/0.jpg" class="thumb"/>
	      <div class="vDesc">
	      	<strong><a href="https://www.youtube.com/watch?v='.$videoId.'" target="_blank" class="a_blue">'.$title.'</a></strong>
	      	<div style="margin-top:5px">'.$desc.'</div>
	      </div>
	   </div>';
	}

	public function insertMuro(int $pid, string $body, int $type, int $date, string $visibility = 'everyone', int $adult = 0): int|false {
		$ip = (new IP)->executeIP();
		$sql = "INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip, p_visibility, p_adult)  VALUES ({$pid}, {$this->User->uid}, '{$body}', {$date}, {$type}, '{$ip}', '{$visibility}', {$adult})";
		if (!db_exec([__FILE__, __LINE__], 'query', $sql)) {
			return false;
		}
		return db_exec('insert_id');
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