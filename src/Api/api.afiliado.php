<?php

declare(strict_types=1);

/**
 * @package    src\Api
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

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
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . "/c.afiliado.php";
$tsAfiliado = new tsAfiliado($tsCore, $tsUser);

switch($action) {
	case 'afiliado-form':
		// Para que no corte la ejecución
	break;
	case 'afiliado-nuevo':
		echo $tsAfiliado->newAfiliado();
	break;
	case 'afiliado-borrar':
		echo $tsAfiliado->DeleteAfiliado();
	break;
	case 'afiliado-setactive':
		echo $tsAfiliado->activeAfiliado();
	break;
	case 'afiliado-url':
		$tsAfiliado->urlOut();
	break;
	case 'afiliado-detalles':
		$smarty->assign("tsAfiliado", $tsAfiliado->getAfiliado());
	break;
	default:
		die('0: Este archivo no existe.');
	break;
}
