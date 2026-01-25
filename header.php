<?php

/**
 * @name header.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/*
 * -------------------------------------------------------------------
 *  Estableciendo variables importantes
 * -------------------------------------------------------------------
 */

defined('TS_HEADER') OR define('TS_HEADER', TRUE);

const BASEPATH = __DIR__;
require_once BASEPATH . '/inc/config/Config.Paths.php';
require_once TS_CONFIG . '/Config.php';
require_once TS_CONFIG . '/Config.Session.php';

// Reporte de errores
error_reporting((Config::app('app.debug_all') ? E_ALL : (Config::app('app.debug') ? (E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : 0)));

ini_set('display_errors', Config::app('app.debug') ? '1' : '0');
ini_set('log_errors',     '1');
ini_set('error_log',      Config::app('paths.logs') . '/log-' . date('dmy') . '.log');

// Límite de ejecución
set_time_limit(300);

// Funciones
require_once TS_UTILS . '/IP.php';
require_once TS_UTILS . '/Paginator.php';

$IP = new IP;
$Paginator = new Paginator;

require_once TS_EXTRA . '/functions.php';

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
require_once TS_EXTRA . '/QueryString.php';

/*
 * -------------------------------------------------------------------
 *  Inicializamos los objetos principales
 * -------------------------------------------------------------------
 */
$cleanRequest = new LimpiarSolicitud();
$cleanRequest->limpiar();

// Cargamos el nucleo
$tsCore = new tsCore();

// Usuario
$tsUser = new tsUser();

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
$smarty = new tsSmarty();
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

// Obtejo usuario
$smarty->assign('tsUser', $tsUser);

// Avisos
$smarty->assign('tsAvisos', $tsMonitor->avisos);

// Nofiticaciones
$smarty->assign('tsNots', $tsMonitor->notificaciones);

// Mensajes
$smarty->assign('tsMPs', $tsMP->mensajes);
		
/**
 * Si hay alguna IP bloqueada por el Moderador/Administrador,
 * ejecutamos esta función, en caso contrario no hará nada
*/
$IPBAN = $IP->executeIP();
if(!filter_var($IPBAN, FILTER_VALIDATE_IP)) exit('Su ip no se pudo validar.');
if(db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM w_blacklist WHERE type = 1 && value = '{$IPBAN}' LIMIT 1"))) die('Tu IP fue bloqueada por el administrador/moderador.');

/**
 * Si hay un usuario baneado por el Moderador/Administrador,
 * ejecutamos esta función, en caso contrario no hará nada
*/
$banned_data = $tsUser->getUserBanned();

if(!empty($banned_data)){
   if(empty($_GET['action'])){
      $smarty->assign([
         'tsTitle' => "Usuario baneado - {$tsCore->settings['titulo']}",
         'tsBanned' => $banned_data
      ]);
      $smarty->loadFilter('output', 'trimwhitespace');
      $smarty->display('suspension.tpl');

   } else die('<div class="emptyError">Usuario suspendido</div>');
   //
   exit;
}

/**
 * Si la página esta en modo mantenimiento, ejecutamos la función
*/
if($tsCore->settings['offline'] == 1 && ($tsUser->is_admod != 1 && $tsUser->permisos['govwm'] == false) && $_GET['action'] != 'login-user'){
   $smarty->assign('tsTitle', "Sitio en mantenimiento - {$tsCore->settings['titulo']}");
   $smarty->assign('tsLogin', (isset($_GET["login"]) and $_GET["login"] == 'admin' ? true : false));

   if(empty($_GET["action"])) {
      $smarty->loadFilter('output', 'trimwhitespace');
      $smarty->display('mantenimiento.tpl');
   } else die('Espera un poco...');
   exit();
}