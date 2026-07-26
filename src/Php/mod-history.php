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

$ctx = Controller::init('mod-history', 'members');

if($ctx->continue()) {

	$tsModeracion = Container::get(tsModeracion::class);

	// ACTION
	$action = trim($_GET['ver'] ?? '');
   	// HISTORIAL
   	$smarty->assign("tsHistory", $tsModeracion->getHistory(($action === 'fotos' ? 'fotos' : 1)));
	// ACCION?
	$smarty->assign("tsAction",$action);
	
}

Controller::render($tsAjax, $tsTitle, $tsPage);
