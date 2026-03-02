<?php

/**
 * @name api.feed.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'feed-support'	=> ['nivel' => 0, 'template' => '', 'ajax' => false],
   'feed-version'	=> ['nivel' => 0, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.live.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

require_once TS_HELPERS . '/CoreHelper.php';
require_once TS_UTILS . '/Extras.php';
$CoreHelper = new CoreHelper;
$Extras = new Extras;

//
$code = [
	'w' => $tsCore->settings['titulo'], 
	's' => $tsCore->settings['slogan'], 
	'u' => str_replace(['http://','https://'], '', $tsCore->settings['url']), 
	'v' => $tsCore->settings['version_code'], 
	'a' => $tsUser->nick, 
	'i' => $tsUser->uid
];
$key = base64_encode(serialize($code));

$type = explode('-', $action)[1];
$endpoint = file_get_contents("http://phpost-api.test/v1/scripts/phpost/{$type}");
header('Content-Type: application/json; charset=UTF-8');
// CODIGO

switch($action) {
	case 'feed-support':
	case 'feed-version':
		// Al no existir el endpoint genera error 522!
		//echo $endpoint;
		if($action === 'feed-version') {
			$version = 'PHPost 4.3.31';
			$version_code = $Extras->slugify($version, '_');
			$time = time();
			# ACTUALIZAR VERSIÓN
			if($tsCore->settings['version'] !== $version) {
				db_exec([__FILE__, __LINE__], 'query', "UPDATE w_configuracion SET version = '$version', version_code = '$version_code' WHERE phpost_id = 1 LIMIT 1");
				db_exec([__FILE__, __LINE__], 'query', "UPDATE w_stats SET stats_time_upgrade = $time WHERE stats_no = 1 LIMIT 1");
			}
		}
		
	break;
	default:
      http_response_code(404);
      echo json_encode([
         'error' => true,
         'message' => 'Endpoint inválido'
      ], JSON_THROW_ON_ERROR);
      break;
}