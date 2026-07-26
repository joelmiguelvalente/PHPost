<?php

declare(strict_types=1);

/**
 * @package    Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::init('portal', 'everybody');

if($ctx->continue()) {

    // Instanciamos
    $tsPortal = Containter::get(tsPortal::class);
    $tsAfiliado = Containter::get(tsAfiliado::class);
    // NOS HAN REFERIDO?
    if(!empty($_GET['ref'])) $tsAfiliado->urlIn();

    $smarty->assign("tsMuro",$tsPortal->getNews());
    $smarty->assign("tsInfo",array('uid' => $tsUser->uid));
    $smarty->assign("tsType", "news");
    //
    $smarty->assign("tsCategories",$tsPortal->composeCategories());
    //$tsPosts = $tsPortal->getMyPosts();
    //$smarty->assign("tsPosts",$tsPosts['data']);
    //$smarty->assign("tsPages",$tsPosts['pages']);
    //
    $smarty->assign("tsLastPostsVisited",$tsPortal->getLastPosts());
    $smarty->assign("tsFavorites",$tsPortal->getFavorites());
    // FOTOS
    $tsImages = $tsPortal->getFotos();
	$smarty->assign("tsImages",$tsImages);
    $smarty->assign("tsImTotal",count($tsImages));
    // STATS
    $smarty->assign("tsStats",$tsPortal->getStats());
    // AFILIADOS
    $smarty->assign("tsAfiliados",$tsAfiliado->getAfiliados());

}

Controller::render($tsAjax, $tsTitle, $tsPage);
