<?php

/**
 * @name buscador.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('buscador')->everybody();
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

	$query = trim($_GET['query'] ?? '');
   $engine = trim($_GET['engine'] ?? 'web');
   $category = (int)($_GET['category'] ?? 0);
   $autor = trim($_GET['autor'] ?? '');
	//
	require_once TS_CLASS . "/c.buscador.php";
	$tsBuscador = new tsBuscador($tsCore, $tsUser);

	if(!in_array('', [$query, $autor], true) && ($engine !== 'google')) {
	   $smarty->assign("tsResults", $tsBuscador->getQuery());
	}
	//
	$smarty->assign("tsQuery", 	$query);
   $smarty->assign("tsEngine", 	$engine);
   $smarty->assign("tsCategory", $category);
   $smarty->assign("tsAutor", 	$autor);

}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}