<?php

/**
 * @name perfil.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);
	
require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::page('perfil')->everybody();
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

	$username = $tsCore->setSecure($_GET['user'] ?? '');
	$usuario = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_name, user_activo, user_baneado FROM u_miembros WHERE user_name = '{$username}'"));
	// EXISTE?
	if(empty($usuario['user_id']) || ((int)$usuario['user_activo'] !== 1 && 
		!$tsUser->permiso('moderacion.usuarios.ver_desactivados') && !$tsUser->is_admod) || ((int)$usuario['user_baneado'] !== 0 && !$tsUser->permiso('moderacion.usuarios.ver_suspendidos') && !$tsUser->is_admod)) {
		$tsPage = 'aviso';
		$tsAjax = 0;
		$smarty->assign("tsAviso", [
			'titulo' => 'Opps!', 
			'mensaje' => (empty($usuario['user_id']) ? 'El usuario no existe' : 'La cuenta de '.$usuario['user_name'].' se encuentra inhabilitada' ), 
			'but' => 'Ir a p&aacute;gina principal'
		]);
	} else {
		//
		require_once TS_HELPERS . "/UserHelper.php";
		require_once TS_CLASS . "/c.cuenta.php";
		require_once TS_CLASS . "/c.muro.php";
		$tsPaises = require_once TS_EXTRA . "/Paises.php";

		$tsCuenta = new tsCuenta($tsCore, $tsUser);
		$UserHelper = new UserHelper($tsCore);

		$tsInfo = $tsCuenta->loadHeadInfo((int)$usuario['user_id']);
		$tsInfo['uid'] = (int)$usuario['user_id'];
		// IS ONLINE?
		$tsInfo['status'] = $UserHelper->getStatusCode((int)$tsInfo['user_lastactive'], (int)$tsInfo['user_baneado']);
		// GENERAL
		$tsGeneral = $tsCuenta->loadGeneral($tsInfo['uid']);
		$tsInfo['nick'] = $tsInfo['user_name'];
		//$tsInfo = array_merge($tsInfo,$tsGeneral);
		// PAIS
		$tsInfo['user_pais'] = $tsPaises[$tsInfo['user_pais']];
		// LO SIGO?
		$tsInfo['follow'] = $tsCuenta->isFollowed($tsInfo['uid'], true);
		// ME SIGUE?
		$tsInfo['yfollow'] = $tsCuenta->isFollowed($tsInfo['uid'], false);
		// MANDAR A PLANTILLA
		$smarty->assign("tsInfo", $tsInfo);
		$smarty->assign("tsRedes", $tsCuenta->redes);
		$smarty->assign("tsGeneral", $tsGeneral);
		
		// MURO
		$tsMuro = new tsMuro($tsCore, $tsUser);
		// PERMISOS
		$privacidad = $tsMuro->getPrivacity((int)$tsInfo['user_id'], $username, (int)$tsInfo['follow'], (int)$tsInfo['yfollow']);
		// SE PERMITE VER EL MURO?
		if($privacidad['muro']['status']) {
			// CARGAR HISTORIA
			if(!empty($_GET['pid'])) {
				$pub_id = (int)($_GET['pid'] ?? 0);
				$story = $tsMuro->getStory($pub_id, $tsInfo['user_id']);
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
					$smarty->assign("tsType", "story");
				}
			} elseif((int)$tsCore->settings['c_allow_portal'] === 0 && (int)$tsInfo['uid'] === (int)$tsUser->uid) {
				$smarty->assign("tsMuro", $tsMuro->getNews());
				$smarty->assign("tsType", "news");
			}else{
				$smarty->assign("tsMuro", $tsMuro->getWall((int)$tsInfo['user_id']));
				$smarty->assign("tsType", "wall");
			}
		}
		$smarty->assign("tsPrivacidad", $privacidad);
		// TITULO
		$tsTitle = "Perfil de {$tsInfo['nick']} | {$tsCore->settings['titulo']}";
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
