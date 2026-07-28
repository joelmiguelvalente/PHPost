<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
	'moderacion-posts' => ['nivel' => 3, 'template' => 'main', 'ajax' => true],
	'moderacion-fotos' => ['nivel' => 3, 'template' => 'main', 'ajax' => true],
	'moderacion-users' => ['nivel' => 3, 'template' => 'main', 'ajax' => true],
	'moderacion-mps' =>   ['nivel' => 3, 'template' => 'main', 'ajax' => true]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.moderacion.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
$tsModeracion = Container::get(tsModeracion::class);

$do = trim($_GET['do'] ?? '');
// CODIGO
if(in_array($do, ['ocultar', 'reboot', 'sticky', 'openclosed'], true) ||
	isset($_POST['razon']) || isset($_POST['av_body'])) {
	$tsAjax = false;
}

match($action) {
	'moderacion-posts' => (static function() use ($do, $tsModeracion, $smarty, &$tsPage) {
		$pid = (int)($_POST['postid'] ?? $_POST['id'] ?? $_POST['pid'] ?? 0);
		match($do) {
			'view' => (static function() use ($tsModeracion, $pid, $smarty, &$tsPage) {
				$tsPage = 'p.posts.preview';
				$preview = $tsModeracion->getPreview($pid);
				$smarty->assign("tsPreview", $preview);
			})(),
			'ocultar' => print $tsModeracion->OcultarPost($pid, Html::escape($_POST['razon'])),
			'reboot' => print $tsModeracion->rebootPost($pid),
			'borrar' => (static function() use ($tsModeracion, $pid, $smarty, &$tsPage) {
				if (isset($_POST['razon'])) {
					echo $tsModeracion->deletePost($pid);
				} else {
					require_once TS_EXTRAS . "/datos.php";
					$tsPage = 'p.posts.mod';
					$smarty->assign("tsDenuncias", $tsDenuncias['posts']);
				}
			})(),
			'sticky' => print $tsModeracion->setSticky($pid),
			'openclosed' => print $tsModeracion->setOpenClosed($pid),
			default => null,
		};
	})(),
	'moderacion-users' => (static function() use ($do, $tsModeracion, $tsUser, $tsMonitor, $smarty, &$tsAjax) {
		$user_id = (int)($_POST['uid'] ?? 0);
		$username = $tsUser->getUserName($user_id);
		match($do) {
			'aviso' => (static function() use ($tsMonitor, $tsUser, $user_id, $username, $smarty) {
				if (isset($_POST['av_body'])) {
					$subject = trim($_POST['av_subject'] ?? '');
					$subject = (int)($_POST['av_type'] ?? 0);
					$aviso = "{$_POST['av_body']}\n\nStaff: {$tsUser->nick}";
					$aviso_resp = $tsMonitor->setAviso($user_id, $subject, $aviso, $type);
					if (!$aviso_resp) echo "0: Error al enviar el aviso a <strong>$username</strong>.";
					else echo "1: El avioso fue enviado con éxito a <strong>$username</strong>.";
				} else $smarty->assign("tsUsername", $tsUser->getUserName($user_id));
			})(),
			'ban' => (static function() use ($tsModeracion, $user_id, $tsUser, $smarty, &$tsAjax) {
				if (isset($_POST['b_causa'])) {
					$tsAjax = false;
					echo $tsModeracion->banUser($user_id);
				} else $smarty->assign("tsUsername", $tsUser->getUserName($user_id));
			})(),
			'unban' => (static function() use ($tsModeracion, &$tsAjax) {
				$tsAjax = false;
				echo $tsModeracion->rebootUser($_POST['id'], 'unban');
			})(),
			'reboot' => (static function() use ($tsModeracion, &$tsAjax) {
				$tsAjax = false;
				echo $tsModeracion->rebootUser($_POST['id'], 'reboot');
			})(),
			'info' => $smarty->assign("tsIUser", $tsModeracion->InfoUser($_POST['user_id'])),
			'editar' => (static function() use ($tsModeracion, &$tsAjax) {
				$tsAjax = false;
				echo $tsModeracion->EditarUser($_POST['user_id']);
			})(),
			default => null,
		};
		$smarty->assign("tsDo", $do);
	})(),
	'moderacion-mps' => (static function() use ($do, $tsModeracion, &$tsAjax) {
		$mid = $_POST['mpid'];
		$tsAjax = false;
		echo ($do === 'reboot') ? $tsModeracion->rebootMps($_POST['id']) : $tsModeracion->deleteMps($mid);
	})(),
	'moderacion-fotos' => (static function() use ($do, $tsModeracion, $smarty, &$tsAjax, &$tsPage) {
		$fid = (int)$_POST['fid'];
		match($do) {
			'reboot' => (static function() use ($tsModeracion, &$tsAjax) {
				$tsAjax = false;
				echo $tsModeracion->rebootFoto($_POST['id']);
			})(),
			'borrar' => (static function() use ($tsModeracion, $fid, $smarty, &$tsAjax, &$tsPage) {
				if ($_POST['razon']) {
					$tsAjax = false;
					echo $tsModeracion->deleteFoto($fid);
				} else {
					require_once TS_EXTRAS . '/datos.php';
					$tsPage = 'p.fotos.mod';
					$smarty->assign("tsDenuncias", $tsDenuncias['fotos']);
				}
			})(),
			default => null,
		};
	})(),
};
