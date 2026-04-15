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
   'favoritos' => ['nivel' => 2, 'template' => 'home', 'ajax' => true],
   'favoritos-agregar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'favoritos-borrar' => ['nivel' => 2, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.favoritos.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . "/c.favoritos.php";
$tsFavoritos = new tsFavoritos($tsCore, $tsUser);

// CODIGO
switch($action){
	case 'favoritos':
		$smarty->assign("tsFavoritos",$tsFavoritos->getFavoritos());
	break;
	case 'favoritos-agregar':
		echo $tsFavoritos->saveFavorito();
	break;
	case 'favoritos-borrar':
		echo $tsFavoritos->delFavorito();
	break;
}
