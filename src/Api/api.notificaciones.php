<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

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
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}
$how = trim($_POST['action'] ?? '');

match($action) {
	'notificaciones-ajax' => match($how) {
		'last' => (static function() use ($tsMonitor, $smarty) {
			$notificaciones = $tsMonitor->getNotificaciones();
			$smarty->assign("tsData", $notificaciones['data']);
		})(),
		'follow' => (static function() use ($tsMonitor, &$tsAjax) {
			echo $tsMonitor->setFollow();
			$tsAjax = false;
		})(),
		'unfollow' => (static function() use ($tsMonitor, &$tsAjax) {
			echo $tsMonitor->setUnFollow();
			$tsAjax = false;
		})(),
		'spam' => print $tsMonitor->setSpam(),
		default => null,
	},
	'notificaciones-filtro' => print $tsMonitor->setFiltro(),
};
