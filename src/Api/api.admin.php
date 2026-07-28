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

$tsAdmin = Container::get(tsAdmin::class);
$tsMedal = Container::get(tsMedal::class);

// CODIGO
match($action) {
	'admin-medalla-asignar-form' => null,
	'admin-medalla-borrar' => print $tsMedal->DelMedalla(),
	'admin-medalla-asignar' => print $tsMedal->AsignarMedalla(),
	'admin-medallas-borrar-asignacion' => print $tsMedal->delAssign(),
	'admin-foto-borrar' => print $tsAdmin->DelFoto(),
	'admin-foto-setOpenClosed' => print $tsAdmin->setOpenClosedFoto(),
	'admin-foto-setShowHide' => print $tsAdmin->setShowHideFoto(),
	'admin-users-setInActivo' => print $tsAdmin->setUserInActivo(),
	'admin-users-sessions' => print $tsAdmin->delSession(),
	'admin-noticias-setInActive' => print Container::get(tsNoticias::class)->setNoticiaInActive(),
	'admin-sesiones-borrar' => print $tsAdmin->delSession(),
	'admin-nicks-change' => print $tsAdmin->ChangeNick_o_no(),
	'admin-blacklist-delete' => print Container::get(tsBloqueos::class)->deleteBlock(),
	'admin-badwords-delete' => print Container::get(tsCensura::class)->deleteBadWord(),
	'admin-ordenar-categorias' => print $tsAdmin->saveOrden(),
	default => print '0: Este archivo no existe.',
};
