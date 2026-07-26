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

$ctx = Controller::init('usuarios', 'everybody');

if($ctx->continue()) {

	$tsPaises = require_once TS_EXTRAS . "/Paises.php";
	$smarty->assign("tsPaises", $tsPaises);
	// USUARIOS
	$tsUsers = $tsUser->getUsuarios();
	$smarty->assign("tsUsers", $tsUsers['data']);
	$smarty->assign("tsPages", $tsUsers['pages']);
	$smarty->assign("tsTotal", $tsUsers['total']);
	// FILTROS
	$smarty->assign("tsFiltro", [
		'online' => trim($_GET['online'] ?? ''),
		'avatar' => trim($_GET['avatar'] ?? ''),
		'sex' 	 => trim($_GET['sexo'] ?? ''),
		'pais' 	 => trim($_GET['pais'] ?? ''),
		'rango'  => trim($_GET['rango'] ?? '')
	]);
	// RANGOS
	$smarty->assign("tsRangos", $tsUser->getAllRangos());

}

Controller::render($tsAjax, $tsTitle, $tsPage);
