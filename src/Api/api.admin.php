<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
	'admin-foto-borrar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-foto-setOpenClosed' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-foto-setShowHide' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-medalla-asignar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-medalla-asignar-form' => ['nivel' => 4, 'template' => 'asignar-form', 'ajax' => true],
	'admin-medalla-borrar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-medallas-borrar-asignacion' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-nicks-change' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-noticias-setInActive' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-ordenar-categorias' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-sesiones-borrar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-users-sessions' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'admin-users-setInActivo' => ['nivel' => 4, 'template' => '', 'ajax' => false],
   'admin-badwords-delete' => ['nivel' => 4, 'template' => '', 'ajax' => false],
   'admin-blacklist-delete' => ['nivel' => 4, 'template' => '', 'ajax' => false],
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.admin.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);

if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASES
require_once TS_CLASS . "/c.admin.php";
require_once TS_CLASS . "/c.medals.php";

$tsAdmin = new tsAdmin($tsCore, $tsUser);
$tsMedal = new tsMedal($tsCore, $tsUser);
// CODIGO
switch($action) {
	case 'admin-medalla-asignar-form':
		# Simulando data
	break;
	case 'admin-medalla-borrar':
      echo $tsMedal->DelMedalla();
	break;
	case 'admin-medalla-asignar':
      echo $tsMedal->AsignarMedalla();
	break;
	case 'admin-medallas-borrar-asignacion':
      echo $tsMedal->delAssign();
	break;
	case 'admin-foto-borrar':
      echo $tsAdmin->DelFoto();
	break;
	case 'admin-foto-setOpenClosed':
      echo $tsAdmin->setOpenClosedFoto();
	break;
	case 'admin-foto-setShowHide':
      echo $tsAdmin->setShowHideFoto();
	break;
	case 'admin-users-InActivo':
      echo $tsAdmin->setUserInActivo();
	break;
	case 'admin-users-sessions':
      echo $tsAdmin->delSession();
	break;
	case 'admin-noticias-setInActive':
   	require_once TS_CLASS . "/c.noticias.php";
		$tsNoticias = new tsNoticias($tsCore, $tsUser);
      echo $tsNoticias->setNoticiaInActive();
	break;
	case 'admin-sesiones-borrar':
      echo $tsAdmin->delSession();
	break;
	case 'admin-nicks-change':
      echo $tsAdmin->ChangeNick_o_no();
	break;
   case 'admin-blacklist-delete':
   	require_once TS_CLASS . "/c.bloqueos.php";
   	$tsBloqueos = new tsBloqueos($tsCore, $tsUser);
      echo $tsBloqueos->deleteBlock();
	break;
   case 'admin-badwords-delete':
   	require_once TS_CLASS . "/c.censura.php";
   	$tsCensura = new tsCensura($tsCore, $tsUser);
      echo $tsCensura->deleteBadWord();
	break;
	case 'admin-ordenar-categorias':
	   echo $tsAdmin->saveOrden();
	break;
   default:
      echo '0: Este archivo no existe.';
   break;
}
