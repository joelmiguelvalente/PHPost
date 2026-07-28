<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
   'feed-support'	=> ['nivel' => 0, 'template' => '', 'ajax' => false],
   'feed-version'	=> ['nivel' => 0, 'template' => '', 'ajax' => false]
];

$Response = Container::get(Response::class);

if (!array_key_exists($action, ACTIONS)) {
   $Response->text('Acción inválida', 403);
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.live.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

//
$code = [
	'title' => $tsCore->settings['titulo'],
	'url' => $tsCore->settings['url'],
	'version' => Config::app('app.version_code')
];
$key = json_encode($code, JSON_FORCE_OBJECT);

$type = explode('-', $action)[1];
$endpoint = file_get_contents("http://phpost-api.test/v1/scripts/phpost/{$type}");
$Response->contentType('application/json');
// CODIGO

match($action) {
	'feed-support', 'feed-version' => $action === 'feed-version' ? (static function() use ($tsCore) {
		$version = Config::app('app.version');
		$version_code = Config::app('app.version_code');
		$time = time();
		if ($tsCore->settings['version'] !== $version) {
			DB::update('w_configuracion', [
				'version' => $version,
				'version_code' => $version_code,
			], 'phpost_id = :id', ['id' => 1]);
			DB::update('w_stats', [
				'stats_time_upgrade' => $time,
			], 'stats_no = :id', ['id' => 1]);
		}
	})() : null,
	default => $Response->json([
         'error' => true,
         'message' => 'Endpoint inválido'
	], 404);
};
