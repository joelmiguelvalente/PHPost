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

$ctx = Controller::init('home', 'everybody');

if($ctx->continue()) {

	// Afiliado Class
	$tsAfiliado = Container::get(tsAfiliado::class);
		
	// Referido?
	if(!empty($_GET['ref'])) {
		$tsAfiliado->urlInRef();
	}
	
	// Category
	$category = trim($_GET['cat'] ?? '');
	
	// Post anterior/siguiente
	if(isset($_GET['action']) && in_array($_GET['action'], ['next', 'prev', 'random'])) {
		Container::get(tsPosts::class)->navigatePost();
	}

	// CLASE TOPS
	$tsHome = Container::get(tsHome::class);
	$tsComentarios = Container::get(tsComentarios::class);
	$tsTops = Container::get(tsTops::class);
	$tsFotos = Container::get(tsFotos::class);
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

	if ($tsUser->is_admod === 1) {
		require_once TS_LOGGER . '/LogWidget.php';
		$smarty->assign('logWidget', LogWidget::getData());
	}
}

Controller::render($tsAjax, $tsTitle, $tsPage);
