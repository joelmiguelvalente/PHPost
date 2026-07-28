<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
   'comentario-preview' => ['nivel' => 2, 'template' => 'preview', 'ajax' => true],
   'comentario-agregar' => ['nivel' => 2, 'template' => 'preview', 'ajax' => true],
   'comentario-editar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'comentario-borrar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'comentario-ocultar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'comentario-votar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'comentario-ajax' => ['nivel' => 0, 'template' => 'ajax', 'ajax' => true],
   'comentario-pages' => ['nivel' => 0, 'template' => 'pages', 'ajax' => true],
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.comentario.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

$do = trim($_GET['do'] ?? '');
// CLASE
$tsComentarios = Container::get(tsComentarios::class);
if($do === 'fotos') {
	$tsFotos = Container::get(tsFotos::class);
}
// CODIGO
match($action) {
	'comentario-preview' => (static function() use ($tsCore, $smarty) {
		$comentario = Html::escape($_POST['comentario']);
		$comentario = substr($comentario, 0, 1500);
		$tsText = preg_replace('# +#', "", $comentario);
		if (empty($tsText)) die('0: El campo <b>Comentario</b> es requerido para esta operación');
		$auser = $_POST['auser'];
		$preview = [0, $tsCore->parseBBCode($comentario), '', time(), $auser, $comentario, $_SERVER['REMOTE_ADDR']];
		$smarty->assign("tsComment", $preview);
		$smarty->assign("tsType", $_GET['type']);
	})(),
	'comentario-agregar' => (static function() use ($do, $tsComentarios, $tsFotos, $smarty) {
		if (empty($do)) {
			$tsComment = $tsComentarios->newComentario();
			$smarty->assign("tsType", 'new');
			if (is_array($tsComment)) $smarty->assign("tsComment", $tsComment);
			else die($tsComment);
		} elseif ($do === 'fotos') {
			$tsComment = $tsFotos->newComentario();
			if (is_array($tsComment)) $smarty->assign("tsComment", $tsComment);
			else die($tsComment);
			$tsPage = 'p.comentario.fotos';
		}
	})(),
	'comentario-editar' => print $tsComentarios->editComentario(),
	'comentario-borrar' => (static function() use ($do, $tsComentarios, $tsFotos) {
		if (empty($do)) {
			echo $tsComentarios->delComentario();
		} elseif ($do == 'fotos') {
			echo $tsFotos->delComentario();
		}
	})(),
	'comentario-ocultar' => print $tsComentarios->OcultarComentario(),
	'comentario-votar' => (static function() use ($do, $tsComentarios, $tsFotos) {
		if (empty($do)) {
			echo $tsComentarios->votarComentario();
		} elseif ($do === 'fotos') {
			echo $tsFotos->votarFoto();
		}
	})(),
	'comentario-ajax' => (static function() use ($tsComentarios, $smarty) {
		$tsPost = (int)($_POST['postid'] ?? 0);
		$tsAutor = Html::escape($_POST['autor']);
		$tsComments = $tsComentarios->getComentarios($tsPost);
		$tsComments = [
			'num' => $tsComments['num'],
			'data' => $tsComments['data'],
			'block' => $tsComments['block'],
			'autor' => $tsAutor,
		];
		$smarty->assign("tsComments", $tsComments);
		$smarty->assign("tsPost", [
			'postid' => $tsPost,
			'autor' => $tsAutor,
		]);
	})(),
	'comentario-pages' => (static function() use ($tsCore, $smarty) {
		$tsPages = Container::get(Paginator::class)->getPages(
			(int)($_POST['total'] ?? 0),
			(int)$tsCore->settings['c_max_com']
		);
		$tsPages['post_id'] = (int)($_POST['postid'] ?? 0);
		$tsPages['autor'] = (int)($_POST['autor'] ?? 0);
		$smarty->assign("tsPages", $tsPages);
	})(),
};
