<?php

declare(strict_types=1);

/**
 * @package    PHPost/Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('pages')->everybody();
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

    $pages = ['ayuda','chat','contact','protocolo','terminos-y-condiciones','privacidad','dmca'];
    if(!in_array($action, $pages, true)) {
    	$tsCore->redirectTo($tsCore->settings['url']);
    }

    $smarty->assign("tsAction", $action);

}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
