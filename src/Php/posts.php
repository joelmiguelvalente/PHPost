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

$ctx = Controller::init('posts', 'everybody');

if($ctx->continue()) {

	$tsAfiliado = Container::get(tsAfiliado::class);
	$tsPosts = Container::get(tsPosts::class);
	$tsComentarios = Container::get(tsComentarios::class);
	
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
		$tsPages = Container::get(Paginator::class)->getPages((int)$total, (int)$tsCore->settings['c_max_com']);
		$tsPages['post_id'] = $tsPost['post_id'];
		$tsPages['autor'] = $tsPost['post_user'];
		//
		$smarty->assign("tsPages", $tsPages);
	
	}
}

Controller::render($tsAjax, $tsTitle, $tsPage);
