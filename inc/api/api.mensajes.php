<?php

/**
 * @name ajax.mensajes.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'mensajes-validar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'mensajes-enviar' => ['nivel' => 2, 'template' => '', 'ajax' => false],
   'mensajes-respuesta' => ['nivel' => 2, 'template' => 'resp', 'ajax' => true],
   'mensajes-lista' => ['nivel' => 2, 'template' => 'lista', 'ajax' => true],
   'mensajes-editar' => ['nivel' => 2, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.mensajes.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CODIGO
switch($action){
	case 'mensajes-validar':
		echo $tsMP->getValid();
	break;
	case 'mensajes-enviar':
		echo $tsMP->newMensaje();
	break;
	case 'mensajes-respuesta':
		$smarty->assign("mp",$tsMP->newRespuesta());
	break;
	case 'mensajes-lista':
		$smarty->assign("tsMensajes",$tsMP->getMensajes(1, false, 'monitor'));
	break;
	case 'mensajes-editar':
		echo $tsMP->editMensajes();
	break;
}

$_GET['ts'] = true;