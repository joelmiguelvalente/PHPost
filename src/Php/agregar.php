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

$ctx = Controller::init('agregar', 'members');

if(!$tsUser->is_member) {
	Container::get(Response::class)->redirect($Routes->route('url'));
	die;
}

if($ctx->continue()) {

	$action = trim($_GET['action'] ?? '');

	if($action === 'editar' || isset($_POST['title'])) {
		// CLASE
		require_once TS_CLASS . "/c.agregar.php";
		$tsAgregar = new tsAgregar($tsCore, $tsUser);
	}

	if(is_numeric($action)) {
		require_once TS_CLASS . "/c.borradores.php";
		$tsBorradores = new tsBorradores($tsCore, $tsUser);
		$tsBorrador = $tsBorradores->getDraft();
		$smarty->assign("tsDraft", $tsBorrador);
		//
	} elseif($action === 'editar') {
		// GUARDAR
		if(isset($_POST['title'])) {
			$postSave = $tsAgregar->savePost();
			if($postSave) {
				$postUrl = $tsAgregar->getPostDataID();
				// NOS VAMOS AL POST
				$tsCore->redirectTo($postUrl);
			} else {
				$tsPage = 'aviso';
				$smarty->assign("tsAviso", [
					'titulo' => 'Oops!', 
					'mensaje' => $postSave, 
					'but' => 'Volver', 
					'link' => 'javascript:history.go(-1)'
				]);
			}
		// EDITAR
		} else {
			$draft = $tsAgregar->getEditPost();
			if(!is_array($draft)) {
				$tsPage = 'aviso';
				$smarty->assign("tsAviso", [
					'titulo' => 'Opps...', 
					'mensaje' => $draft, 
					'but' => 'Ir a pagina principal', 
					'link' => "{$tsCore->settings['url']}"
				]);
			} else $smarty->assign("tsDraft", $draft);
		}
		//
		$smarty->assign("tsAction", $action);
		$smarty->assign("tsPid", $_GET['id']);
		
	} elseif(isset($_POST['title'])) {
		$tsPost = $tsAgregar->newPost();

		$tsPage = 'aviso';
		$tsAjax = 0;
		if($tsPost > 0) {
			$link = $tsAgregar->getPostDataID($tsPost);
			if(!$tsUser->is_admod && ($tsUser->permiso('global.posts.revisar') || (int)$tsCore->settings['c_desapprove_post'] === 1)) {
				$smarty->assign("tsAviso", [
					'titulo' => 'Bien!', 
					'mensaje' => "El post <b>{$_POST['title']}</b> fue agregado. \nDeberá esperar su aprobación.",
					'but' => 'Volver a la home', 
					'link' => $tsCore->settings['url']
				]);
			} else {
				Container::get(Response::class)->redirect($link);
				die;
			}
		} elseif($tsPost == -1){
			$smarty->assign("tsAviso", [
				'titulo' => 'Anti Flood', 
				'mensaje' => "No puedes realizar tantas acciones en tan poco tiempo. Vuelve a intentarlo en unos instantes.", 
				'but' => 'Volver', 
				'link' => "javascript:history.go(-1)"
			]);
		} else {
			$smarty->assign("tsAviso", [
				'titulo' => 'Oops!', 
				'mensaje' => "Ha ocurrido un error intentalo más tarde.\n<b>Error</b>: $tsPost",
				'but' => 'Volver', 
				'link' => 'javascript:history.go(-1)'
			]);
		}
	}
}

Controller::render($tsAjax, $tsTitle, $tsPage);
