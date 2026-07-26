<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
   'live-stream' => ['nivel' => 2, 'template' => 'stream', 'ajax' => true]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.live.%s', $config['template']);

$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
   echo json_encode(['state' => 0, 'data' => 'Sin permisos']);
   die();
}

// CODIGO
switch($action) {
	case 'live-stream':
		// NOTIFICACIONES
		if($_POST['nots'] !== 'OFF') {
			$tsStream = $tsMonitor->getNotificaciones(true);
			$smarty->assign("tsStream", $tsStream);
		}
		// MENSAJES
		if($_POST['mps'] !== 'OFF') {
			$tsMensajes = $tsMensajes->getMensajes(1, true, 'live');
			$smarty->assign("tsMensajes", $tsMensajes);   
		}
	break;
	default:
		die('0: Este archivo no existe.');
	break;
}
