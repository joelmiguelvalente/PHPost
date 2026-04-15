<?php

/**
 * @name src/Api/api.cuenta.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'denuncia-post' => ['nivel' => 2, 'template' => 'form', 'ajax' => true],
   'denuncia-foto' => ['nivel' => 2, 'template' => 'form', 'ajax' => true],
   'denuncia-mensaje' => ['nivel' => 2, 'template' => 'form', 'ajax' => true],
   'denuncia-usuario' => ['nivel' => 2, 'template' => 'form', 'ajax' => true]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.denuncia.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . "/c.denuncias.php";
$tsDenuncias = new tsDenuncias($tsCore, $tsUser);

// VARS
$obj_id = (int)($_POST['obj_id'] ?? 0);
$user = trim($_POST['obj_user'] ?? '');
$razon = trim($_POST['razon'] ?? '');
$tsData = [
	'obj_id' => $obj_id,
	'obj_title' => trim($_POST['obj_title'] ?? ''),
	'obj_user' => $user, 
];
$item = explode('-', $action);

if($razon) {
	echo $tsDenuncias->setDenuncia($obj_id, $item[1]);
	$tsAjax = false;
}
// CODIGO
switch($action){
	case 'denuncia-post':  
	case 'denuncia-foto':   
		if(!$razon) {
			$smarty->assign("tsData", $tsData);
		}
	break; 
	case 'denuncia-mensaje':
	case 'denuncia-usuario':
		if($action === 'denuncia-usuario') {
			$smarty->assign("tsData", ['nick' => $user]);
		}
	break;
}
if(in_array($action, ['denuncia-post', 'denuncia-foto', 'denuncia-usuario'])) {
	require_once TS_EXTRAS . "/datos.php";
	$data = [
		'post' => 'posts',
		'usuario' => 'users',
		'foto' => 'fotos'
	];
	$smarty->assign("tsDenuncias", $Denuncias[$data[$item[1]]]);
}
// ACCION
$smarty->assign("tsAction", $action);
