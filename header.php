<?php
/**
 * Archivo de Inicialización del Sistema
 *
 * Carga las clases base y ejecuta la solicitud.
 *
 * @name    header.php
 * @author  PHPost Team
 */

/*
 * -------------------------------------------------------------------
 *  Estableciendo variables importantes
 * -------------------------------------------------------------------
 */

	if( defined('TS_HEADER') ) return;

	// Sesión
	if(!isset($_SESSION)) session_start();

	// Reporte de errores
	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
	ini_set('display_errors', TRUE);

	// Límite de ejecución
	set_time_limit(300);

/*
 * -------------------------------------------------------------------
 *  Definiendo constantes
 * -------------------------------------------------------------------
 */
	//DEFINICION DE CONSTANTES
	define('TS_ROOT', __DIR__);

	define('TS_HEADER', TRUE);

	define('TS_THEMES',	TS_ROOT . '/themes/');
	define('TS_INCLUDES',TS_ROOT . '/inc/');
	define('TS_STORAGE', TS_INCLUDES . 'storage/');
	define('TS_CLASS', 	TS_INCLUDES . 'class/');
	define('TS_LIBS', 	TS_INCLUDES . 'libs/');
	define('TS_EXTRA', 	TS_INCLUDES . 'ext/');
	define('TS_FILES', 	TS_ROOT . '/files/');
	define('TS_SMARTY', 	TS_LIBS . 'smarty/');
	define('TS_PLUGINS', TS_LIBS . 'plugins/');
	define('TS_CACHE', 	TS_STORAGE . 'cache/');
	
	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('./'));

/*
 * -------------------------------------------------------------------
 *  Agregamos los archivos globales
 * -------------------------------------------------------------------
 */

	// Funciones
	include TS_EXTRA.'functions.php';

	// Nucleo
	include TS_CLASS.'c.core.php';
	
	// Controlador de usuarios
	include TS_CLASS.'c.user.php';

	// Monitor de usuario
	include TS_CLASS.'c.monitor.php';
	
	// Actividad de usuario
	include TS_CLASS.'c.actividad.php';

	// Mensajes de usuario
	include TS_CLASS.'c.mensajes.php';

	// Smarty
	require_once TS_CLASS . 'c.smarty.php';
	
	// Crean requests
	include TS_EXTRA.'QueryString.php';

/*
 * -------------------------------------------------------------------
 *  Inicializamos los objetos principales
 * -------------------------------------------------------------------
 */

	// Cargamos el nucleo
	$tsCore = new tsCore();
	
	// Usuario
	$tsUser = new tsUser();

	// Monitor
	$tsMonitor = new tsMonitor();

	// Actividad
	$tsActividad = new tsActividad();

	// Mensajes
	$tsMP = new tsMensajes();

	// Definimos el template a utilizar
	$tsTema = $tsCore->settings['tema']['t_path'];
	if(empty($tsTema)) $tsTema = 'default';
	define('TS_TEMA', $tsTema);

	// Smarty
	$smarty = new tsSmarty();
	// Nueva configuración
	$smarty->output(false);

/*
 * -------------------------------------------------------------------
 *  Asignación de variables
 * -------------------------------------------------------------------
*/
// Configuraciones
$smarty->assign('tsConfig', $tsCore->settings);
$smarty->assign('tsRoutes', $tsCore->route());

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
$IPBAN = (isset($_SERVER["X_FORWARDED_FOR"])) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
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