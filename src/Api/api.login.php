<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
	'login-user'	 => ['nivel' => 1, 'template' => '', 'ajax' => false],
	'login-activar' => ['nivel' => 1, 'template' => '', 'ajax' => false],
	'login-salir' 	 => ['nivel' => 1, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
	http_response_code(403);
	exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.login.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) {
	echo '0: '.$tsLevelMsg;
	die();
}

// CODIGO
switch($action){
	case 'login-user':
		$username = Html::escape($_POST['username']);
		// La contraseña no se sanitiza! aprendido :D
		$password = (string) ($_POST['password'] ?? '');
		$remember = ((string)$_POST['remember'] === 'true');
		//
		if(empty($username) || empty($password)) echo '0: Faltan datos';
		else echo $tsUser->loginUser($username, $password, $remember, null);
	break;
	case 'login-activar':
		//<--
			$activar = $tsUser->userActivate();
			if($activar['user_password'])
				$tsUser->loginUser($activar['user_nick'], $activar['user_password'], true, $tsCore->settings['url'].'/cuenta/');
			else {
				$tsPage = "aviso";
				$tsAjax = 0;
				$tsAviso = [
					'titulo' => 'Error al activar tu cuenta',
					'mensaje' => 'El código de validación es incorrecto.'
				];
				//
				$smarty->assign("tsAviso",$tsAviso);
			}
		//-->
	break;
	case 'login-salir':
		//<---
		$tsUser->logoutUser((int)$tsUser->uid, $tsCore->settings['url']);
		//--->
	break;
}
