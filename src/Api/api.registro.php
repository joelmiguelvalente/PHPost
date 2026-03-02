<?php

/**
 * @name api.registro.php
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
$tsPage  = sprintf('p.registro.%s', $config['template']);
	
// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}
	
// CLASE
require_once TS_CLASS . '/c.registro.php';
$tsRegistro = new tsRegistro($tsCore, $tsUser);
	
// CODIGO
switch($action) {
	case 'registro-form':
		if((int)$tsCore->settings['c_reg_active'] === 0) {
			$tsAjax = true;
			echo "0: El registro de nuevas cuentas en <strong>{$tsCore->settings['titulo']}</strong> est&aacute; desactivado.";
		} else {
			$tsPaises = require_once TS_EXTRA . "/Paises.php";
			$tsMeses = require_once TS_EXTRA . "/Meses.php";
			
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
		$tsEstados = require TS_EXTRA . "/geodata.php";
		$pais = trim($_GET['pais_code'] ?? '');
		if ($pais === '') {
		   echo '0: El campo <strong>pais_code</strong> es requerido para esta operación';
		   return;
		}
		if (!isset($tsEstados[$pais]) || !is_array($tsEstados[$pais])) {
		   echo '0: Código de país incorrecto.';
		   return;
		}
		$paisOption = [];
		foreach ($tsEstados[$pais] as $key => $country) {
		   $paisOption[] = '<option value="' . $key . '">' . $country . '</option>';
		}
		echo '1: ' . implode("\n", $paisOption);
	break;
	
	case 'registro-nuevo':
		echo $tsRegistro->registerUser();
	break;
}