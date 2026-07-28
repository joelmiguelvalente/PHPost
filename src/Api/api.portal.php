<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     PHPost Team
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
	'portal-posts_config'     => ['nivel' => 2, 'template' => '', 'ajax' => true],
	'portal-posts_pages'      => ['nivel' => 2, 'template' => 'posts', 'ajax' => false],
	'portal-favs_pages'       => ['nivel' => 2, 'template' => 'posts', 'ajax' => false],
	'portal-activity_pages'   => ['nivel' => 2, 'template' => 'actividad', 'ajax' => false],
];

if (!array_key_exists($action, ACTIONS)) {
	http_response_code(403);
	exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.portal.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);

if ($tsLevelMsg != 1) {
	echo '0: ' . $tsLevelMsg['mensaje'];
	die();
}

$tsPortal = Container::get(tsPortal::class);

// CODIGO
match ($action) {
	'portal-posts_config' => print $tsPortal->savePostsConfig(),
	'portal-posts_pages' => (static function() use ($tsPortal, $smarty) {
		$tsPosts = $tsPortal->getMyPosts();
		$smarty->assign("tsPosts", $tsPosts['data']);
		$smarty->assign("tsPages", $tsPosts['pages']);
		$smarty->assign("tsType", 'posts');
	})(),
	'portal-favs_pages' => (static function() use ($tsPortal, $smarty) {
		$tsPosts = $tsPortal->getFavorites();
		$smarty->assign("tsPosts", $tsPosts['data']);
		$smarty->assign("tsPages", $tsPosts['pages']);
		$smarty->assign("tsType", 'favs');
	})(),
	'portal-activity_pages' => (static function() use ($tsActividad, $user_id, $smarty) {
		$actividad = $tsActividad->getActividadFollows();
		if (!is_array($actividad)) {
			die('<div class="emptyData">' . $actividad . '</div>');
		}
		$smarty->assign("tsActividad", $actividad);
		$smarty->assign("tsUserID", $user_id);
	})(),
	default => die('0: Este archivo no existe.'),
};
