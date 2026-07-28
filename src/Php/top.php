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

$ctx = Controller::init('tops', 'everybody');

if($ctx->continue()) {

	$tsTops = Container::get(tsTops::class);
	//
	$fecha = (int)($_GET['fecha'] ?? 0);
	$cat = (int)($_GET['cat'] ?? 0);
	$action = (string)($_GET['action'] ?? 'posts');
	$smarty->assign("tsFecha",$fecha);
	$smarty->assign("tsCat",$cat);
	$smarty->assign("tsAction",$action);

	$boxes = match($action) {
		'posts' => (function() use ($smarty, $tsTops, $fecha, $cat) {
			$smarty->assign("tsTops", $tsTops->getTopPosts($fecha, $cat));
			return [
				'puntos' 	=> ['txt' => 'puntos', 'count' => 'post_puntos', 'icon' => 'puntos'],
				'favoritos' => ['txt' => 'favoritos', 'count' => 'post_favoritos', 'icon' => 'favoritos'],
				'comments'  => ['txt' => 'comentado', 'count' => 'post_comments', 'icon' => 'comentarios'],
				'seguidores'  => ['txt' => 'seguidores', 'count' => 'post_seguidores', 'icon' => 'follow']
			];
		})(),
		'usuarios' => (function() use ($smarty, $tsTops, $fecha, $cat) {
			$smarty->assign("tsTops", $tsTops->getTopUsers($fecha, $cat));
			return [
				'puntos' 	=> ['txt' => 'puntos', 'icon' => 'puntos'],
				'seguidores' => ['txt' => 'seguidores', 'icon' => 'follow'],
				'medallas'  => ['txt' => 'medallas', 'icon' => 'medallas']
			];
		})(),
	};
	$smarty->assign("tsBoxes", $boxes);
}

Controller::render($tsAjax, $tsTitle, $tsPage);
