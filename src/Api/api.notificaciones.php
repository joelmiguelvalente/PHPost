<?php

/**
 * @name src/Api/api.notificacion.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'notificaciones-ajax' => ['nivel' => 2, 'template' => 'ajax', 'ajax' => true],
   'notificaciones-filtro' => ['nivel' => 2, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.notificaciones.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}
$how = trim($_POST['action'] ?? '');

switch($action) {
	case 'notificaciones-ajax':
		switch($how){
			case 'last':
				$notificaciones = $tsMonitor->getNotificaciones();
				$smarty->assign("tsData", $notificaciones['data']);
			break;
			case 'follow':
				echo $tsMonitor->setFollow();
				$tsAjax = false;
			break;
			case 'unfollow':
				echo $tsMonitor->setUnFollow();
				$tsAjax = false;
			break;
			case 'spam':
				echo $tsMonitor->setSpam();
			break;
		}
	break;
	case 'notificaciones-filtro':
		echo $tsMonitor->setFiltro();
	break;
}
