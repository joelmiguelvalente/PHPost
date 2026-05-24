<?php

declare(strict_types=1);

/**
 * @package    src\Api
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
	'dbmanager-delete_backup' => ['nivel' => 4, 'template' => '', 'ajax' => false],
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.dbmanager.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	echo '0: '.$tsLevelMsg; 
	die();
}

// CLASE
require_once TS_CLASS . '/c.dbmanager.php';
$DBManager = new tsDBManager();

$flash = null;

// CODIGO
switch($action) {
   case 'dbmanager-delete_backup':
      echo $DBManager->deleteBackup(); 
   break;
}
