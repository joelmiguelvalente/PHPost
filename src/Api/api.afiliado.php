<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
   'afiliado-nuevo' => ['nivel' => 0, 'template' => '', 'ajax' => false],
   'afiliado-borrar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
   'afiliado-setaction' => ['nivel' => 0, 'template' => '', 'ajax' => false],
   'afiliado-url' => ['nivel' => 0, 'template' => '', 'ajax' => false],
   'afiliado-detalles' => ['nivel' => 0, 'template' => 'detalles', 'ajax' => true],
   'afiliado-editar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
   'afiliado-form' => ['nivel' => 0, 'template' => 'form', 'ajax' => true],
   'afiliado-setactive' => ['nivel' => 0, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.afiliado.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
$tsAfiliado = Container::get(tsAfiliado::class);

match($action) {
	'afiliado-form' => null,
	'afiliado-nuevo' => print $tsAfiliado->newAfiliado(),
	'afiliado-borrar' => print $tsAfiliado->DeleteAfiliado(),
	'afiliado-setactive' => print $tsAfiliado->activeAfiliado(),
	'afiliado-url' => $tsAfiliado->urlOut(),
	'afiliado-detalles' => $smarty->assign("tsAfiliado", $tsAfiliado->getAfiliado()),
	default => die('0: Este archivo no existe.'),
};
