<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

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
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

if(in_array($action, ['posts-genbus', 'posts-preview'])) {
	$tsAgregar = Container::get(tsAgregar::class);
}

$tsPosts = Container::get(tsPosts::class);

// CODIGO
match($action) {
	'posts-genbus' => (static function() use ($tsAgregar, $smarty) {
		$query = Html::escape(trim($_GET['query'] ?? $_POST['query'] ?? ''));
		$do = trim($_GET['do'] ?? '');
		if ($do === 'search') $smarty->assign("tsPosts", $tsAgregar->simiPosts($query));
		else $smarty->assign("tsTags", $tsAgregar->genTags($query));
		$smarty->assign("tsDo", $do);
	})(),
	'posts-preview' => $smarty->assign("tsPreview", $tsAgregar->getPreview()),
	'posts-borrar' => print $tsPosts->deletePost(),
	'posts-admin-borrar' => print $tsPosts->deleteAdminPost(),
	'posts-votar' => print $tsPosts->votarPost(),
	'posts-last-comentarios' => (static function() use ($tsCore, $tsUser, $smarty) {
		$tsComentarios = Container::get(tsComentarios::class);
		$smarty->assign("tsComments", $tsComentarios->getLastComentarios());
	})(),
};
