<?php

require_once dirname(__DIR__, 2) . '/inc/utils/Permissions.php';
require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');
$name = "permisos";

$rangos = DB::fechtAll('SELECT rango_id, r_allows FROM u_rangos');

foreach ($rangos as $rango) {
   $raw = $rango['r_allows'];
   // Detectar si ya es JSON
   if ($raw !== '' && $raw[0] === '{') {
	   echo json_encode(['success' => false, 'message' => $name . ' ya fue migrado']);
	   exit;
   }
   $old = @unserialize($raw);
   if (!is_array($old)) {
      echo "Rango {$rango['rango_id']} inválido, se setean defaults\n";
      $old = [];
   }
   $normalized = Permissions::DEFINITIONS;
   foreach ($normalized as $key => $default) {
      if (!array_key_exists($key, $old)) {
         continue;
      }
      if (is_bool($default)) {
         $normalized[$key] = ($old[$key] === 'on' || $old[$key] === true);
      } else {
         $normalized[$key] = (int) $old[$key];
      }
   }

   $json = json_encode($normalized, JSON_THROW_ON_ERROR);

	$upgrade = DB::update('u_rangos', 
		['r_allows' => $json], 
		'rango_id = :id', 
		['id' => $rango['rango_id']
	]);

   if(DB::insert('w_migrations', [ 'migration' => $name,  'executed_at' => $time ])) {
      echo json_encode([
         'success' => true, 
         'message' => $name . ' migrado'
      ]);
   }
}