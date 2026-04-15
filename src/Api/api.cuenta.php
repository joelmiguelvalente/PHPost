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
   'cuenta-desactivar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'cuenta-guardar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'cuenta-cambiar-tema' => ['nivel' => 2, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.cuenta.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . "/c.cuenta.php";
$tsCuenta = new tsCuenta($tsCore, $tsUser);

// CODIGO
switch($action) {
	case 'cuenta-desactivar':
		echo $tsCuenta->desactivarCuenta();
	break;
	case 'cuenta-guardar':
		echo $tsCuenta->savePerfil();
	break;
	case 'cuenta-cambiar-tema':
		echo $tsCuenta->cambiarTema();
	break;
}
