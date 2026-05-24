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

$ctx = Controller::page('posts')->everybody();
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
	require_once TS_CLASS . "/c.posts.php";
	require_once TS_CLASS . "/c.comentarios.php";

	$tsAfiliado = new tsAfiliado($tsCore, $tsUser);
	$tsPosts = new tsPosts($tsCore, $tsUser);
	$tsComentarios = new tsComentarios($tsCore, $tsUser);
	
	// Post anterior/siguiente
	if(isset($_GET['action']) && in_array($_GET['action'], ['next', 'prev', 'random'], true)) {
		$tsPosts->navigatePost();
	}
		
	// Referido?
	if(isset($_GET['ref']) && (int)$_GET['ref']) {
		$tsAfiliado->urlInRef();
	}

	// Category
	$category = trim($_GET['cat'] ?? '');

/*
 * -------------------------------------------------------------------
 *  Tareas principales
 * -------------------------------------------------------------------
 */

	// DATOS DEL POST
	$tsPost = $tsPosts->getPost();
	// Si el post no existe
	if($tsPosts->postId === 0) {
		$tsPage = "post.aviso";
		$smarty->assign("tsAviso", $tsPost);
		//
		$title = explode("-", trim($_GET['title'] ?? ''));
		// RELACIONADOS
		$smarty->assign("tsRelated", $tsPosts->getPostsRelatedByTags($title));
	// Si existe, pero es privado
	} elseif ($tsPost['post_private'] === 1 && !$tsUser->is_member) {
		$tsTitle = $tsPost['post_title'].' - '.$tsTitle;
		$tsPage = "privado";
		$smarty->assign("tsType", 'post');
	// Existe y es público
	} else {
		// TITULO NUEVO
		$tsTitle = $tsPost['post_title'].' - '.$tsTitle;
		// ASIGNAMOS A LA PLANTILLA
		$smarty->assign("tsPost", $tsPost);
		// DATOS DEL AUTOR
		$smarty->assign("tsAutor", $tsPosts->getAutor((int)$tsPost['post_user']));
		$smarty->assign("PrevPost", $tsPosts->getNearbyPostTitle('prev'));
		$smarty->assign("NextPost", $tsPosts->getNearbyPostTitle('next'));
		// DATOS DEL RANGO DEL PUTEADOR						
		$smarty->assign("tsPunteador", $tsPosts->getPunteador());
		// RELACIONADOS
		$smarty->assign("tsRelated", $tsPosts->getPostsRelatedByTags($tsPost['post_tags']));
		// COMENTARIOS
		$smarty->assign("tsComments", $tsComentarios->getLastComentarios());
		// PAGINAS
		$total = $tsPost['post_comments'];
		$tsPages = (new Paginator)->getPages((int)$total, (int)$tsCore->settings['c_max_com']);
		$tsPages['post_id'] = $tsPost['post_id'];
		$tsPages['autor'] = $tsPost['post_user'];
		//
		$smarty->assign("tsPages", $tsPages);
	
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
