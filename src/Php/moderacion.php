<?php

declare(strict_types=1);

/**
 * @package    Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "Moderacion de {$tsCore->settings['titulo']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::init('moderacion', 'moderator');

if($ctx->continue()) {

	// ACTION
	$action = trim($_GET['action'] ?? '');
	// ACTION 2
	$act = trim($_GET['act'] ?? '');
	// CLASE POSTS
	$tsModeracion = Container::get(tsModeracion::class);

	if($action === '') {
		$smarty->assign("tsPlantilla", "");
		$smarty->assign("tsMods", $tsModeracion->getMods());

   	# DENUNCIAS
	} elseif(in_array($action, ['posts', 'users', 'mps', 'fotos'], true)) {
		$smarty->assign("tsPlantilla", "report_$action");
      	// DATOS EXTRA
      	require_once TS_EXTRAS . '/datos.php';
      	// SEGUNDA ACCION
		if(empty($act)){
		 	$smarty->assign("tsReports", $tsModeracion->getDenuncias($action));
		} elseif($act === 'info') {
         	$smarty->assign("tsDenuncia", $tsModeracion->getDenuncia($action));
		}
      	$smarty->assign("tsDenuncias", $Denuncias[$action]);
   	// SUSPENSIONES
	} elseif($action === 'banusers') {
		$smarty->assign("tsPlantilla", "ban_users");
      	$smarty->assign("tsSuspendidos",$tsModeracion->getSuspendidos());
	//PAPELERAS
   	} elseif($action === 'pospelera'){
		$smarty->assign("tsPlantilla", "papelera_posts");
      $smarty->assign("tsPospelera",$tsModeracion->getPospelera());
   	} elseif($action === 'fopelera'){
		$smarty->assign("tsPlantilla", "papelera_fotos");
       $smarty->assign("tsFopelera",$tsModeracion->getFopelera());
    // CONTENIDO DESAPROBADO
   	} elseif($action === 'comentarios'){
		$smarty->assign("tsPlantilla", "revision_comentarios");
      	$smarty->assign("tsComentarios",$tsModeracion->getComentariosD());
   	} elseif($action === 'revposts'){
		$smarty->assign("tsPlantilla", "revision_posts");
		$smarty->assign("tsPosts",$tsModeracion->getPostsD());
	// BUSCADOR DE IP Y CONTENIDO
   	} elseif($action === 'buscador') {
		$smarty->assign("tsPlantilla", "buscador");
		if(isset($_POST['buscar'])) {
			$tsCore->redirectTo($tsCore->settings['url'].'/moderacion/buscador/'.$_POST['m'].'/'.$_POST['t'].'/'.$_POST['texto']);
		}	
		if($act === 'search') $smarty->assign("tsContenido", $tsModeracion->getContenido());
	}

	// ACCION?
	$smarty->assign("tsAction",$action);
	$smarty->assign("tsAct",$act);

}

Controller::render($tsAjax, $tsTitle, $tsPage);
