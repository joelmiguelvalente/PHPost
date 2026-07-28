<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
	'upload-avatar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
	'upload-crop'   => ['nivel' => 2, 'template' => '', 'ajax' => false],
	'upload-images' => ['nivel' => 2, 'template' => '', 'ajax' => false],
];

if (!array_key_exists($action, ACTIONS)) {
	http_response_code(403);
	exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.upload.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
$tsUpload = Container::get(tsUpload::class);

// CODIGO
match($action) {
	'upload-avatar' => (static function() use ($tsUpload) {
		$file = $_FILES['file'] ?? null;
		$url  = $_POST['url'] ?? null;
		$result = $tsUpload->uploadTempImage($file, $url);
		echo json_encode($result);
	})(),
	'upload-crop' => (static function() use ($tsUpload, $tsUser) {
		echo json_encode($tsUpload->cropAvatarWebp((int)$tsUser->uid));
		DB::update('u_perfil', ['p_avatar' => 1], 'user_id = :uid', ['uid' => $tsUser->uid]);
	})(),
	'upload-images' => print json_encode(['error' => 'No implementado']),
};
