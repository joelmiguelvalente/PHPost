<?php

require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');
$name = "denuncias";

$schemaCheck = "SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'w_denuncias' AND COLUMN_NAME = 'd_type'";

$result = DB::fetch($schemaCheck);
$isEnum = false;

if ($result && $row = $result) {
   $isEnum = ($row['DATA_TYPE'] === 'enum');
}

if ($isEnum) {
   echo json_encode([
      'success' => false, 
      'message' => $name . ' ya fue migrado (columna ya es ENUM)'
   ]);
   exit;
}

$sentencia = "ALTER TABLE `w_denuncias` CHANGE `d_type` `d_type` ENUM('none','post','mensaje','usuario','foto') NOT NULL DEFAULT 'none'";
if (DB::query($sentencia)) {
   // Ya existe, lo que significa que NO hemos migrado aún
   if(DB::insert('w_migrations', [ 'migration' => $name,  'executed_at' => $time ])) {
      echo json_encode([
         'success' => true, 
         'message' => $name . ' migrado'
      ]);
   }
   exit;
} else {
   // Ya migrado
   echo json_encode(['success' => false, 'message' => $name . ' ya fue migrado']);
   exit;
}
