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

$tsPage  = "agregar";
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

//
if($tsContinue) {

	$action = trim($_GET['action'] ?? '');
	if($action === 'editar' || isset($_POST['titulo'])) {
		// CLASE
		require_once dirname(__DIR__, 1) . "/class/c.agregar.php";
		$tsAgregar = new tsAgregar($tsCore, $tsUser);
	}

	if(is_numeric($action)) {
		require_once dirname(__DIR__, 1) . "/class/c.borradores.php";
		$tsDrafts = new tsDrafts($tsCore, $tsUser);
		$tsBorrador = $tsDrafts->getDraft();
		$smarty->assign("tsDraft", $tsBorrador);
		//
	} elseif($action === 'editar') {
		// GUARDAR
		if(!empty($_POST['titulo'])) {
			$postSave = $tsAgregar->savePost();
			if($postSave) {
				$tsPost = (int)$_GET['pid'];
				$tsCat = (int)$_POST['categoria'];
				$tsCat = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT c.c_seo FROM p_categorias AS c WHERE c.cid = $tsCat LIMIT 1"));
				//
				$post_url = "{$tsCore->settings['url']}/posts/{$tsCat['c_seo']}/$tsPost/{$tsCore->setSEO($_POST['titulo'])}.html";
				// NOS VAMOS AL POST
				$tsCore->redirectTo($post_url);
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
		$smarty->assign("tsAction", $_GET['action']);
		$smarty->assign("tsPid", $_GET['pid']);
		
	} elseif(isset($_POST['titulo'])) {
		$tsPost = $tsAgregar->newPost();
		var_dump($tsPost);
		//
		$tsPage = 'aviso';
		$tsAjax = 0;
		if($tsPost > 0) {
			$tsCat = (int)$_POST['categoria'];
			$tsCat = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT c.c_seo FROM p_categorias AS c WHERE c.cid = $tsCat LIMIT 1"));
			//
			$approved = !$tsUser->is_admod && ($tsUser->permisos['gorpap'] || (int)$tsCore->settings['c_desapprove_post'] === 1) ? 'Deber&aacute; esperar su aprobaci&oacute;n' : '';
			$smarty->assign("tsAviso", [
				'titulo' => 'Bien!', 
				'mensaje' => "El post <b>{$_POST['titulo']}</b> fue agregado. $approved", 
				'but' => 'Acceder al post', 
				'link' => "{$tsCore->settings['url']}/posts/{$tsCat['c_seo']}/$tsPost/{$tsCore->setSEO($_POST['titulo'])}.html"
			]);
		} elseif($tsPost == -1){
			$smarty->assign("tsAviso",array('titulo' => 'Anti Flood', 'mensaje' => "No puedes realizar tantas acciones en tan poco tiempo. Vuelve a intentarlo en unos instantes.", 'but' => 'Volver', 'link' => "javascript:history.go(-1)"));
		} else {
			$smarty->assign("tsAviso",array('titulo' => 'Oops!', 'mensaje' => "Ha ocurrido un error intentalo m&aacute;s tarde.<br><b>Error</b>: ".$tsPost, 'but' => 'Volver', 'link' => 'javascript:history.go(-1)'));
		}
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}