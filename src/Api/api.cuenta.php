<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

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
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
$tsCuenta = Container::get(tsCuenta::class);

// CODIGO
match($action) {
	'cuenta-desactivar' => print $tsCuenta->desactivarCuenta(),
	'cuenta-guardar' => print $tsCuenta->savePerfil(),
	'cuenta-cambiar-tema' => print $tsCuenta->cambiarTema(),
};
