<?php

/**
 * @name api.bloqueos.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

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
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

$do = trim($_GET['do'] ?? '');
// CLASE
require_once TS_CLASS . "/c.comentarios.php";
$tsComentarios = new tsComentarios($tsCore, $tsUser);
if($do === 'fotos') {
	require_once TS_CLASS . "/c.fotos.php";
	$tsFotos = new tsFotos($tsCore, $tsUser);
}
// CODIGO
switch($action){
	case 'comentario-preview':
		$comentario = $tsCore->setSecure($_POST['comentario']);
		$comentario = substr($comentario,0,1500);
		// COMENTARIO VACIO?
		$tsText = preg_replace('# +#',"",$comentario);
		if(empty($tsText)) die('0: El campo <b>Comentario</b> es requerido para esta operaci&oacute;n');
		//
		$auser = $_POST['auser'];
		$preview = array(0,$tsCore->parseBBCode($comentario),'',time(),$auser, $comentario, $_SERVER['REMOTE_ADDR']);
		$smarty->assign("tsComment",$preview);
		$smarty->assign("tsType",$_GET['type']);
	break;
	case 'comentario-agregar':
		//<--
		if(empty($do)){
			$tsComment = $tsComentarios->newComentario();
			$smarty->assign("tsType",'new');
			//
			if(is_array($tsComment)) $smarty->assign("tsComment",$tsComment);
			else die($tsComment);
		} elseif($do == 'fotos'){
		   //
		   $tsComment = $tsFotos->newComentario();
			if(is_array($tsComment)) $smarty->assign("tsComment",$tsComment);
			else die($tsComment);
			// NUEVA PLANTILLA
			$tsPage = 'p.comentario.fotos';
		}
		//-->
	break;
	case 'comentario-editar':
		//<--
			echo $tsComentarios->editComentario();
		//-->
	break;
	case 'comentario-borrar':
		//<--
		if(empty($do)){
			echo $tsComentarios->delComentario();
		} elseif($do == 'fotos'){
			//
			echo $tsFotos->delComentario();
		}
		//-->
	break;
	case 'comentario-ocultar':
		//<--
			echo $tsComentarios->OcultarComentario();
		//-->
	break;
	case 'comentario-votar':
		//<--
		if(empty($do)){
			echo $tsComentarios->votarComentario();
		} elseif($do == 'fotos'){
			//
			echo $tsFotos->votarFoto();
		}
		//-->
	break;
	case 'comentario-ajax':
		//<--
		// COMENTARIOS
		$tsPost = (int)($_POST['postid'] ?? 0);
		$tsAutor = $tsCore->setSecure($_POST['autor']);
		$tsComments = $tsComentarios->getComentarios($tsPost);
		
		$tsComments = [
			'num' => $tsComments['num'], 
			'data' => $tsComments['data'], 
			'block' => $tsComments['block'], 
			'autor' => $tsAutor
		];
		$smarty->assign("tsComments",$tsComments);	
		$smarty->assign("tsPost", [
			'postid' => $tsPost, 
			'autor' => $tsAutor
		]);
		//-->
	break;
	case 'comentario-pages':
		//
		$total = (int)($_POST['total'] ?? 0);
		$tsPages = (new Paginator)->getPages((int)$total, (int)$tsCore->settings['c_max_com']);
		$tsPages['post_id'] = $tsCore->setSecure($_POST['postid']);
		$tsPages['autor'] = $tsCore->setSecure($_POST['autor']);
		//
		$smarty->assign("tsPages",$tsPages);
		//-->
	break;
}