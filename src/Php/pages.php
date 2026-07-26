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

$ctx = Controller::init('pages', 'everybody');

if($ctx->continue()) {

    $action = trim($_GET['action'] ?? '');

    $pages = ['ayuda','chat','contact','protocolo','terminos-y-condiciones','privacidad','dmca'];
    if(!in_array($action, $pages, true)) {
    	$tsCore->redirectTo($tsCore->settings['url']);
    }

    $smarty->assign("tsAction", $action);

}

Controller::render($tsAjax, $tsTitle, $tsPage);
