<?php

require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');
$name = "mensaje_off";

// Ya existe, lo que significa que NO hemos migrado aún
DB::query("ALTER TABLE u_perfil CHANGE p_mensajes_privados p_mensajes_privados ENUM('everyone','registered','followers','following','friends_mutual','friends_any','nobody','off') NULL DEFAULT 'everyone'");
if(DB::insert('w_migrations', [ 'migration' => $name,  'executed_at' => $time ])) {
   echo json_encode([
      'success' => true, 
      'message' => $name . ' migrado'
   ]);
}
exit;