<?php

declare(strict_types=1);

/**
 * @package    Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::init('monitor', 'members');

if($ctx->continue()) {

	$action = trim($_GET['action'] ?? '');

	if(empty($action)) {
      	$tsMonitor->show_type = 2;
		$notificaciones = $tsMonitor->getNotificaciones();
		$smarty->assign("tsData", $notificaciones);
      	$smarty->assign("tsStatus", $_COOKIE);
   } else {
		$smarty->assign("tsData", $tsMonitor->getFollows($action));
	}

	$smarty->assign("tsAction",$action);
	
}

Controller::render($tsAjax, $tsTitle, $tsPage);
