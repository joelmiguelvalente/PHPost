<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @author     Miguel92
 * @copyright  2026
 */

/*
 * -------------------------------------------------------------------
 *  Estableciendo variables importantes
 * -------------------------------------------------------------------
 */

defined('TS_HEADER') OR define('TS_HEADER', TRUE);

define('BASEPATH', realpath(__DIR__));

require_once BASEPATH  . '/config/bootstrap.php';
require_once TS_UTILS  . '/Theme/Container.php';

require_once TS_EXTRAS . '/functions.php';

/*
 * -------------------------------------------------------------------
 *  Inicializamos los objetos principales
 * -------------------------------------------------------------------
 */
Container::get(RequestGuard::class)->check();

// Factories!
Container::factory(Paginator::class, fn () =>
	new Paginator(Container::get(tsCore::class)->settings['url'])
);
Container::factory(Avatar::class, fn () =>
	new Avatar(Container::get(tsCore::class)->settings['url'])
);

$tsCore 	 = Container::get(tsCore::class);
$tsUser 	 = Container::get(tsUser::class);
$tsMonitor 	 = Container::get(tsMonitor::class);
$tsActividad = Container::get(tsActividad::class);
$tsMensajes  = Container::get(tsMensajes::class);
$Routes 	 = Container::get(Routes::class);
$smarty 	 = Container::get(tsSmarty::class);

/*
 * -------------------------------------------------------------------
 *  Asignación de variables
 * -------------------------------------------------------------------
 */

// Definimos el template a utilizar
define('TS_TEMA', $tsCore->getThemePath());

// Minificar HTML
$smarty->output(Config::app('app.minifyHTML'));

define('CSP_NONCE', CspNonce::generate());
$GLOBALS['csp_nonce'] = CSP_NONCE;

Container::get(Response::class)->csp(CSP_NONCE);

foreach([
	'tsConfig' 		=> $tsCore->settings,
	'tsCategories' 	=> $tsCore->getCategorias(),
	'tsModerar' 	=> $tsCore->getNovemods(),
	'tsRoutes' 		=> $Routes->flattenRoutes(),
	'tsUser' 		=> $tsUser,
	'tsAvisos' 		=> $tsMonitor->avisos,
	'tsNots' 		=> $tsMonitor->notificaciones,
	'tsMPs' 		=> $tsMensajes->mensajes,
	'tsDescription' => Config::app('app.description'),
	'tsThemeCurrent' => $tsCore->getThemePath(),
	'tsCsrf' 		=> Container::get(CsrfToken::class)->generate(),
	'csp_nonce' 	=> CSP_NONCE
] as $key => $asignacion) {
	$smarty->assign($key, $asignacion);
}

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

Hook::register('header', fn() => Ads::show('header'));
