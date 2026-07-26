<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
   'perfil-wall'       => ['nivel' => 0, 'template' => 'wall', 'ajax' => true],
   'perfil-actividad'  => ['nivel' => 0, 'template' => 'actividad', 'ajax' => true],
   'perfil-info' 		  => ['nivel' => 0, 'template' => 'info', 'ajax' => true],
   'perfil-posts' 	  => ['nivel' => 0, 'template' => 'posts', 'ajax' => true],
   'perfil-seguidores' => ['nivel' => 0, 'template' => 'follows', 'ajax' => true],
   'perfil-siguiendo'  => ['nivel' => 0, 'template' => 'follows', 'ajax' => true],
   'perfil-medallas'   => ['nivel' => 0, 'template' => 'medallas', 'ajax' => true]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.perfil.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASS
require_once TS_CLASS . "/c.cuenta.php";
$tsCuenta = new tsCuenta($tsCore, $tsUser);

// USER ID
$user_id = (int)$_POST['pid'];
if(empty($user_id))  {
	echo '0: El campo <b>user_id</b> es obligatorio.';
}

$username = $tsUser->getUserName($user_id);
$smarty->assign("tsUsername", $username);
// CODIGO
switch($action){
	case 'perfil-wall':
		require_once TS_CLASS . "/c.muro.php";
		$tsMuro = new tsMuro($tsCore, $tsUser);
		// GENERAL
		$tsGeneral = $tsCuenta->loadGeneral($user_id);
		$smarty->assign("tsGeneral",$tsGeneral);
		//
		$privacidad = $tsMuro->getPrivacity(
			$user_id, 
			$username, 
			(int)$tsCuenta->isFollowed($user_id, true), 
			(int)$tsCuenta->isFollowed($user_id, false)
		);
		if($privacidad['muro']['status']) {
			$smarty->assign("tsMuro", $tsMuro->getWall($user_id));
			// INFO
			$smarty->assign("tsInfo", ['uid' => $user_id, 'nick' => $username]);   
		}
		$smarty->assign("tsPrivacidad", $privacidad);
	break;
	case 'perfil-actividad':
		//<---
		$ac_do = trim($_POST['do'] ?? '');
		$ac_type = (int)($_POST['ac_type'] ?? 0);
		$start = (int)($_POST['start'] ?? 0);
		//
		if($ac_do !== 'borrar') {
			$actividad = $tsActividad->getActividad($user_id, $ac_type, $start);
			$smarty->assign("tsActividad",$actividad);
			$smarty->assign("tsDo",$ac_do);
			$smarty->assign("tsUserID",$user_id);
		} else {
			echo $tsActividad->delActividad();
			die;
		}
		//--->
	break;
	case 'perfil-info':
		//<---
		require_once TS_EXTRAS . '/datos.php';
		$tsPaises = require_once TS_EXTRAS . "/Paises.php";
		// PERFIL INFO
		$tsPerfil = $tsCuenta->loadPerfil((int)$user_id);
		$smarty->assign("tsPerfil", $tsPerfil);
		// PAIS
		$smarty->assign("tsPais", $tsPaises[$tsPerfil['user_pais']]);
		//--->
	break;
	case 'perfil-posts':
		//<---
		$smarty->assign("tsGeneral",$tsCuenta->loadPosts($user_id));
		//--->
	break;
	case 'perfil-seguidores':
	case 'perfil-siguiendo':
		//<---
		$type = ($action === 'perfil-seguidores') ? 'seguidores' : 'siguiendo';

		$smarty->assign("tsType", $type);
		$smarty->assign("tsHide", $_GET['hide']); // MOSTRAR DIVS
		$smarty->assign("tsData", $tsMonitor->getFollows($type, (int)$user_id));
		//--->
	break;
	case 'perfil-medallas':
		//<---
		$smarty->assign("tsMedallas",$tsCuenta->loadMedallas($user_id));
		//--->
	break;
	default:
		die('0: Este archivo no existe.');
	break;
}
