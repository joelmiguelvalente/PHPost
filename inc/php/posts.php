<?php

/**
 * @name posts.php
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

$tsPage  = "posts";
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
	require_once dirname(__DIR__, 1) . "/class/c.posts.php";

	// Posts Class
	$tsPosts = new tsPosts($tsCore, $tsUser);
	// Afiliado Class
	$tsAfiliado = new tsAfiliado($tsCore, $tsUser);
		
	// Referido?
	if(isset($_GET['ref']) && (int)$_GET['ref']) {
		$tsAfiliado->urlInRef();
	}
	
	// Category
	$category = trim((string)$_GET['cat'] ?? '');
	
	// Post anterior/siguiente
	if(isset($_GET['action']) && in_array($_GET['action'], ['next', 'prev', 'random'], true)) {
		$tsPosts->navigatePost();
	}

/*
 * -------------------------------------------------------------------
 *  Tareas principales
 * -------------------------------------------------------------------
 */

	// DATOS DEL POST
	$tsPost = $tsPosts->getPost();
	//
	if($tsPosts->postId !== 0) {
		// TITULO NUEVO
		$tsTitle = $tsPost['post_title'].' - '.$tsTitle;
		// ASIGNAMOS A LA PLANTILLA
		$smarty->assign("tsPost", $tsPost);
		// DATOS DEL AUTOR
		$smarty->assign("tsAutor", $tsPosts->getAutor((int)$tsPost['post_user']));						
		// DATOS DEL RANGO DEL PUTEADOR						
		$smarty->assign("tsPunteador", $tsPosts->getPunteador());
		// RELACIONADOS
		$tsRelated = $tsPosts->getPostsRelatedByTags($tsPost['post_tags']);
		$smarty->assign("tsRelated",$tsRelated);
		// COMENTARIOS
		/*$tsComments = $tsPosts->getComentarios($tsPost['post_id']);
		$tsComments = array('num' => $tsComments['num'], 'data' => $tsComments['data']);
		$smarty->assign("tsComments",$tsComments);*/
		// PAGINAS
		$total = $tsPost['post_comments'];
		$tsPages = $tsCore->getPages($total, $tsCore->settings['c_max_com']);
		$tsPages['post_id'] = $tsPost['post_id'];
		$tsPages['autor'] = $tsPost['post_user'];
		//
		$smarty->assign("tsPages",$tsPages);
	
	} else {
		//
		if($tsPost[0] == 'privado'){
			$tsTitle = $tsPost[1].' - '.$tsTitle;
			$tsPage = "registro";
		} else {
			$tsTitle = $tsTitle.' - '.$tsCore->settings['slogan'];
			//
			$tsPage = "post.aviso";
			$tsAjax = 0;
			$smarty->assign("tsAviso",$tsPost);
			//
			$title = str_replace("-",",",$tsCore->setSecure($_GET['title']));
			$title = explode(",",$title);
			// RELACIONADOS
			$tsRelated = $tsPosts->getRelated($title);
			$smarty->assign("tsRelated",$tsRelated);
		}
	}
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}