<?php

/**
 * @name src/Api/api.upload.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

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
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . '/c.upload.php';
$tsUpload = new tsUpload();

// CODIGO
switch($action){
	case 'upload-avatar':
		$file = $_FILES['file'] ?? null;
		$url  = $_POST['url'] ?? null;
		$result = $tsUpload->uploadTempImage($file, $url);
		echo json_encode($result);
	break;
	case 'upload-crop':
		echo json_encode($tsUpload->cropAvatarWebp((int)$tsUser->uid));
		db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_perfil SET p_avatar = 1 WHERE user_id = ' . (int)$tsUser->uid);
	break;
	case 'upload-images':
		echo json_encode(['error' => 'No implementado']);
	break;
}
