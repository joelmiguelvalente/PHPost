<?php

/**
 * @name tops.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/**
 * Inicializamos variable
 * 
 * $tsPage  = Plantilla para mostrar con este archivo.
 * $tsLevel = Nivel de acceso a esta pagina (ver faqs).
 * $tsAjax  = La respuesta sera por ajax si/no.
 */

$tsPage  = "tops";
$tsLevel = 0; 
$tsAjax  = (!isset($_GET['ajax']) && empty($_GET['ajax']));

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * En caso de problemas la variable cambia
*/
$tsContinue = true;  // CONTINUAR EL SCRIPT

/**
 * Verificamos el nivel de acceso
*/
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
   $tsPage = 'aviso';
   $tsAjax = 0;
   $smarty->assign("tsAviso", $tsLevelMsg);
   $tsContinue = false;
}

if($tsContinue) {

	// CLASE TOPS
	require_once dirname(__DIR__, 1) . "/class/c.tops.php";
	$tsTops = new tsTops($tsCore);
	//
	$fecha = (int)($_GET['fecha'] ?? 0);
	$cat = (int)($_GET['cat'] ?? 0);
	$action = (string)($_GET['action'] ?? 'posts');
	$smarty->assign("tsFecha",$fecha);
	$smarty->assign("tsCat",$cat);
	$smarty->assign("tsAction",$action);

	switch($action){
		case 'posts':
			$smarty->assign("tsTops", $tsTops->getTopPosts($fecha, $cat));
		break;
		case 'usuarios':
			$smarty->assign("tsTops", $tsTops->getTopUsers($fecha, $cat));
		break;
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}