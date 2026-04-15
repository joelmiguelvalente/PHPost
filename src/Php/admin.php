<?php

/**
 * @name admin.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 * 
 * $ctx = Controller::page(_pagina_)->requireLevel(_nivel_);
 * $ctx->getLevel() obtinene el nivel para comprobar
 * $ctx->exportLegacy() sincroniza con el sistema
 */

$ctx = Controller::page('admin')->admin();
// sincronizamos
$ctx->exportLegacy();

$tsLevelMsg = $tsCore->setLevel($ctx->getLevel(), true);
if (is_array($tsLevelMsg)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", $tsLevelMsg);
   // sincroniza nuevamente
   $ctx->exportLegacy();
}

if(!$tsUser->is_member) {
	$tsPage = 'login';
}

if($ctx->continue()) {

	// ACTION
	$action = trim($_GET['action'] ?? '');
	$act = trim($_GET['act'] ?? '');

	// CLASE POSTS
	require_once TS_CLASS . "/c.admin.php";
	$tsAdmin = new tsAdmin($tsCore, $tsUser);
	$AdminHelper = new AdminHelper; // Viene dentro del c.admin.php
	
	# CENTRO DE ADMINISTRACION
	if(empty($action)) {
		$smarty->assign("tsAdmins", $tsAdmin->getAdmins());
      $smarty->assign("tsInstalled", $tsAdmin->getInst());
		$smarty->assign("tsVersion", $tsAdmin->getVersions());
      if($act === 'limpiar-cache') {
      	if($tsAdmin->clearCache()) $tsCore->redirectAdmin($action);
      }

   # SOPORTE Y CREDITOS
	} elseif($action === 'creditos') {
		$smarty->assign("tsVersion", $tsAdmin->getVersions());

	# CONFIGURACION
	} elseif($action === 'configs') {
		// GUARDAR CONFIGURACION
		if(!empty($_POST['titulo'])) {
			if($tsAdmin->saveConfig()) $tsCore->redirectAdmin($action);
		}

	# CONFIGURACION DEL REGISTRO
	} elseif($action === 'registro') {
		$smarty->assign("tsRegistro", $tsCore->reCaptchaConfig());
		// GUARDAR CONFIGURACION
		if(!empty($_POST['public_key'])) {
			if($tsAdmin->saveConfig('w_registro', 'reg_id')) $tsCore->redirectAdmin($action);
		}

	} elseif($action === 'phpmailer') {
		require_once TS_CLASS . '/c.mailer.php';
		$Mailer = new Mailer();

		$smarty->assign("tsPHPMailer", $Mailer->mailerConfig());
		if(!empty($_POST['SMTP_HOST'])) {
			if($Mailer->saveMailerConfig()) $tsCore->redirectAdmin($action);
		}

	} elseif($action === 'dbmanager') {
		require_once TS_CLASS . '/c.dbmanager.php';
		$DBManager = new tsDBManager;
		$tablesInfo  = $DBManager->getTablesInfo();
		$smarty->assign("tsTablesInfo", $tablesInfo);

		$flash = null;
		$tables = array_map('strval', (array)($_POST['tables'] ?? []));
		
		if(empty($act)) {
			$smarty->assign("tsTablesTotal", array_sum(array_map('count', $tablesInfo)) );
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
				$result = $DBManager->createBackup($tables);
		      $flash  = $result['ok'] ? ['type' => 'success', 'msg' => "Backup creado: <strong>{$result['filename']}</strong> ({$result['size']} KB)"] : ['type' => 'error',   'msg' => $result['message']];
		      $backupsList = $DBManager->listBackups();
		      $smarty->assign("tsMessage", $flash);
		      $smarty->assign("tsSave", true);
		   }
		} elseif($act === 'mantenimiento') {
			$smarty->assign("checkResults", $DBManager->checkTables($tables));
			if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] !== 'check') {
				if($_POST['action'] === 'optimize') {
			      $results = $DBManager->optimizeTables($tables);
			      $ok = array_filter($results, fn($v) => $v !== 'error');
			      $flash = ['type' => 'success', 'msg' => count($ok) . ' tabla(s) optimizada(s).'];
			   } elseif ($_POST['action'] === 'repair') {
			      $results = $DBManager->repairTables($tables);
			      $ok = array_filter($results, fn($v) => $v !== 'error');
			      $flash = ['type' => 'success', 'msg' => count($ok) . ' tabla(s) reparada(s).'];
			   }
		      $smarty->assign("tsMessage", $flash);
		      $smarty->assign("tsSave", true);
			}
		} elseif($act === 'truncar') {
			$smarty->assign('tsTruncaTables', tsDBManager::TRUNCATABLE_TABLES);
		} elseif($act === 'historial') {
			$smarty->assign('backupsList', $DBManager->listBackups());
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
	      	$filename = basename($_POST['filename'] ?? '');
	      	if(!preg_match('/^backup_[\w\-]+\.sql$/', $filename)) {
	      	   header("Location: {$tsCore->settings['url']}/admin/dbmanager?act=historial");
	      	   exit;
	      	}
	      	$path = TS_BACKUPS . '/' . $filename;
	      	if (!file_exists($path)) {
	      	   $smarty->assign("tsMessage", ['type' => 'danger', 'msg' => 'El backup no existe']);
	      	   return;
	      	} else {
	      	   header('Content-Type: application/octet-stream');
	      	   header('Content-Disposition: attachment; filename="' . $filename . '"');
	      	   header('Content-Length: ' . filesize($path));
	      	   readfile($path);
	      	   exit;
	      	}
	    	}
		}

	# TEMAS
	} elseif($action === 'imageprovider') {
   	require_once TS_CLASS . "/c.imageprovider.php";
   	$ImageProvider = new ImageProvider;
   	if(empty($act)) {
			$smarty->assign("tsProviders", $ImageProvider->getAll());
		} elseif($act === 'nuevo' && !empty($_POST['provider_name'])) {
			if($ImageProvider->newProvider()) $tsCore->redirectAdmin($action);
		} elseif($act === 'editar') {
			$smarty->assign("tsProvider", $ImageProvider->getProvider());
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
				if($ImageProvider->editProvider()) $tsCore->redirectAdmin($action);
			}
		} elseif($act === 'activar') {
			$id = (int)($_GET['id'] ?? 0);
			$api = trim($_GET['api'] ?? '');
			if($ImageProvider->activate($id, $api)) $tsCore->redirectAdmin($action);
		} elseif($act === 'borrar') {
			$smarty->assign("tsProvider", $ImageProvider->getProvider());
			if($ImageProvider->delProvider()) $tsCore->redirectAdmin($action);
		}

	# TEMAS
	} elseif($action === 'temas') {
   	require_once TS_CLASS . "/c.themes.php";
		$smarty->assign("tsTemas", (new tsThemes($tsCore))->getTemas());

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
	} elseif($action === 'ads') {
		if(!empty($_POST['ads_300']) || !empty($_POST['ads_search'])){
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

   # AFILIADOS
	} elseif($action === 'afs') {
      // CLASS
      require_once TS_CLASS . "/c.afiliado.php";
      $tsAfiliado = new tsAfiliado($tsCore, $tsUser);
       // QUE HACER
	   if(empty($act)) {
        // AFILIADOS
        $smarty->assign("tsAfiliados", $tsAfiliado->getAfiliados('admin'));
	   } elseif($act === 'editar') {
	   	if(isset($_POST['a_titulo'])) {
	         if($tsAfiliado->editarAfiliado()) {
	         	$tsCore->redirectAdmin($action, 'save', "&act=editar&aid={$_GET['aid']}");
	         }
         }
        	$smarty->assign("tsAfiliado", $tsAfiliado->getAfiliado('admin'));
      }

	# ESTADÍSTICAS
	} elseif($action === 'stats') {
		require_once TS_CLASS . "/c.estadisticas.php";
		$tsStats = new tsEstadisticas;
		$smarty->assign("tsAdminStats", $tsStats->obtenerEstadisticas());
	
	# LISTA NEGRA
   } elseif($action === 'blacklist') {
		require_once TS_CLASS . "/c.bloqueos.php";
		$tsBloqueos = new tsBloqueos($tsCore, $tsUser);

		if(empty($act)) {
			$smarty->assign("tsBlackList", $tsBloqueos->getBlackList());
		} elseif($act === 'nuevo') {
			if(isset($_POST['reason'])) {
				$data = [
					'value' => trim($_POST['value'] ?? ''), 
					'type' => trim($_POST['type'] ?? ''),
					'reason' => trim($_POST['reason'] ?? '')
				];
				$smarty->assign("tsBloqueo", $data);
			}   
      } else {
			$smarty->assign("tsBloqueo", $tsBloqueos->getBlock());
		}
      if(in_array($act, ['editar', 'nuevo'], true)) {
			if(isset($_POST['value'])) {
				$status = ($act === 'editar') ? $tsBloqueos->saveBlock() : $tsBloqueos->newBlock();
				if($status) $tsCore->redirectAdmin($action);
				else $smarty->assign("tsError", $status); 
			}
      }

  	# CENSURAS
   } elseif($action === 'badwords') {
		require_once TS_CLASS . "/c.censura.php";
		$tsCensura = new tsCensura($tsCore, $tsUser);
   	
		if(empty($act)) {
			$smarty->assign("tsBadWords", $tsCensura->getBadWords());
		} elseif($act === 'editar') {
         $smarty->assign("tsBadWord", $tsCensura->getBadWord());
		} 

      if(in_array($act, ['editar', 'nuevo'], true)) {
			if(isset($_POST['word'])) {
				$status = ($act === 'editar') ? $tsCensura->saveBadWord() : $tsCensura->newBadWord();
				if($status) $tsCore->redirectAdmin($action);
				else $smarty->assign("tsError", $status);
				$smarty->assign("tsBadWord", [
					'word' => $_POST['word'], 
					'swop' => $_POST['swop'], 
					'method' => $_POST['method'], 
					'type' => $_POST['type']
				]);
			}
      }

	# POSTS
	} elseif($action === 'posts') {
		if(!$act) {
			$smarty->assign("tsAdminPosts", $tsAdmin->getAdmin('posts'));
		}

	# FOTOS
	} elseif($action === 'fotos') {
		if(!$act) {
			$smarty->assign("tsAdminFotos", $tsAdmin->getAdmin('fotos'));
		}

	# CAMBIOS DE NOMBRE DE USUARIO
	} elseif($action === 'nicks'){
		$smarty->assign("tsAdminNicks", $tsAdmin->getChangeNicks($act));
   
   # CATEGORIAS
	} elseif($action === 'cats'){
		if($act === 'editar' || $act === 'nueva'){
			if(isset($_POST['c_nombre'])) {
				$status = $act === 'editar' ? $tsAdmin->saveCat() : $tsAdmin->newCat();
				if($status) $tsCore->redirectAdmin($action);
			} else {
				if($act === 'editar') $smarty->assign("tsCat", $tsAdmin->getCat());
				if($act === 'nueva') $smarty->assign("tsCID", (int)($_GET['cid'] ?? 0));
				$smarty->assign("tsIcons", $AdminHelper->getExtraIcons());
			}
		} elseif($act === 'change'){
			if(isset($_POST['save'])) {
				if($tsAdmin->MoveCat()) $tsCore->redirectAdmin($action);
			}
		} elseif($act === 'borrar'){
			if(isset($_POST['save'])) {
				if($tsAdmin->delCat()) $tsCore->redirectAdmin($action);
				else $smarty->assign("tsError", $save);
			}
			//
			$smarty->assign("tsType", $_GET['t']);
			$smarty->assign("tsCID", $_GET['cid']);
			$smarty->assign("tsSID", $_GET['sid']);
		}
   
	# SESIONES
	} elseif($action === 'sesiones'){
		if(!$act) {
			$smarty->assign("tsAdminSessions", $tsAdmin->GetSessions());
		}

	# RANGOS
	} elseif($action === 'rangos') {
		require_once TS_CLASS . '/c.rangos.php';
		$tsRangos = new tsRangos($tsCore, $tsUser);
		$smarty->assign("tsPredeterminado", $tsCore->reCaptchaConfig('c_reg_rango'));
		// PORTADA
		if(empty($act)) {
			$smarty->assign("tsRangos", $tsRangos->getRangos());
		// LISTAR USUARIOS DEPENDIENDO EL RANGO
		} elseif($act === 'list') {
			$smarty->assign("tsMembers", $tsRangos->getRangoUsers());
		// EDITAR RANGO | NUEVO RANGO
		} elseif($act === 'editar' || $act === 'nuevo') {
			$isEdit = ($act === 'editar');
			if(!empty($_POST['save'])) {
				$save = $isEdit ? $tsRangos->saveRango() : $tsRangos->newRango();
				if($save) $tsCore->redirectAdmin($action);
				if(!$save && !$isEdit) {
					$smarty->assign("tsError", $save); 
					$smarty->assign("tsIcons", $AdminHelper->getExtraIcons('ran'));
				}
			} else {
				if($isEdit) $smarty->assign("tsRango", $tsRangos->getRango());
				$smarty->assign("tsIcons", $AdminHelper->getExtraIcons('ran'));
            $smarty->assign("tsType", trim($_GET['type'] ?? 'special'));
			}
		} elseif($act === 'borrar'){
			if(empty($_POST['save'])) {
				$smarty->assign("tsRangos", $tsRangos->getAllRangos());
			} else {
				if($tsRangos->delRango()) $tsCore->redirectAdmin($action);
			}
		// CAMBIAR RANGO PREDETERMINADO DEL REGISTRO
		} elseif($act === 'setdefault'){
			if($tsRangos->SetDefaultRango()) $tsCore->redirectAdmin($action);
		}

	} elseif($action === 'users') {
		require_once TS_CLASS . '/c.useradmin.php';
		$tsUserAdmin = new tsUserAdmin($tsCore, $tsUser, $tsAdmin);
	   if(empty($act)) $smarty->assign("tsMembers", $tsUserAdmin->getUsuarios());
	   elseif($act === 'show') {
			$userID = $tsUserAdmin->getUserID();
	      $type = (int)($_GET['type'] ?? 0);
         // HACER
         if($type === 5 OR $type === 6) {
				require_once TS_EXTRAS . "/datos.php";
            $smarty->assign("tsPerfil", $tsUserAdmin->getUserPrivacidad());
				$smarty->assign("tsPrivacidad", $tsPrivacidad);
				$smarty->assign("tsContenido", $tsContenido);
         }
         switch($type){
				case 5:
        	     	if(!empty($_POST['save'])) {
        	         $update = $tsUserAdmin->setUserPrivacidad($userID);
        	         if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$userID.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
            break;
            case 6:
            	if(!empty($_POST['save'])) {
            		$delete = $tsUserAdmin->deleteContent($userID);
            		if($delete === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$userID.'&save=true');
            		else $smarty->assign("tsError", $delete);
            	}
            break;
            case 7: // Rango
        	   	if(!empty($_POST['save'])) {
        	       	$update = $tsAdmin->setUserRango($userID);
        	       	if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$userID.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
               $smarty->assign("tsUserR", $tsUserAdmin->getUserRango($userID));
            break;
				case 8: // Firma
        	      if(!empty($_POST['save'])){
        	         $update = $tsUserAdmin->setUserFirma($userID);
        	         if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$userID.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
					$smarty->assign("tsUserF", $tsUserAdmin->getUserData());
            break;
            default:
               if(!empty($_POST['user_name'])) {
        	         $update = $tsUserAdmin->setUserData();
        	         if($update === 'OK') $tsCore->redirectTo('/admin/users?act=show&uid='.$userID.'&save=true');
                  else $smarty->assign("tsError", $update);
               }
    	         $smarty->assign("tsUserD", $tsUserAdmin->getUserData());
            break;
         }
         // TIPO
         $smarty->assign("tsType", $type);
         $smarty->assign("tsUserID", $userID);
         $smarty->assign("tsUsername", $tsUser->getUserName($userID));
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
   require_once TS_ROOT . "/footer.php";
}
