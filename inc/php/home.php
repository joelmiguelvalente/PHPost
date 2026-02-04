<?php

/**
 * @name home.php
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

$tsPage  = "home";
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

	// Afiliados
	require_once dirname(__DIR__, 1) . "/class/c.afiliado.php";
	require_once dirname(__DIR__, 1) . "/class/c.comentarios.php";
	require_once dirname(__DIR__, 1) . "/class/c.fotos.php";
	require_once dirname(__DIR__, 1) . "/class/c.home.php";
	require_once dirname(__DIR__, 1) . "/class/c.posts.php";
	require_once dirname(__DIR__, 1) . "/class/c.tops.php";

	// Afiliado Class
	$tsAfiliado = new tsAfiliado($tsCore, $tsUser);
		
	// Referido?
	if(!empty($_GET['ref'])) {
		$tsAfiliado->urlInRef();
	}
	
	// Category
	$category = trim($_GET['cat'] ?? '');
	
	// Post anterior/siguiente
	if(isset($_GET['action']) && in_array($_GET['action'], ['next', 'prev', 'random'])) {
		(new tsPosts($tsCore, $tsUser))->navigatePost();
	}

	// CLASE TOPS
	$tsHome = new tsHome($tsCore, $tsUser);
	$tsComentarios = new tsComentarios($tsCore, $tsUser);
	$tsTops = new tsTops($tsCore);
	$tsFotos = new tsFotos();
	// PAGINA
	$tsPage = "home";
	$tsTitle = $tsTitle.' - '.$tsCore->settings['slogan']; 	// TITULO DE LA PAGINA ACTUAL
	
	// ULTIMOS POSTS
	$tsLastPosts = $tsHome->getLastPosts($category);
	$smarty->assign("tsPosts", $tsLastPosts['data']);
	$smarty->assign("tsPages", $tsLastPosts['pages']);
	// ULTIMOS POSTS FIJOS
	if($tsLastPosts['pages']['current'] === 1) {
	   $tsLastStickys = $tsHome->getLastStickys($category);
	   $smarty->assign("tsPostsStickys", $tsLastStickys['data']);
	}
	// CAT
	$smarty->assign("tsCat", $category);
	$smarty->assign("tsStats", $tsTops->getStats());
	// ULTIMOS COMENTARIOS
	$smarty->assign("tsComments", $tsComentarios->getLastComentarios());
	// TOP POSTS
	$smarty->assign("tsTopPosts", $tsTops->getHomeTops('posts'));
	// TOP USERS
	$smarty->assign("tsTopUsers", $tsTops->getHomeTops('users'));
	// TITULO
	if(!empty($category)) {
		$categorie = $tsHome->getDataCategorie();
		$tsTitle = $tsCore->settings['titulo'].' - '.$categorie['c_nombre'];
		$smarty->assign("tsCatData", $categorie);
	}
	// IMAGENES
	$tsImages = $tsFotos->getLastFotos();
	$smarty->assign("tsImages",$tsImages);
	$smarty->assign("tsImTotal", count($tsImages ?? []));
	
	// AFILIADOS
	$smarty->assign("tsAfiliados", $tsAfiliado->getAfiliados());
	// DO <= PARA EL MENU
	$smarty->assign("tsDo", $_GET['do'] ?? '');

	

}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}