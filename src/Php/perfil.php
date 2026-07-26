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

$ctx = Controller::init('perfil', 'everybody');

if($ctx->continue()) {

	$username = $_GET['user'] ?? '';
	$usuario = DB::fetch("SELECT user_id, user_name, user_activo, user_baneado FROM u_miembros WHERE user_name = :name", [
		'name' => $username
	]);
	// EXISTE?
	if(empty($usuario['user_id']) || ((int)$usuario['user_activo'] !== 1 && 
		!$tsUser->permiso('moderacion.usuarios.ver_desactivados') && !$tsUser->is_admod) || ((int)$usuario['user_baneado'] !== 0 && !$tsUser->permiso('moderacion.usuarios.ver_suspendidos') && !$tsUser->is_admod)) {
		$tsPage = 'aviso';
		$tsAjax = 0;
		$smarty->assign("tsAviso", [
			'titulo' => 'Opps!', 
			'mensaje' => (empty($usuario['user_id']) ? 'El usuario no existe' : 'La cuenta de '.$usuario['user_name'].' se encuentra inhabilitada' ), 
			'but' => 'Ir a página principal'
		]);
	} else {
		//
		$tsPaises = require_once TS_EXTRAS . "/Paises.php";

		$tsCuenta = Container::get(tsCuenta::class);
		$UserHelper = Container::get(UserHelper::class);

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
		$tsMuro = Container::get(tsMuro::class);
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

Controller::render($tsAjax, $tsTitle, $tsPage);
