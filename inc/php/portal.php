<?php 

/**
 * @name portal.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::page('portal')->everybody();
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

    // PORTAL
    require_once TS_CLASS . "/c.portal.php";
    require_once TS_CLASS . "/c.afiliado.php";
    $tsPortal = new tsPortal();
    // AFILIADOS
    $tsAfiliado = new tsAfiliado();
    // NOS HAN REFERIDO?
    if(!empty($_GET['ref'])) $tsAfiliado->urlIn();

/**********************************\

*	(INSTRUCCIONES DE CODIGO)		*

\*********************************/

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

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}