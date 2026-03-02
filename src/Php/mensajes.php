<?php

/**
 * @name mensajes.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('mensajes')->members();
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

	$unread = !isset($_GET['qt']);
	$action = trim($_GET['action'] ?? '');

	switch($action){
		case '':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(2, $unread));
		break;
		case 'enviados':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(3));
		break;
		case 'respondidos':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(4));
		break;
		case 'search':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(5));
		break;
		case 'leer':
			$smarty->assign("tsMensajes",$tsMP->readMensaje());
		break;
		case 'avisos':
			$aid = (int)($_GET['aid'] ?? 0);
			$did = (int)($_GET['did'] ?? 0);
			if($aid === 0 && $did === 0) {
				$smarty->assign("tsMensajes", $tsMonitor->getAvisos());
			} elseif($aid !== 0 && $did === 0) {
				$smarty->assign("tsMensaje", $tsMonitor->readAviso($aid));
			} elseif($aid === 0 && $did !== 0) {
				if($tsMonitor->delAviso($did)) {
					$tsCore->redirectTo($tsCore->settings['url'].'/mensajes/avisos/');
				}
			}
		break;
	}
	# VARIABLE
	$smarty->assign("tsQT", $unread);
	$smarty->assign("tsAction",$action);
	
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}