<?php

/**
 * @name cuenta.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/**
 * Inicializamos variable
 * 
 * $tsPage  	= Plantilla para mostrar con este archivo.
 * $tsLevel 	= Nivel de acceso a esta pagina (ver faqs).
 * $tsAjax  	= La respuesta sera por ajax si/no.
 * $tsContinue	= Continuar con la ejecución
 */

$tsPage  = "cuenta";
$tsLevel = 2; 
$tsAjax  = (!isset($_GET['ajax']) && empty($_GET['ajax']));
$tsContinue = true;

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
// VERIFICAMOS EL NIVEL DE ACCESO ANTES CONFIGURADO
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg){	
	$tsPage = 'aviso';
	$tsAjax = 0;
	$smarty->assign("tsAviso",$tsLevelMsg);
	//
	$tsContinue = false;
}

if($tsContinue) {

	$action = trim($_GET['action'] ?? '');
	//
	require_once dirname(__DIR__, 1) . "/class/c.cuenta.php";
	$tsCuenta = new tsCuenta($tsCore, $tsUser);

	if(empty($action)) {
		require_once dirname(__DIR__, 1) . "/extras/datos.php";
		$tsMeses = require_once dirname(__DIR__, 1) . "/extras/Meses.php";
		$tsPaises = require_once dirname(__DIR__, 1) . "/extras/Paises.php";
		$tsEstados = require_once dirname(__DIR__, 1) . "/extras/geodata.php";

		$minAge = (int)$tsCore->settings['c_allow_edad']; // ej. 16
		$maxAge = 100;

		$today = new DateTimeImmutable('today');
		$maxDate = $today->modify("-{$minAge} years"); // ej. 2010-xx-xx
		$minDate = $today->modify("-{$maxAge} years"); // ej. 1926-xx-xx
		$smarty->assign('birthMin', $minDate->format('Y-m-d'));
		$smarty->assign('birthMax', $maxDate->format('Y-m-d'));

		$smarty->assign("tsMenuCuenta", [
			'' => 'Cuenta',
			'perfil' => 'Perfil', 
         'block' => 'Bloqueados',
         'clave' => 'Cambiar Clave',
         'nick' => 'Cambiar Nick',
         'config' => 'Privacidad'
		]);

		// PERFIL INFO
      $tsPerfil = $tsCuenta->loadPerfil();
		$smarty->assign("tsPerfil", $tsPerfil);
		#var_dump($tsPerfil);
		// PERFIL DATA
      $smarty->assign("tsPrivacidad", $tsPrivacidad);
		#var_dump($tsPrivacidad);
		// DATOS
		$smarty->assign("tsPaises", 	$tsPaises);
		$smarty->assign("tsEstados",	$tsEstados[$tsPerfil['user_pais']]);
		$smarty->assign("tsMeses",		$tsMeses);
      // BLOQUEOS
      $smarty->assign("tsBlocks", $tsCuenta->loadBloqueos());
        
	} elseif($action === 'save'){
		echo json_encode($tsCuenta->savePerfil());
	}
}

$smarty->assign("tsAccion", $_GET["accion"] ?? '');
	
if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}