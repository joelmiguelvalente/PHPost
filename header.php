<?php

declare(strict_types=1);

/**
 * @package    PHPost/Utils
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

/*
 * -------------------------------------------------------------------
 *  Estableciendo variables importantes
 * -------------------------------------------------------------------
 */

defined('TS_HEADER') OR define('TS_HEADER', TRUE);

define('BASEPATH', realpath(__DIR__));
require_once BASEPATH  . '/config/Config.Paths.php';
require_once TS_CONFIG . '/Config.php';
require_once TS_CONFIG . '/Config.Session.php';
require_once TS_CONFIG . '/Config.Errors.php';

# Si no usas PHP 8.5 se ejecuta el archivo
require_once TS_EXTRAS . '/polyfill.uri.php';
require_once TS_UTILS . '/Compat.php';

date_default_timezone_set(Config::app('localization.timezone'));

// Límite de ejecución
set_time_limit(300);

// Evitamos llamar al archivo a cada clase
require_once TS_UTILS . "/Paginator.php";
require_once TS_UTILS . "/IP.php";
require_once TS_UTILS . "/Extras.php";
require_once TS_UTILS . "/Themes.php";

require_once TS_EXTRAS . '/functions.php';

// Nucleo
require_once TS_CLASS . '/c.core.php';

// Controlador de usuarios
require_once TS_CLASS . '/c.user.php';

// Monitor de usuario
require_once TS_CLASS . '/c.monitor.php';

// Actividad de usuario
require_once TS_CLASS . '/c.actividad.php';

// Mensajes de usuario
require_once TS_CLASS . '/c.mensajes.php';

// Crean requests
require_once TS_EXTRAS . '/QueryString.php';

// Controller para inc/php/...
require_once TS_UTILS . '/Controller.php';

/*
 * -------------------------------------------------------------------
 *  Inicializamos los objetos principales
 * -------------------------------------------------------------------
 */
(new LimpiarSolicitud())->limpiar();

// Cargamos el nucleo
$tsCore = new tsCore();

// Usuario
$tsUser = new tsUser($tsCore);

// Monitor
$tsMonitor = new tsMonitor($tsCore, $tsUser);

// Actividad
$tsActividad = new tsActividad($tsCore, $tsUser);

// Mensajes
$tsMP = new tsMensajes($tsCore, $tsUser);

// Definimos el template a utilizar
define('TS_TEMA', $tsCore->settings['tema']['t_path'] ?? 'default');

// Smarty
require_once TS_CLASS . '/c.smarty.php';
$smarty = new tsSmarty;
$smarty->output(false);

/*
 * -------------------------------------------------------------------
 *  Asignación de variables
 * -------------------------------------------------------------------
 */
// Configuraciones
$smarty->assign('tsConfig', $tsCore->settings);
$smarty->assign('tsRoutes', $tsCore->route());
$smarty->assign('tsCategories', $tsCore->getCategorias());
$smarty->assign('tsModerar', $tsCore->getNovemods());

// Obtejo usuario
$smarty->assign('tsUser', $tsUser);

// Avisos
$smarty->assign('tsAvisos', $tsMonitor->avisos);

// Nofiticaciones
$smarty->assign('tsNots', $tsMonitor->notificaciones);

// Mensajes
$smarty->assign('tsMPs', $tsMP->mensajes);

$smarty->assign('tsDescription', Config::app('app.description'));

/**
 * Si hay alguna IP bloqueada por el Moderador/Administrador,
 * ejecutamos esta función, en caso contrario no hará nada
 */
$tsUser->getUserBlacklist();

/**
 * Si hay un usuario baneado por el Moderador/Administrador,
 * ejecutamos esta función, en caso contrario no hará nada
 */
$banned_data = $tsUser->getUserBanned();

if (!empty($banned_data)) {
	if (empty($_GET['action'])) {
		$smarty->assign([
			'tsTitle' => "Usuario baneado - {$tsCore->settings['titulo']}",
			'tsBanned' => $banned_data,
		]);
		$smarty->setTheme(TS_TEMA);
		$smarty->setPage('suspension');
		$smarty->load('suspension', true);
		die;
	}
}

/**
 * Si la página esta en modo mantenimiento, ejecutamos la función
 */
$actionOFF = trim($_GET['action'] ?? '');
if ((int)$tsCore->settings['offline'] === 1 && (!$tsUser->is_admod && !$tsUser->permiso('global.sistema.modo_mantenimiento')) && $actionOFF !== 'login-user') {

	$login = isset($_GET["login"]) && $_GET["login"] === 'admin';
	$smarty->assign('tsTitle', "Sitio en mantenimiento - {$tsCore->settings['titulo']}");
	$smarty->assign('tsLogin', $login);

	$smarty->setTheme(TS_TEMA);
	$smarty->setPage('mantenimiento');
	$smarty->load('mantenimiento', true);
	
	exit();
}
