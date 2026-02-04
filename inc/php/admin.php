<?php

/**
 * @name agregar.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/**
 * Inicializamos variable
 * 
 * $tsPage  	= Plantilla para mostrar con este archivo.
 * $tsLevel 	= Nivel de acceso a esta pagina (ver faqs).
 * $tsAjax  	= La respuesta sera por ajax si/no.
 * $tsContinue	= Continuar con la ejecución
 */

$tsPage  = "admin";
$tsLevel = 4; 
$tsAjax  = (!isset($_GET['ajax']) && empty($_GET['ajax']));
$tsContinue = true;

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
// VERIFICAMOS EL NIVEL DE ACCESO ANTES CONFIGURADO
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg){	
	$tsPage = 'aviso';
	$tsAjax = 0;
	$smarty->assign("tsAviso",$tsLevelMsg);
	//
	$tsContinue = false;
}

if(!$tsUser->is_member) {
	header("Location: {$tsCore->settings['url']}");
}

if($tsContinue) {

	// ACTION
	$action = htmlspecialchars(trim($_GET['action'] ?? ''));
	$act = htmlspecialchars(trim($_GET['act'] ?? ''));

	// CLASE POSTS
	require_once TS_CLASS . "/c.admin.php";
	$tsAdmin = new tsAdmin($tsCore, $tsUser);
	$AdminHelper = $tsAdmin->AdminHelper;
	
	# CENTRO DE ADMINISTRACION
	if(empty($action)) {
		$smarty->assign("tsAdmins", $tsAdmin->getAdmins());
      $smarty->assign("tsInstalled", $tsAdmin->getInst());

   # SOPORTE Y CREDITOS
	} elseif($action === 'creditos'){
		$smarty->assign("tsVersion", $tsAdmin->getVersions());

	# CONFIGURACION
	} elseif($action === 'configs') {
		// GUARDAR CONFIGURACION
		if(!empty($_POST['titulo'])) {
			if($tsAdmin->saveConfig()) $tsCore->redirectAdmin($action);
		}

	# TEMAS
	} elseif($action === 'temas') {
   	require_once TS_CLASS . "/c.themes.php";
		$tsThemes = new tsThemes($tsCore, $tsUser);

		if(empty($act)) $smarty->assign("tsTemas", $tsThemes->getTemas());
		elseif($act === 'editar'){
			$smarty->assign("tsTema", $tsThemes->getTema());
			if(isset($_POST['path']) && !empty($_POST['path'])) {
				if($tsThemes->saveTema())  $tsCore->redirectAdmin($action);
			}
		} elseif($act === 'borrar'){
			if(!empty($_POST['confirm'])) {
				if($tsThemes->deleteTema()) $tsCore->redirectAdmin($action);
			}
			$smarty->assign("themeName", $_GET['tt']);
		}

   # NOTICIAS
   } elseif($action === 'news') {
   	require_once TS_CLASS . "/c.noticias.php";
		$tsNoticias = new tsNoticias($tsCore, $tsUser);
      if(empty($act)) $smarty->assign("tsNews", $tsNoticias->obtenerNoticias());
      elseif($act === 'nueva' && !empty($_POST['not_body'])) {
			if($tsNoticias->nuevaNoticia()) $tsCore->redirectAdmin($action);
      } elseif($act === 'editar') {
         if(empty($_POST['not_body'])) {
         	$smarty->assign("tsNew", $tsNoticias->obtenerNoticia());
         } else if($tsNoticias->editarNoticia()) $tsCore->redirectAdmin($action);
      }  elseif($act === 'borrar' && isset($_POST['confirmar'])) {
         if($tsNoticias->eliminarNoticia()) $tsCore->redirectAdmin($action, 'borrar');
		}

	# PUBLICIDADES
	} elseif($action === 'ads'){
		if(!empty($_POST['save'])){
			if($tsAdmin->saveAds()) $tsCore->redirectAdmin($action);
		}

   # MEDALLAS
   } elseif($action === 'medals') {
    	# Incluimos el archivo
    	require_once TS_CLASS . "/c.medals.php";
    	$tsMedallas = new tsMedal($tsCore, $tsUser);

    	#
    	if(empty($act)) $smarty->assign("tsMedals", $tsMedallas->adGetMedals());
    	elseif($act === 'nueva') {
    		if(isset($_POST['save'])) {
				$agregar = $tsMedallas->adNewMedal();
				if($agregar) $tsCore->redirectAdmin($action);
				else $smarty->assign("tsError", $agregar); 
         }
    	} elseif($act === 'showassign') {
    		$smarty->assign("tsAsignaciones", $tsMedallas->adGetAssign());
      } elseif($act === 'editar') {
         if(isset($_POST['edit'])) {
         	$editar = $tsMedallas->editMedal();
         	if($editar) {
         		$tsCore->redirectAdmin($action, 'save', "&act=editar&mid={$_GET['mid']}");
         	} else $smarty->assign("tsError", $editar); 
         } else $smarty->assign("tsMed", $tsMedallas->adGetMedal());      
      }
      # Evitamos que se repita
      if($act === 'nueva' || $act === 'editar') {
			//ICONOS PARA LAS MEDALLAS
			$smarty->assign("tsIcons", $AdminHelper->getExtraIcons('med', 32));
			//RANGOS DISPONIBLES
			$smarty->assign("tsRangos", $tsAdmin->getAllRangos());
      	if(isset($_POST["save"]) || isset($_POST["edit"])) {
	      	$smarty->assign("tsMed", [
					'm_title' => $_POST['m_title'], 
					'm_description' => $_POST['m_description'], 
					'm_image' => $_POST['m_image'], 
					'm_cant' => $_POST['m_cant'], 
					'm_type' => $_POST['m_type'], 
					'm_cond_user' => $_POST['m_cond_user'], 
					'm_cond_user_rango' => $_POST['m_cond_user_rango'], 
					'm_cond_post' => $_POST['m_cond_post'], 
					'm_cond_foto' => $_POST['m_cond_foto']
				]);
			}
      }

	# POSTS
	} elseif($action === 'posts'){
		 if(!$act) {
		 $smarty->assign("tsAdminPosts", $tsAdmin->GetAdminPosts());
		 }
	# FOTOS
	} elseif($action === 'fotos'){
		if(!$act) $smarty->assign("tsAdminFotos", $tsAdmin->GetAdminFotos());
	# ESTADÍSTICAS
	} elseif($action === 'stats'){
		$smarty->assign("tsAdminStats", $tsAdmin->GetAdminStats());
	# CAMBIOS DE NOMBRE DE USUARIO
	} elseif($action === 'nicks'){
		$smarty->assign("tsAdminNicks", $tsAdmin->getChangeNicks($act));
   // LISTA NEGRA
   } elseif($action === 'blacklist') {
		if(!$act) $smarty->assign("tsBlackList", $tsAdmin->getBlackList());
		elseif($act === 'editar' OR $act === 'nuevo'){
         if($_POST['edit'] OR $_POST['new']){
         	$mode = ($_POST['edit']) ? $tsAdmin->saveBlock() : $tsAdmin->newBlock();
				if($mode == 1) $tsCore->redirectTo('/admin/blacklist?save=true');
				else $smarty->assign("tsError", $mode); 
				$merge = [
					'value' => $_POST['value'], 
					'type' => $_POST['type']
				];
				if(isset($_POST['new'])) $merge = array_merge($merge, ['reason' => $_POST['reason']]);
				$smarty->assign("tsBL", $merge);

         } else $smarty->assign("tsBL", $tsAdmin->getBlock());
      }
   // CENSURAS
   } elseif($action === 'badwords'){
		 if(!$act) {
		 $smarty->assign("tsBadWords", $tsAdmin->getBadWords());
		 }elseif($act === 'editar'){
         if($_POST['edit']){
                $editar = $tsAdmin->saveBadWord();
				if($editar == 1) $tsCore->redirectTo('/admin/badwords?save=true');
				else $smarty->assign("tsError", $editar); $smarty->assign("tsBW",array(word => $_POST['before'], swop => $_POST['after'], method => $_POST['method'], type => $_POST['type']));
         }else $smarty->assign("tsBW", $tsAdmin->getBadWord());
		 }elseif($act === 'nuevo'){
		  if($_POST['new']){
                $nuevo = $tsAdmin->newBadWord();
				if($nuevo == 1) $tsCore->redirectTo('/admin/badwords?save=true');
				else $smarty->assign("tsError", $nuevo); $smarty->assign("tsBW",array(word => $_POST['before'], swop => $_POST['after'], method => $_POST['method'], type => $_POST['type'], reason => $_POST['reason']));
          }
          }
	// CONECTADOS A LA COMUNIDAD
	} elseif($action === 'sesiones'){
		 if(!$act) {
		 $smarty->assign("tsAdminSessions", $tsAdmin->GetSessions());
		 }
   # AFILIADOS
	} elseif($action === 'afs'){
        // CLASS
        include("../class/c.afiliado.php");
        $tsAfiliado = new tsAfiliado;
        // QUE HACER
	   if($act === ''){
        // AFILIADOS
        $smarty->assign("tsAfiliados", $tsAfiliado->getAfiliados('admin'));
	   } elseif($act === 'editar'){
            if($_POST['edit']){
                if($tsAfiliado->EditarAfiliado()) $tsCore->redirectTo('/admin/afs?act=editar&aid='.$_GET['aid'].'&save=true');
            }
				$smarty->assign("tsAf", $tsAfiliado->getAfiliado('admin'));

                
        }
	} elseif($action === 'pconfigs'){
		if(!empty($_POST['save'])){
			if($tsAdmin->savePConfigs()) $tsCore->redirectTo('/admin/pconfigs?save=true');
		}
	} elseif($action === 'cats'){
		if(!empty($_GET['ordenar'])){
			$tsAdmin->saveOrden();
		} elseif($act === 'editar'){
			if($_POST['save']){
				if($tsAdmin->saveCat()) $tsCore->redirectTo('/admin/cats?save=true');
			} else {
				$smarty->assign("tsType", $_GET['t']);
				$smarty->assign("tsCat", $tsAdmin->getCat());
				// SOLO LAS CATEGORIAS TIENEN ICONOS
				$smarty->assign("tsIcons", $AdminHelper->getExtraIcons());
			}
		} elseif($act === 'nueva'){
			if($_POST['save']){
				if($tsAdmin->newCat()) $tsCore->redirectTo('/admin/cats?save=true');
			} else {
				$smarty->assign("tsType", $_GET['t']);
				$smarty->assign("tsCID", $_GET['cid']);
				$smarty->assign("tsIcons", $AdminHelper->getExtraIcons());
			}
		} elseif($act === 'change'){
			if($_POST['save']){
				if($tsAdmin->MoveCat()) $tsCore->redirectTo('/admin/cats?save=true');
			}
		} elseif($act === 'borrar'){
			if($_POST['save']){
				// BORRAR CATEGORIA
				if($_GET['t'] === 'cat'){
					$save = $tsAdmin->delCat();
					if($save == 1) $tsCore->redirectTo('/admin/cats?save=true');
					else $smarty->assign("tsError", $save); 
				// BORRAR SUBCATEGORIA
				} elseif($_GET['t'] === 'sub'){
					$save = $tsAdmin->delSubcat();
					if($save == 1) $tsCore->redirectTo('/admin/cats?save=true');
					else $smarty->assign("tsError", $save); 
				}
			}
			//
			$smarty->assign("tsType", $_GET['t']);
			$smarty->assign("tsCID", $_GET['cid']);
			$smarty->assign("tsSID", $_GET['sid']);
		}
	} elseif($action === 'rangos'){
			// PORTADA
			if(empty($act)) {
				$smarty->assign("tsRangos", $tsAdmin->getRangos());
			// LISTAR USUARIOS DEPENDIENDO EL RANGO
			} elseif($act === 'list'){
				$smarty->assign("tsMembers", $tsAdmin->getRangoUsers());
			// EDITAR RANGO
			} elseif($act === 'editar'){
				if(!empty($_POST['save'])) {
					if($tsAdmin->saveRango()) $tsCore->redirectTo('/admin/rangos?save=true');
				} else {
					$smarty->assign("tsRango", $tsAdmin->getRango());
					$smarty->assign("tsIcons", $AdminHelper->getExtraIcons('ran'));
               $smarty->assign("tsType", $_GET['t']);
				}
			// NUEVO RANGO
			} elseif($act === 'nuevo'){
				if(!empty($_POST['save'])){
					$save = $tsAdmin->newRango();
					if($save == 1) $tsCore->redirectTo('/admin/rangos?save=true');
					else {
						$smarty->assign("tsError", $save); 
						$smarty->assign("tsIcons", $AdminHelper->getExtraIcons('ran'));
					}
				} else {
					$smarty->assign("tsIcons", $AdminHelper->getExtraIcons('ran'));
                    $smarty->assign("tsType", $_GET['t']);
				}
			} elseif($act === 'borrar'){
				if(empty($_POST['save'])){
					$smarty->assign("tsRangos", $tsAdmin->getAllRangos());
				}else{
					if($tsAdmin->delRango()) $tsCore->redirectTo('/admin/rangos?save=true');
				}
			}
			// CAMBIAR RANGO PREDETERMINADO DEL REGISTRO
			elseif($act === 'setdefault'){
					if($tsAdmin->SetDefaultRango()) $tsCore->redirectTo('/admin/rangos?save=true');
			}
	} elseif($action === 'users'){
	   if(empty($act)) $smarty->assign("tsMembers", $tsAdmin->getUsuarios());
	   elseif($act === 'show'){
	      $do = intval($_GET['t']);
         $user_id = intval($_GET['uid']);
         // HACER
         if($do === 5 OR $do === 6) {
				require_once TS_EXTRA . "datos.php";
            $smarty->assign("tsPerfil", $tsAdmin->getUserPrivacidad());
				$smarty->assign("tsPrivacidad", $tsPrivacidad);
				$smarty->assign("tsContenido", $tsContenido);
         }
         switch($do){
				case 5:
        	     	if(!empty($_POST['save'])) {
        	         $update = $tsAdmin->setUserPrivacidad($user_id);
        	         if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$user_id.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
            break;
            case 6:
            	if(!empty($_POST['save'])) {
            		$delete = $tsAdmin->deleteContent($user_id);
            		if($delete === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$user_id.'&save=true');
            		else $smarty->assign("tsError", $delete);
            	}
            break;
            case 7:
        	   	if(!empty($_POST['save'])){
        	       	$update = $tsAdmin->setUserRango($user_id);
        	       	if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$user_id.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
               $smarty->assign("tsUserR", $tsAdmin->getUserRango($user_id));
            break;
				case 8:
        	      if(!empty($_POST['save'])){
        	         $update = $tsAdmin->setUserFirma($user_id);
        	         if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$user_id.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
					$smarty->assign("tsUserF", $tsAdmin->getUserData());
            break;
            default:
               if(!empty($_POST['save'])){
        	         $update = $tsAdmin->setUserData($user_id);
        	         if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$user_id.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
    	         $smarty->assign("tsUserD", $tsAdmin->getUserData());
            break;
         }
         // TIPO
         $smarty->assign("tsType", $_GET['t']);
         $smarty->assign("tsUserID", $user_id);
         $smarty->assign("tsUsername", $tsUser->getUserName($user_id));
	   }
	}

	// ACCION?
	$smarty->assign("tsAction", $action);
	$smarty->assign("tsAct", $act);
}


if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
	if(isset($_GET['save'])) $smarty->assign("tsSave", $_GET['save']);
	if(isset($_GET['borrar'])) $smarty->assign("tsDelete", $_GET['borrar']);
   require_once dirname(__DIR__, 2) . "/footer.php";
}