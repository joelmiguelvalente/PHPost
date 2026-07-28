<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
   'muro-stream'	=> ['nivel' => 2, 'template' => 'stream', 'ajax' => true],
   'muro-likes'	=> ['nivel' => 2, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.muro.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASS
$tsMuro = Container::get(tsMuro::class);

// CODIGO
match($action) {
	'muro-stream' => (static function() use ($tsMuro, $smarty, &$tsAjax, &$tsPage) {
		$do = trim($_GET['do'] ?? '');
		if ($do === 'check') {
			echo $tsMuro->ajaxCheck();
			$tsAjax = false;
		} elseif ($do === 'post') {
			$tsStream = $tsMuro->streamPost();
			if (!is_array($tsStream) && substr($tsStream, 0, 1) === '0') {
				echo $tsStream;
			} else {
				$tsWall['data'][1] = $tsStream;
				$smarty->assign("tsMuro", $tsWall);
				$tsPrivacidad['muro_firma']['status'] = true;
				$smarty->assign("tsPrivacidad", $tsPrivacidad);
			}
		} elseif ($do === 'more') {
			$tsCuenta = Container::get(tsCuenta::class);
			$user_id = (int)($_POST['pid'] ?? 0);
			$start = (int)($_POST['start'] ?? 0);
			$follow = $tsCuenta->isFollowed((int)$user_id, true) ? 1 : 0;
			$priv = $tsMuro->getPrivacity($user_id, 'null', $follow, 0);
			$smarty->assign("tsPrivacidad", $priv);
			if ($_GET['type'] === 'wall') $tsStream = $tsMuro->getWall($user_id, (int)$start);
			else if ($_GET['type'] === 'news') $tsStream = $tsMuro->getNews($start);
			if (!is_array($tsStream)) {
				echo $tsStream;
				$tsAjax = true;
			} else {
				$smarty->assign("tsMuro", $tsStream);
			}
		} elseif ($do === 'repost') {
			$tsPage = 'p.muro.stream.comments';
			$tsRepost = $tsMuro->streamRepost();
			if (!is_array($tsRepost)) {
				echo $tsRepost;
				$tsAjax = true;
			} else {
				$tsComments['data'][1] = $tsRepost;
				$smarty->assign("tsComments", $tsComments);
			}
		} elseif ($do === 'more_comments') {
			$tsPage = 'p.muro.stream.comments';
			$tsComments = $tsMuro->getComments();
			if (!is_array($tsComments)) {
				echo $tsComments;
				$tsAjax = true;
			} else {
				$smarty->assign("tsComments", $tsComments);
			}
		} elseif ($do === 'delete') {
			echo $tsMuro->deletePost();
			$tsAjax = true;
		}
	})(),
	'muro-likes' => (static function() use ($tsMuro) {
		$action = (trim($_GET['do'] ?? '') === '') ? $tsMuro->likePost() : $tsMuro->showLikes();
		echo json_encode($action);
	})(),
	default => die('0: Este archivo no existe.'),
};
