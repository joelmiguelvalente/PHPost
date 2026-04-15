<?php

/**
 * @name src/Api/api.cuenta.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

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
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . "/c.moderacion.php";
$tsMod = new tsMod($tsCore, $tsUser);

$do = trim($_GET['do'] ?? '');
// CODIGO
if(in_array($do, ['ocultar', 'reboot', 'sticky', 'openclosed'], true) ||
	isset($_POST['razon']) || isset($_POST['av_body'])) {
	$tsAjax = false;
}

switch($action){
	case 'moderacion-posts':
		// POST ID
		$pid = (int)($_POST['postid'] ?? $_POST['id'] ?? $_POST['pid'] ?? 0);
		// ACCIONES SECUNDARIAS
		switch($do){
			case 'view':
				$tsPage = 'p.posts.preview';
				$preview = $tsMod->getPreview($pid);
				$smarty->assign("tsPreview",$preview);
			break;
			case 'ocultar':
				echo $tsMod->OcultarPost($pid, $tsCore->setSecure($_POST['razon']));
			break;
			case 'reboot':
				echo $tsMod->rebootPost($pid);
			break;
			case 'borrar':
				if(isset($_POST['razon'])) {
					echo $tsMod->deletePost($pid);
				} else {
					require_once TS_EXTRAS . "/datos.php";
					$tsPage = 'p.posts.mod';
					$smarty->assign("tsDenuncias", $tsDenuncias['posts']);   
				}
			break;
			case 'sticky':
				echo $tsMod->setSticky($pid);
			break;
			case 'openclosed':
				echo $tsMod->setOpenClosed($pid);
			break;
		}
	break;
	case 'moderacion-users':
		// POST ID
		$user_id = (int)($_POST['uid'] ?? 0);
		$username = $tsUser->getUserName($user_id);
		// ACCIONES SECUNDARIAS
		switch($do){
			case 'aviso':
				if(isset($_POST['av_body'])) {
					$subject = trim($_POST['av_subject'] ?? '');
					$subject = (int)($_POST['av_type'] ?? 0);
					$aviso = "{$_POST['av_body']}\n\nStaff: {$tsUser->nick}";
					$aviso_resp = $tsMonitor->setAviso($user_id, $subject, $aviso, $type);
					if(!$aviso_resp) echo "0: Error al enviar el aviso a <strong>$username</strong>.";
					else echo "1: El avioso fue enviado con &eacute;xito a <strong>$username</strong>.";
				} else $smarty->assign("tsUsername", $tsUser->getUserName($user_id));
			break;
			case 'ban':
				if(isset($_POST['b_causa'])) {
					$tsAjax = false;
					echo $tsMod->banUser($user_id);
				}  else $smarty->assign("tsUsername", $tsUser->getUserName($user_id));
			break;
			case 'unban':
				$tsAjax = false;
				echo $tsMod->rebootUser($_POST['id'], 'unban');
			break;
			case 'reboot':
				$tsAjax = false;
				echo $tsMod->rebootUser($_POST['id'], 'reboot');
			break;
			case 'info':
				$smarty->assign("tsIUser", $tsMod->InfoUser($_POST['user_id']));
			break;
			case 'editar':
				$tsAjax = false;
				echo $tsMod->EditarUser($_POST['user_id']);
			break;
		}
		$smarty->assign("tsDo",$do);
	break;
	case 'moderacion-mps':
		// MP ID
		$mid = $_POST['mpid'];
		// ACCIONES SECUNDARIAS
		$tsAjax = false;
		echo ($do === 'reboot') ? $tsMod->rebootMps($_POST['id']) : $tsMod->deleteMps($mid);
	break;
	case 'moderacion-fotos':
		$fid = (int)$_POST['fid'];
		// ACCIONES SECUNDARIAS
			switch($do){
				case 'reboot':
					$tsAjax = false;
					echo $tsMod->rebootFoto($_POST['id']);
				break;
				case 'borrar':
					if($_POST['razon']) {
						$tsAjax = false;
						echo $tsMod->deleteFoto($fid);
					} else {
						require_once TS_EXTRAS . '/datos.php';
						$tsPage = 'p.fotos.mod';
						$smarty->assign("tsDenuncias",$tsDenuncias['fotos']);   
					}
				break;
			}
		//-->
	break;
}
