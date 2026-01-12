<?php

/**
 * @name ajax.registro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'registro-form'	 		=> ['nivel' => 1, 'template' => 'form', 'ajax' => true],
   'registro-check-nick' 	=> ['nivel' => 1, 'template' => '', 'ajax' => false],
   'registro-check-email'	=> ['nivel' => 1, 'template' => '', 'ajax' => false],
   'registro-geo' 			=> ['nivel' => 0, 'template' => '', 'ajax' => false],
   'registro-nuevo' 			=> ['nivel' => 0, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('php_files/p.login.%s', $config['template']);
	
// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}
	
// CLASE
require_once dirname(__DIR__, 2) . '/class/c.registro.php';
$tsRegistro = new tsRegistro;
	
// CODIGO
switch($action) {
	case 'registro-form':
		if((int)$tsCore->settings['c_reg_active'] === 0) {
			$tsAjax = true;
			echo "0: El registro de nuevas cuentas en <strong>{$tsCore->settings['titulo']}</strong> est&aacute; desactivado.";
		} else {
			$tsPaises = require_once dirname(__DIR__, 1) . "/extras/Paises.php";
			$tsMeses = require_once dirname(__DIR__, 1) . "/extras/Meses.php";
			
			// SOLO MENORES DE 100 AÑOS xD Y MAYORES DE...
			$minAge = (int)$tsCore->settings['c_allow_edad'];
			$maxAge = 100;
			$currentYear = date("Y");

			$minBirthYear = $currentYear - $maxAge;
			$maxBirthYear = $currentYear - $minAge;
			
			$smarty->assign("tsMax", (int)$maxBirthYear);
			$smarty->assign("tsEndY", (int)$minBirthYear);
			$smarty->assign("tsPaises", $tsPaises);
			$smarty->assign("tsMeses", $tsMeses);	
		}
	break;
		case 'registro-check-nick':	
		case 'registro-check-email':	
			echo $tsRegistro->checkUserEmail();
		break;
		
		case 'registro-geo':
		
			//<--
			
			include("../extras/geodata.php");
			
			$pais = htmlspecialchars($_GET['pais_code']);
			
			//
			
			if($pais) $html = '1: ';
			
			else $html = '0: El campo <b>pais_code</b> es requerido para esta operacion';
			
			foreach($estados[$pais] as $key => $estado){
			
				$html .= '<option value="'.($key+1).'">'.$estado.'</option>'."\n";
				
			}
			
			//
			
			if(strlen($html) > 3) echo $html;
			
			else echo '0: Código de pais incorrecto.';
			
			//-->
			
		break;
		
		case 'registro-nuevo':
		
			//<--
			
                $result = $tsRegistro->registerUser();
				
				echo $result;
				
			//-->
			
		break;
		
	}