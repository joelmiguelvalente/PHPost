<?php

/**
 * @name mensajes.php
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

$tsPage  = "mensajes";
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

$unread = !isset($_GET['qt']);
if($tsContinue){

	$action = htmlspecialchars(trim($_GET['action'] ?? ''));

	switch($action){
		case '':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(2, $unread));
		break;
		case 'enviados':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(3));
		break;
		case 'respondidos':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(4));
		break;
		case 'search':
			$smarty->assign("tsMensajes",$tsMP->getMensajes(5));
		break;
		case 'leer':
			$smarty->assign("tsMensajes",$tsMP->readMensaje());
		break;
		case 'avisos':
			$aId = (int)($_GET['aid'] ?? 0);
			$dId = (int)($_GET['did'] ?? 0);
			if($aId === 0 && $dId === 0) {
				$smarty->assign("tsMensajes", $tsMonitor->getAvisos());
			} elseif($aId !== 0 && $dId === 0) {
				$smarty->assign("tsMensaje", $tsMonitor->readAviso($aId));
			} elseif($aId === 0 && $dId !== 0) {
				if($tsMonitor->delAviso($dId)) {
					$tsCore->redirectTo($tsCore->settings['url'].'/mensajes/avisos/');
				}
			}
		break;
	}
	# VARIABLE
	$smarty->assign("tsQT", $unread);
	$smarty->assign("tsAction",$action);
	
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}