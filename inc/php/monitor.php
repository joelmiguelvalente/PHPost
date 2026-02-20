<?php 

/**
 * @name monitor.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('monitor')->members();
// sincronizamos
$ctx->exportLegacy();

$tsLevelMsg = $tsCore->setLevel($ctx->getLevel(), true);
if (is_array($tsLevelMsg)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", $tsLevelMsg);
   // sincroniza nuevamente
   $ctx->exportLegacy();
}

if($ctx->continue()) {

	$action = trim($_GET['action'] ?? '');

	if(empty($action)) {
      $tsMonitor->show_type = 2;
		$notificaciones = $tsMonitor->getNotificaciones();
		$smarty->assign("tsData",$notificaciones);
      $smarty->assign("tsStatus",$_COOKIE);
   } else {
		$smarty->assign("tsData",$tsMonitor->getFollows($action));
	}

	$smarty->assign("tsAction",$action);
	
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}