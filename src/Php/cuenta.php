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
 * 
 * $ctx = Controller::page(_pagina_)->requireLevel(_nivel_);
 * $ctx->getLevel() obtinene el nivel para comprobar
 * $ctx->exportLegacy() sincroniza con el sistema
 */

$ctx = Controller::page('cuenta')->requireLevel(2);
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
	//
	require_once TS_CLASS . "/c.cuenta.php";
	$tsCuenta = new tsCuenta($tsCore, $tsUser);
	$Themes = new Themes;

	if(empty($action)) {
		require_once TS_EXTRAS . "/datos.php";
		$tsMeses = require_once TS_EXTRAS . "/Meses.php";
		$tsPaises = require_once TS_EXTRAS . "/Paises.php";
		$tsEstados = require_once TS_EXTRAS . "/geodata.php";

		$minAge = (int)$tsCore->reCaptchaConfig('c_allow_edad'); // ej. 16
		$maxAge = 100;

		$today = new DateTimeImmutable('today');
		$maxDate = $today->modify("-{$minAge} years"); // ej. 2010-xx-xx
		$minDate = $today->modify("-{$maxAge} years"); // ej. 1926-xx-xx
		$smarty->assign('birthMin', $minDate->format('Y-m-d'));
		$smarty->assign('birthMax', $maxDate->format('Y-m-d'));

		$smarty->assign("tsMenuCuenta", [
			'' => 'Cuenta',
			'perfil' => 'Perfil', 
			'apariencia' => 'Apariencia',
         'block' => 'Bloqueados',
         'clave' => 'Cambiar Clave',
         'nick' => 'Cambiar Nick',
         'config' => 'Privacidad'
		]);

		// PERFIL INFO
      $tsPerfil = $tsCuenta->loadPerfil();
		$smarty->assign("tsPerfil", $tsPerfil);
		// PERFIL DATA
      $smarty->assign("tsPrivacidad", $tsPrivacidad);
		// DATOS
		$smarty->assign("tsPaises", 	$tsPaises);
		$smarty->assign("tsEstados",	$tsEstados[$tsPerfil['user_pais']]);
		$smarty->assign("tsMeses",		$tsMeses);
      // BLOQUEOS
      $smarty->assign("tsBlocks", $tsCuenta->loadBloqueos());
      $smarty->assign("tsThemes", $Themes->getAllThemes());
      $smarty->assign("tsThemeCurrent", $Themes->getUserThemeUse((int)$tsUser->uid));

	} elseif($action === 'save') {
		echo json_encode($tsCuenta->savePerfil());
	}
}

$smarty->assign("tsAccion", $_GET["accion"] ?? '');
	
if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
