<?php

declare(strict_types=1);

/**
 * @package    PHPost/Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 * 
 * $ctx = Controller::page(_pagina_)->requireLevel(_nivel_);
 * $ctx->getLevel() obtinene el nivel para comprobar
 * $ctx->exportLegacy() sincroniza con el sistema
 */

$ctx = Controller::page('fotos')->requireLevel(0);
// sincronizamos
$ctx->exportLegacy();

// PARA LAS FOTOS...
$action = trim($_GET['action'] ?? '');
if((int)$tsCore->settings['c_fotos_private'] === 1 && !in_array($action, ['', 'ver'], true)) {
	$ctx->requireLevel(2);  
}

$tsLevelMsg = $tsCore->setLevel($ctx->getLevel(), true);
if (is_array($tsLevelMsg)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", $tsLevelMsg);
   // sincroniza nuevamente
   $ctx->exportLegacy();
}

if($ctx->continue()) {

	require_once TS_CLASS . "/c.fotos.php";
	$tsFotos = new tsFotos($tsCore, $tsUser);

	switch($action){
		case '':
			$smarty->assign("tsLastFotos", $tsFotos->getLastFotos());
			$smarty->assign("tsLastComments", $tsFotos->getLastComments());
			$q = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT stats_miembros, stats_fotos, stats_foto_comments FROM w_stats WHERE stats_no = \'1\''));
			$smarty->assign("tsStats", $q);
			
		break;
		case 'agregar':
			if(!empty($_POST['title'])) {
				$foto = $tsFotos->newFoto();
				if(!is_array($foto) && $foto > 0){
					$titulo = (new Extras)->slugify(trim($_POST['title'] ?? ''));
					header("Location: {$tsCore->settings['url']}/fotos/{$tsUser->nick}/{$foto}/{$titulo}.html");
					return;
				}
				$tsPage = 'aviso';
				$smarty->assign("tsAviso", [
					'titulo' => 'Opps...',
					'mensaje' => $result,
					'but' => 'Volver',
					'link' => "{$tsCore->settings['url']}/fotos/agregar.php"
				]);

			}
			
		break;
		case 'editar':
			if(empty($_POST['titulo'])){
				$tsFoto = $tsFotos->getFotoEdit();
				if(!is_array($tsFoto)){
					$tsPage = 'aviso';
					$smarty->assign("tsAviso",array('titulo' => 'Opps...', 'mensaje' => $tsFoto, 'but' => 'Ir a Fotos', 'link' => "{$tsCore->settings['url']}/fotos/"));
				}
				else $smarty->assign("tsFoto", $tsFoto);
			} else {
				$tsPage = 'aviso';
				$tsFoto = $tsFotos->editFoto();
				$smarty->assign("tsAviso",array('titulo' => 'Opps...', 'mensaje' => $tsFoto, 'but' => 'Ir a Fotos', 'link' => "{$tsCore->settings['url']}/fotos/"));
			}
		break;
		case 'borrar':
			$tsAjax = 1;
			echo $tsFotos->delFoto();
		break;
		case 'ver':
			$tsFoto = $tsFotos->getFoto();
			// TITULO
			$tsTitle = $tsFoto['foto']['f_title'].' - '.$tsFoto['foto']['user_name'].' - '.$tsCore->settings['titulo'];
			
			if((int)$tsFoto['foto']['f_status'] === 1 && (!$tsUser->is_admod && $tsUser->permiso('moderacion.panel.acceso') == false)) {
				$tsPage = 'aviso';
				$smarty->assign("tsAviso", [
					'titulo' => 'Opps...',
					'mensaje' => 'Esta foto se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias',
					'but' => 'Ir a Fotos',
					'link' => "{$tsCore->settings['url']}/fotos/"
				]);
			} elseif((int)$tsFoto['foto']['exist'] === 0) {
				$tsPage = 'aviso';
				$smarty->assign("tsAviso", [
					'titulo' => 'Opps...',
					'mensaje' => 'Esta foto no existe',
					'but' => 'Ir a Fotos',
					'link' => "{$tsCore->settings['url']}/fotos/"
				]);
			} else {
				$smarty->assign("tsFoto", $tsFoto['foto']);
				$smarty->assign("tsUFotos", $tsFoto['last']);
				$smarty->assign("tsFFotos", $tsFoto['amigos']);
				$smarty->assign("tsFComments", $tsFoto['comentarios']);
				$smarty->assign("tsFVisitas", $tsFoto['visitas']);
				$smarty->assign("tsFMedallas", $tsFoto['medallas']);
				$smarty->assign("tsTMedallas", $tsFoto['m_total']);
			}
		break;
		case 'album':
			$username = $_GET['user'];
			$user_id = $tsUser->getUserID($username);
			if(empty($user_id)){
				$tsPage = 'aviso';
				$smarty->assign("tsAviso",array('titulo' => 'Opps...', 'mensaje' => 'Este usuario no existe.', 'but' => 'Ir a Fotos', 'link' => "{$tsCore->settings['url']}/fotos/"));
			} else {
				$tsFotox = $tsFotos->getFotos($user_id);
				$smarty->assign("tsFotos", $tsFotox);
				$smarty->assign("tsFUser", array($user_id, $username));
			}

		break;
	}

	$smarty->assign("tsAction",$action);
	
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
