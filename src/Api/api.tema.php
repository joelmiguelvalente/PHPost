<?php

/**
 * @name src/Api/api.tema.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
	'tema-usar' => ['nivel' => 4, 'template' => '', 'ajax' => false],
	'tema-nuevo' => ['nivel' => 4, 'template' => '', 'ajax' => false],
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.tema.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASES
require_once TS_CLASS . "/c.themes.php";
$tsThemes = new tsThemes($tsCore, $tsUser);

// CODIGO
switch($action) {
	case 'tema-usar':
      echo $tsThemes->changeTema();
	break;
	case 'tema-nuevo':
      echo $tsThemes->newTema();
	break;
}
