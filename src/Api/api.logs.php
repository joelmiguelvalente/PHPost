<?php

/**
 * @name src/Api/api.logs.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'logs-borrar' => ['nivel' => 4, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config  = ACTIONS[$action];
$tsLevel = $config['nivel'];
$tsAjax  = (int)$config['ajax'];

// Verificar nivel admin
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
   echo json_encode(['status' => false, 'message' => 'Sin permisos']);
   die();
}

require_once TS_LOGGER . '/LogParser.php';

switch ($action) {
   case 'logs-borrar':
      $file     = trim($_POST['file']     ?? '');
      $datetime = trim($_POST['datetime'] ?? '');

      if (empty($file) || empty($datetime)) {
         echo json_encode(['status' => false, 'message' => 'Datos incompletos']);
         die();
      }

      $logsDir  = Config::app('paths.logs');
      $filepath = realpath($logsDir . '/' . basename($file));
   
      // Verificar que el archivo esté dentro del directorio de logs
      if (!$filepath || !str_starts_with($filepath, realpath($logsDir))) {
         echo json_encode(['status' => false, 'message' => 'Archivo inválido']);
         die();
      }

      $ok = LogParser::deleteEntry($filepath, $datetime);
      echo json_encode([
         'status'  => $ok,
         'message' => $ok ? 'Entrada eliminada' : 'No se pudo eliminar la entrada',
      ]);
   break;
}
