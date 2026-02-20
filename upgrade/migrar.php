<?php
require_once __DIR__ . '/app.php'; // La conexion a la base de datos

// Obtener el nombre de migración específico si se proporciona
$migration_solicitada = $_POST['migration'] ?? null;

// Obtener archivos de migración de la carpeta
$path = __DIR__ . '/migration/';
$archivos_migracion = [];

if (is_dir($path)) {
	$iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
	foreach ($iterator as $item) {
		if ($item->isFile()) {
			$base = pathinfo($item->getPathname(), PATHINFO_FILENAME);
			$archivos_migracion[] = $base;
		}
	}
}

// Obtener migraciones ya ejecutadas
$migraciones_ejecutadas_db = DB::fetchAll("SELECT migration FROM w_migrations");
$migraciones_ejecutadas = array_column($migraciones_ejecutadas_db, 'migration');

// Buscar primera migración pendiente o la específica solicitada
$migracion_a_ejecutar = null;

foreach ($archivos_migracion as $archivo) {
	if ($migration_solicitada && $archivo === $migration_solicitada) {
		// Si se solicitó una migración específica
		if (!in_array($archivo, $migraciones_ejecutadas)) {
			$migracion_a_ejecutar = $archivo;
			break;
		}
	} else if (!$migration_solicitada && !in_array($archivo, $migraciones_ejecutadas)) {
		// Si no se solicitó una específica, tomar la primera pendiente
		$migracion_a_ejecutar = $archivo;
		break;
	}
}

if ($migracion_a_ejecutar) {
	// Ejecutar la migración específica
	$ruta_archivo = __DIR__ . '/migration/' . $migracion_a_ejecutar . '.php';
	
	if (file_exists($ruta_archivo)) {
		// Incluir el archivo de migración específico
		include $ruta_archivo;
		
		// Registrar en la tabla de migraciones
		if(DB::insert('w_migrations', [
			'migration' => $migracion_a_ejecutar, 
			'executed_at' => date('Y-m-d H:i:s', $time)
		])) {
			echo json_encode(['success' => true, 'message' => "Migración '{$migracion_a_ejecutar}' ejecutada"]);
		} else {
			echo json_encode(['success' => false, 'message' => "Error al registrar migración '{$migracion_a_ejecutar}'"]);
		}
	} else {
		echo json_encode(['success' => false, 'message' => "Archivo de migración no encontrado: {$ruta_archivo}"]);
	}
} else {
	if ($migration_solicitada) {
		echo json_encode(['success' => false, 'message' => "La migración '{$migration_solicitada}' no está disponible o ya fue ejecutada"]);
	} else {
		echo json_encode(['success' => false, 'message' => 'No hay migraciones pendientes para ejecutar']);
	}
}
?>