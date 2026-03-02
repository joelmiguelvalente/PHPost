<?php

/**
 * @name api.posts.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'posts-genbus'				 => ['nivel' => 2, 'template' => 'genbus', 'ajax' => true],
   'posts-preview' 			 => ['nivel' => 2, 'template' => 'preview', 'ajax' => true],
   'posts-borrar'				 => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'posts-admin-borrar'		 => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'posts-votar'				 => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'posts-last-comentarios' => ['nivel' => 0, 'template' => 'last-comentarios', 'ajax' => true]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.posts.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

if(in_array($action, ['posts-genbus', 'posts-preview'])) {
	require_once TS_CLASS . "/c.agregar.php";
	$tsAgregar = new tsAgregar($tsCore, $tsUser);
}

require_once TS_CLASS . "/c.posts.php";
$tsPosts = new tsPosts($tsCore, $tsUser);

// CODIGO
switch($action){
	case 'posts-genbus':
		$query = $tsCore->setSecure(trim($_GET['query'] ?? $_POST['query'] ?? ''));
		$do = trim($_GET['do'] ?? '');
		if($do === 'search') $smarty->assign("tsPosts", $tsAgregar->simiPosts($query));
		else $smarty->assign("tsTags", $tsAgregar->genTags($query));
		$smarty->assign("tsDo", $do);
	break;
	case 'posts-preview':
		$smarty->assign("tsPreview", $tsAgregar->getPreview());
	break;
	case 'posts-borrar':
		echo $tsPosts->deletePost();
	break;
	case 'posts-admin-borrar':
		echo $tsPosts->deleteAdminPost();
	break;
	case 'posts-votar':
		echo $tsPosts->votarPost();
	break;
	case 'posts-last-comentarios':
		require_once TS_CLASS . "/c.comentarios.php";
		$tsComentarios = new tsComentarios($tsCore, $tsUser);
		$smarty->assign("tsComments", $tsComentarios->getLastComentarios());
	break;
}