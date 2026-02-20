<?php

require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');
$name = "categorias";

// Sentencia
$sentence = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name LIMIT 1";

// Coleccion
$collection = [
	'table_name' =>'p_categorias',
	'column_name' => 'c_privada'
];

if (!DB::exists($sentence, $collection)) {
   // Ya existe, lo que significa que NO hemos migrado aún
   DB::query("ALTER TABLE p_categorias ADD c_privada TINYINT(1) NOT NULL DEFAULT 0");
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