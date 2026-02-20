<?php

require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');
$name = "configuracion_registro";

// Sentencia
$sentence = DB::exists("SHOW TABLES LIKE 'w_registro'");

if($sentence) {
   echo json_encode([
   	'success' => false, 
   	'message' => $name . ' ya fue migrado'
   ]);
   exit;
} else {
	$create = "CREATE TABLE IF NOT EXISTS `w_registro` (
	  `reg_id` INT PRIMARY KEY,
	  `c_reg_active` TINYINT NOT NULL DEFAULT 1,
	  `c_reg_activate` TINYINT NOT NULL DEFAULT 1,
	  `c_reg_rango` INT NOT NULL DEFAULT 3,
	  `c_met_welcome` TINYINT NOT NULL DEFAULT 0,
	  `c_message_welcome` varchar(500) NOT NULL DEFAULT 'Hola [usuario], [welcome] a [b][web][/b].',
	  `c_allow_edad` TINYINT NOT NULL DEFAULT 16,
	  `captcha_provider` ENUM('recaptcha', 'hcaptcha', 'recaptcha_enterprise') NOT NULL DEFAULT 'recaptcha',
	  `g_project_id` VARCHAR(255) NOT NULL DEFAULT '',
	  `g_credentials_json` VARCHAR(255) NOT NULL DEFAULT '/secure/google.json',
	  `public_key` VARCHAR(72) NOT NULL DEFAULT '',
	  `secret_key` VARCHAR(72) NOT NULL DEFAULT ''
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	DB::query($create);

	$collection = DB::fetch("SELECT c_reg_active, c_reg_activate, c_reg_rango, c_met_welcome, c_message_welcome, c_allow_edad, pkey AS public_key, skey AS secret_key FROM w_configuracion");

	if(!$collection) {
	   echo json_encode([
	   	'success' => false, 
	   	'message' => 'No se pudieron leer datos de w_configuracion'
	   ]);
	   exit;
	}

	$upgrade = DB::update('w_registro', $collection, 'reg_id = :id', ['id' => 1]);
	if(!db_exec([__FILE__, __LINE__], 'query', $update)) {
	   echo json_encode([
	   	'success' => false, 
	   	'message' => 'Error migrando datos a w_registro'
	   ]);
	   exit;
	}

	DB::query("ALTER TABLE w_configuracion 
		DROP COLUMN c_reg_active, 
		DROP COLUMN c_reg_activate, 
		DROP COLUMN c_reg_rango, 
		DROP COLUMN c_met_welcome, 
		DROP COLUMN c_message_welcome, 
		DROP COLUMN c_allow_edad, 
		DROP COLUMN pkey, 
		DROP COLUMN skey"
	);

	if(DB::insert('w_migrations', [ 'migration' => $name,  'executed_at' => $time ])) {
	   echo json_encode([
	      'success' => true, 
	      'message' => $name . ' migrado'
	   ]);
	}
}