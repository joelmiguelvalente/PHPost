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

// PARA LAS FOTOS...
$action = trim($_GET['action'] ?? '');

$isPrivate = (int)$tsCore->settings['c_fotos_private'] === 1;
$access = ($isPrivate && !in_array($action, ['', 'ver'], true)) ? 'members' : 'everybody';
$ctx = Controller::init('fotos', $access);

if($ctx->continue()) {

	$tsFotos = Container::get(tsFotos::class);

	$routes = [
	    ''        => fn() => $tsFotos->renderHome(),
	    'agregar' => fn() => $tsFotos->handleAgregar(),
	    'editar'  => fn() => $tsFotos->handleEditar(),
	    'borrar'  => fn() => $tsFotos->delFoto(),
	    'ver'     => fn() => $tsFotos->handleVer(),
	    'album'   => fn() => $tsFotos->handleAlbum(),
	];
	$route = $routes[$action] ?? null;

	if ($route === null) {
		$tsPage = 'aviso';
		$tsFotos->assignAviso($smarty, 'Acción no reconocida.', 'Ir a Fotos');
  	} else {
    	$result = $route();
    	if ($action === 'borrar') {
      		$tsAjax = 1;
      		echo $result;
    	}
	    if (is_array($result)) {
	      	if (isset($result['tsPage'])) {
	        	$tsPage = $result['tsPage'];
	      	}
	      	if (isset($result['tsTitle'])) {
	        	$tsTitle = $result['tsTitle'];
	      	}
	    }
	}

	$smarty->assign("tsAction",$action);
	
}

Controller::render($tsAjax, $tsTitle, $tsPage);
