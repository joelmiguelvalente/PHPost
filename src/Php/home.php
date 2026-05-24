<?php

declare(strict_types=1);

/**
 * @package    PHPost/Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::page('home')->everybody();
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

	// Afiliados
	require_once TS_CLASS . "/c.afiliado.php";
	require_once TS_CLASS . "/c.comentarios.php";
	require_once TS_CLASS . "/c.fotos.php";
	require_once TS_CLASS . "/c.home.php";
	require_once TS_CLASS . "/c.posts.php";
	require_once TS_CLASS . "/c.tops.php";

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
	$tsFotos = new tsFotos($tsCore, $tsUser);
	// PAGINA
	$tsPage = "home";
	
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

	require_once TS_LOGGER . '/LogWidget.php';
	if ($tsUser->is_admod === 1) {
	   $smarty->assign('logWidget', LogWidget::getData());
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
