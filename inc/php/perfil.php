<?php

/**
 * @name registro.php
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

$tsPage  = "perfil";
$tsLevel = 0; 
$tsAjax  = (!isset($_GET['ajax']) && empty($_GET['ajax']));
$tsContinue = true;
	
require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

// VERIFICAMOS EL NIVEL DE ACCSESO ANTES CONFIGURADO
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg){	
	$tsPage = 'aviso';
	$tsAjax = 0;
	$smarty->assign("tsAviso",$tsLevelMsg);
	//
	$tsContinue = false;
}

if($tsContinue) {

	$username = $tsCore->setSecure($_GET['user'] ?? '');
	$usuario = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_name, user_activo, user_baneado FROM u_miembros WHERE user_name = '{$username}'"));
	// EXISTE?
	if(empty($usuario['user_id']) || ((int)$usuario['user_activo'] !== 1 && !$tsUser->permisos['movcud'] && !$tsUser->is_admod) || ((int)$usuario['user_baneado'] !== 0 && !$tsUser->permisos['movcus'] && !$tsUser->is_admod)) {
		$tsPage = 'aviso';
		$tsAjax = 0;
		$smarty->assign("tsAviso", [
			'titulo' => 'Opps!', 
			'mensaje' => (empty($usuario['user_id']) ? 'El usuario no existe' : 'La cuenta de '.$usuario['user_name'].' se encuentra inhabilitada' ), 
			'but' => 'Ir a p&aacute;gina principal'
		]);
	} else {
		//
		require_once dirname(__DIR__, 1) . "/utils/Extras.php";
		require_once dirname(__DIR__, 1) . "/class/c.cuenta.php";
		require_once dirname(__DIR__, 1) . "/class/c.muro.php";
		$tsPaises = require_once dirname(__DIR__, 1) . "/extras/Paises.php";

		$tsCuenta = new tsCuenta();
		$Extras = new Extras();

		$tsInfo = $tsCuenta->loadHeadInfo((int)$usuario['user_id']);
		$tsInfo['uid'] = $usuario['user_id'];
		// IS ONLINE?
		$tsInfo['status'] = $Extras->isOnline($tsInfo, (int)$tsCore->settings['c_last_active']);
		// GENERAL
		$tsGeneral = $tsCuenta->loadGeneral($usuario['user_id']);
		$tsInfo['nick'] = $tsInfo['user_name'];
		$tsInfo = array_merge($tsInfo,$tsGeneral);
		// PAIS
		$tsInfo['user_pais'] = $tsPaises[$tsInfo['user_pais'] ?? 'XX'];
		// LO SIGO?
		$tsInfo['follow'] = $tsCuenta->iFollow($usuario['user_id']);
		// ME SIGUE?
		$tsInfo['yfollow'] = $tsCuenta->yFollow($usuario['user_id']);
		// MANDAR A PLANTILLA
		$smarty->assign("tsInfo", $tsInfo);
		$smarty->assign("tsRedes", $tsCuenta->redes);
		$smarty->assign("tsGeneral", $tsGeneral);
		
		// MURO
		$tsMuro = new tsMuro();
		// PERMISOS
		$priv = $tsMuro->getPrivacity((int)$usuario['user_id'], $username, $tsInfo['follow'], $tsInfo['yfollow']);
		// SE PERMITE VER EL MURO?
		if($priv['m']['v'] === true) {
			// CARGAR HISTORIA
			if(!empty($_GET['pid'])) {
				$pub_id = $tsCore->setSecure($_GET['pid']);
				$story = $tsMuro->getStory($pub_id, $usuario['user_id']);
				//
				if(!is_array($story)) {
					$tsPage = 'aviso';
					$smarty->assign("tsAviso", [
						'titulo' => 'Opps...', 
						'mensaje' => $story, 
						'but' => 'Ir a pagina principal', 
						'link' => $tsCore->settings['url']
					]);
				} else {
					$story['data'][1] = $story;
					$smarty->assign("tsMuro", $story);
					$smarty->assign("tsType","story");
				}
			} elseif((int)$tsCore->settings['c_allow_portal'] === 0 && (int)$tsInfo['uid'] === (int)$tsUser->uid) {
				$smarty->assign("tsMuro", $tsMuro->getNews());
				$smarty->assign("tsType", "news");
			}else{
				$smarty->assign("tsMuro", $tsMuro->getWall($usuario['user_id']));
				$smarty->assign("tsType", "wall");
			}
		}
		$smarty->assign("tsPrivacidad",$priv);
		// TITULO
		$tsTitle = "Perfil de {$tsInfo['nick']} | {$tsCore->settings['titulo']}";
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}