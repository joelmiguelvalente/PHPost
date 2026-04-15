<?php 

/**
 * @name moderacion.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "Moderacion de {$tsCore->settings['titulo']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::page('moderacion')->moderator();
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

	// ACTION
	$action = trim($_GET['action'] ?? '');
	// ACTION 2
	$act = trim($_GET['act'] ?? '');
	// CLASE POSTS
	require_once TS_CLASS . "/c.moderacion.php";
	$tsMod = new tsMod($tsCore, $tsUser);

	if($action === '') {
		$smarty->assign("tsPlantilla", "");
		$smarty->assign("tsMods", $tsMod->getMods());

   # DENUNCIAS
	} elseif(in_array($action, ['posts', 'users', 'mps', 'fotos'], true)) {
		$smarty->assign("tsPlantilla", "report_$action");
      // DATOS EXTRA
      require_once TS_EXTRAS . '/datos.php';
      // SEGUNDA ACCION
		if(empty($act)){
		 	$smarty->assign("tsReports", $tsMod->getDenuncias($action));
		} elseif($act === 'info') {
         $smarty->assign("tsDenuncia", $tsMod->getDenuncia($action));
		}
      $smarty->assign("tsDenuncias", $Denuncias[$action]);
	}
   // SUSPENSIONES
   elseif($action === 'banusers'){
		$smarty->assign("tsPlantilla", "ban_users");
      $smarty->assign("tsSuspendidos",$tsMod->getSuspendidos());
   }
	//PAPELERAS
	elseif($action === 'pospelera'){
		$smarty->assign("tsPlantilla", "papelera_posts");
      $smarty->assign("tsPospelera",$tsMod->getPospelera());
   }
	elseif($action === 'fopelera'){
		$smarty->assign("tsPlantilla", "papelera_fotos");
       $smarty->assign("tsFopelera",$tsMod->getFopelera());
   }
	// CONTENIDO DESAPROBADO
	elseif($action === 'comentarios'){
		$smarty->assign("tsPlantilla", "revision_comentarios");
      $smarty->assign("tsComentarios",$tsMod->getComentariosD());
   }
	elseif($action === 'revposts'){
		$smarty->assign("tsPlantilla", "revision_posts");
      $smarty->assign("tsPosts",$tsMod->getPostsD());
   }
	// BUSCADOR DE IP Y CONTENIDO
   elseif($action === 'buscador') {
		$smarty->assign("tsPlantilla", "buscador");
		if(isset($_POST['buscar'])) {
			$tsCore->redirectTo($tsCore->settings['url'].'/moderacion/buscador/'.$_POST['m'].'/'.$_POST['t'].'/'.$_POST['texto']);
		}	
		if($act === 'search') $smarty->assign("tsContenido", $tsMod->getContenido()); 
	}

	// ACCION?
	$smarty->assign("tsAction",$action);
	$smarty->assign("tsAct",$act);

}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
	if(isset($_GET['save'])) $smarty->assign("tsSave", $_GET['save']);
   require_once TS_ROOT . "/footer.php";
}
