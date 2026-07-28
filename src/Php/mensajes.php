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
$ctx = Controller::init('mensajes', 'members');

if($ctx->continue()) {

	$unread = (isset($_GET['qt']) && $_GET['qt'] === 'unread');
	$action = trim($_GET['action'] ?? '');

	$first = match($action) {
		'enviados' 	  => 3,
		'respondidos' => 4,
		'search' 	  => 5,
		default 	  => 2
	};
	$second = ($action === '') ? $unread : '';

	match($action) {
		'', 'enviados', 'respondidos', 'search' => $smarty->assign("tsMensajes",$tsMensajes->getMensajes($first, $second)),
		'leer' => $smarty->assign("tsMensajes",$tsMensajes->readMensaje()),
		'avisos' => (function() use ($smarty, $tsMonitor, $tsCore) {
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
		})(),
	};
	# VARIABLE
	$smarty->assign("tsQT", $unread);
	$smarty->assign("tsAction",$action);
	
}

Controller::render($tsAjax, $tsTitle, $tsPage);
