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
$tsCuenta = Container::get(tsCuenta::class);

// USER ID
$user_id = (int)$_POST['pid'];
if(empty($user_id))  {
	echo '0: El campo <b>user_id</b> es obligatorio.';
}

$username = $tsUser->getUserName($user_id);
$smarty->assign("tsUsername", $username);
// CODIGO
match($action) {
	'perfil-wall' => (static function() use ($tsCore, $tsUser, $tsCuenta, $user_id, $username, $smarty) {
		$tsMuro = Container::get(tsMuro::class);
		$tsGeneral = $tsCuenta->loadGeneral($user_id);
		$smarty->assign("tsGeneral", $tsGeneral);
		$privacidad = $tsMuro->getPrivacity(
			$user_id,
			$username,
			(int)$tsCuenta->isFollowed($user_id, true),
			(int)$tsCuenta->isFollowed($user_id, false)
		);
		if ($privacidad['muro']['status']) {
			$smarty->assign("tsMuro", $tsMuro->getWall($user_id));
			$smarty->assign("tsInfo", ['uid' => $user_id, 'nick' => $username]);
		}
		$smarty->assign("tsPrivacidad", $privacidad);
	})(),
	'perfil-actividad' => (static function() use ($tsActividad, $user_id, $smarty) {
		$ac_do = trim($_POST['do'] ?? '');
		$ac_type = (int)($_POST['ac_type'] ?? 0);
		$start = (int)($_POST['start'] ?? 0);
		if ($ac_do !== 'borrar') {
			$actividad = $tsActividad->getActividad($user_id, $ac_type, $start);
			$smarty->assign("tsActividad", $actividad);
			$smarty->assign("tsDo", $ac_do);
			$smarty->assign("tsUserID", $user_id);
		} else {
			echo $tsActividad->delActividad();
			die;
		}
	})(),
	'perfil-info' => (static function() use ($tsCuenta, $user_id, $smarty) {
		require_once TS_EXTRAS . '/datos.php';
		$tsPaises = require_once TS_EXTRAS . "/Paises.php";
		$tsPerfil = $tsCuenta->loadPerfil((int)$user_id);
		$smarty->assign("tsPerfil", $tsPerfil);
		$smarty->assign("tsPais", $tsPaises[$tsPerfil['user_pais']]);
	})(),
	'perfil-posts' => $smarty->assign("tsGeneral", $tsCuenta->loadPosts($user_id)),
	'perfil-seguidores', 'perfil-siguiendo' => (static function() use ($action, $tsMonitor, $user_id, $smarty) {
		$type = ($action === 'perfil-seguidores') ? 'seguidores' : 'siguiendo';
		$smarty->assign("tsType", $type);
		$smarty->assign("tsHide", $_GET['hide']);
		$smarty->assign("tsData", $tsMonitor->getFollows($type, (int)$user_id));
	})(),
	'perfil-medallas' => $smarty->assign("tsMedallas", $tsCuenta->loadMedallas($user_id)),
	default => die('0: Este archivo no existe.'),
};
