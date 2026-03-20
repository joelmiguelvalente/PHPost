<?php

/**
 * @name mod-history.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('mod-history')->members();
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

	require_once TS_CLASS . "/c.moderacion.php";
	$tsMod = new tsMod($tsCore, $tsUser);

	// ACTION
	$action = trim($_GET['ver'] ?? '');
   // HISTORIAL
   $smarty->assign("tsHistory", $tsMod->getHistory(($action === 'fotos' ? 'fotos' : 1)));

	// ACCION?
	$smarty->assign("tsAction",$action);
	
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}