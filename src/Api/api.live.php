<?php if ( ! defined('TS_HEADER')) exit('No se permite el acceso directo al script');
/**
 * Controlador AJAX
 *
 * @name    ajax.live.php
 * @author  PHPost Team
*/
/**********************************\

*	(VARIABLES POR DEFAULT)		*

\*********************************/

	// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCIÓN
	$files = array(
		'live-stream' => array('n' => 2, 'p' => 'stream'),
	);

/**********************************\

* (VARIABLES LOCALES ESTE ARCHIVO)	*

\*********************************/

	// REDEFINIR VARIABLES
	$tsPage = 'p.live.'.$files[$action]['p'];
	$tsLevel = $files[$action]['n'];
	$tsAjax = empty($files[$action]['p']) ? 1 : 0;

/**********************************\

*	(INSTRUCCIONES DE CODIGO)		*

\*********************************/
	
	// DEPENDE EL NIVEL
	$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
	if(!$tsLevelMsg) { 
		echo '0: '.$tsLevelMsg['mensaje']; 
		die();
	}
	// CODIGO
	switch($action){
		case 'live-stream':
			//<---
            // NOTIFICACIONES
            if($_POST['nots'] != 'OFF') {
                $tsStream = $tsMonitor->getNotificaciones(true);
                $smarty->assign("tsStream", $tsStream);
            }
            // MENSAJES
            if($_POST['mps'] != 'OFF') {
                $tsMensajes = $tsMP->getMensajes(1, true, 'live'); // Edit: 21/02/2014
                $smarty->assign("tsMensajes", $tsMensajes);   
            }
			//--->
		break;
        default:
            die('0: Este archivo no existe.');
        break;
	}