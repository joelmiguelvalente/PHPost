<?php

/**
 * @name tops.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::page('tops')->everybody();
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

	// CLASE TOPS
	require_once TS_CLASS . "/c.tops.php";
	$tsTops = new tsTops($tsCore);
	//
	$fecha = (int)($_GET['fecha'] ?? 0);
	$cat = (int)($_GET['cat'] ?? 0);
	$action = (string)($_GET['action'] ?? 'posts');
	$smarty->assign("tsFecha",$fecha);
	$smarty->assign("tsCat",$cat);
	$smarty->assign("tsAction",$action);

	switch($action){
		case 'posts':
			$smarty->assign("tsTops", $tsTops->getTopPosts($fecha, $cat));
		break;
		case 'usuarios':
			$smarty->assign("tsTops", $tsTops->getTopUsers($fecha, $cat));
		break;
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}