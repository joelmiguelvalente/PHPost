<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Migration
 * @author     Miguel92
*/

require_once __DIR__ . '/app.php';

// Manejar la solicitud de migración si viene por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_migration') {
	// Incluir el archivo de migración
	include __DIR__ . '/migrar.php';
	exit; // Terminar la ejecución para evitar mostrar el HTML
}

# Tablas migradas
$settings = DB::fetch("SELECT url FROM w_configuracion");

// Obtener migraciones de la base de datos
$migraciones_db = DB::fetchAll("SELECT id, migration, executed_at FROM w_migrations");

// Obtener archivos de migración de la carpeta
$path = __DIR__ . '/data/';
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

// Combinar información para mostrar estado
$migraciones = [];
$migraciones_ejecutadas = [];

// Extraer nombres de migraciones ya ejecutadas
foreach ($migraciones_db as $migracion) {
	$migraciones_ejecutadas[$migracion['migration']] = $migracion;
}

// Verificar cada archivo de migración
foreach ($archivos_migracion as $archivo) {
	if (isset($migraciones_ejecutadas[$archivo])) {
		// Ya ejecutada
		$migraciones[] = [
			'migration' => $archivo,
			'executed_at' => $migraciones_ejecutadas[$archivo]['executed_at'],
			'estado' => 'ejecutada',
			'id' => $migraciones_ejecutadas[$archivo]['id']
		];
	} else {
		// Pendiente
		$migraciones[] = [
			'migration' => $archivo,
			'executed_at' => null,
			'estado' => 'pendiente',
			'id' => null
		];
	}
}

// Añadir migraciones que están en la base de datos pero no en la carpeta (opcional)
foreach ($migraciones_ejecutadas as $nombre => $datos) {
	if (!in_array($nombre, $archivos_migracion)) {
		$migraciones[] = [
			'migration' => $nombre,
			'executed_at' => $datos['executed_at'],
			'estado' => 'ejecutada (fuera de carpeta)',
			'id' => $datos['id']
		];
	}
}

// Ordenar por estado y nombre
usort($migraciones, function($a, $b) {
	// Primero ejecutadas, luego pendientes
	if ($a['estado'] !== $b['estado']) {
		return ($a['estado'] === 'ejecutada') ? -1 : 1;
	}
	// Si ambos tienen executed_at, ordenar por fecha (más reciente primero)
	if ($a['executed_at'] && $b['executed_at']) {
		$dateA = is_numeric($a['executed_at']) ? $a['executed_at'] : strtotime($a['executed_at']);
		$dateB = is_numeric($b['executed_at']) ? $b['executed_at'] : strtotime($b['executed_at']);
		return $dateB <=> $dateA; // Mayor fecha (más reciente) primero
	}
	// Si uno no tiene fecha, va después
	if ($a['executed_at']) return -1;
	if ($b['executed_at']) return 1;
	return strcmp($a['migration'], $b['migration']);
});

?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sistema de migración</title>
<link rel="shortcut icon" href="<?= $urlBase ?>/assets/images/phpost/main-16.png" type="image/png" />
<link rel="preload" href="<?= $urlBase ?>/assets/fonts/Inter.woff2" as="font" type="font/woff2" crossorigin="anonymous">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
	.material-symbols-outlined {
		font-variation-settings: 'FILL' 0, 'wght' 400;
	}
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
	<div class="container mx-auto px-4 py-8 max-w-6xl">
		<main>
			<header class="mb-10 text-center flex justify-between items-center">
				<div class="flex justify-start">
					<img src="<?= $urlBase ?>/assets/images/phpost/main-128.png" alt="Logo PHPost" class="h-28 w-28 object-contain rounded">
				</div>
				<div class="text-right">
					<h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">PHPost: Migrations!</h1>
					<p class="mt-2 text-gray-600">Gestión completa de tus migraciones de base de datos</p>
				</div>
			</header>

			<section class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-200">
				<div class="flex items-center justify-between mb-6">
					<h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
						<span class="material-symbols-outlined text-blue-500">inventory</span>
						Estado de Migraciones
					</h2>
					<button 
						onclick="ejecutarMigracion()" 
						class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium py-2 px-5 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center gap-2"
					>
						<span class="material-symbols-outlined text-sm">sync_alt</span>
						Ejecutar Próxima Migración
					</button>
				</div>
				
				<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
					<div class="bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-xl border border-blue-200">
						<div class="text-blue-800 font-bold text-3xl mb-1"><?= count($migraciones) ?></div>
						<div class="text-blue-600 font-medium">Total</div>
					</div>
					<div class="bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-xl border border-green-200">
						<div class="text-green-800 font-bold text-3xl mb-1">
							<?= count(array_filter($migraciones, fn($m) => $m['estado'] === 'ejecutada' || $m['estado'] === 'ejecutada (fuera de carpeta)')) ?>
						</div>
						<div class="text-green-600 font-medium">Ejecutadas</div>
					</div>
					<div class="bg-gradient-to-br from-orange-50 to-orange-100 p-5 rounded-xl border border-orange-200">
						<div class="text-orange-800 font-bold text-3xl mb-1">
							<?= count(array_filter($migraciones, fn($m) => $m['estado'] === 'pendiente')) ?>
						</div>
						<div class="text-orange-600 font-medium">Pendientes</div>
					</div>
				</div>

				<div class="overflow-x-auto rounded-lg border border-gray-200">
					<table class="min-w-full divide-y divide-gray-200">
						<thead class="bg-gray-50">
							<tr>
								<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
								<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Ejecución</th>
								<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
								<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
							</tr>
						</thead>
						<tbody class="bg-white divide-y divide-gray-200">
							<?php foreach ($migraciones as $migracion): ?>
							<tr class="hover:bg-gray-50 transition-colors duration-200">
								<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 max-w-xs truncate" title="<?= htmlspecialchars($migracion['migration']) ?>">
									<?= htmlspecialchars($migracion['migration']) ?>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
									<?= $migracion['executed_at'] ? date('d/m/Y H:i:s', is_numeric($migracion['executed_at']) ? $migracion['executed_at'] : strtotime($migracion['executed_at'])) : 'N/A' ?>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<?php if ($migracion['estado'] === 'ejecutada'): ?>
										<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
											Ejecutada
										</span>
									<?php elseif ($migracion['estado'] === 'pendiente'): ?>
										<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
											Pendiente
										</span>
									<?php else: ?>
										<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
											<?= htmlspecialchars($migracion['estado']) ?>
										</span>
									<?php endif; ?>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm">
									<?php if ($migracion['estado'] === 'pendiente'): ?>
										<button 
											onclick="ejecutarMigracionIndividual('<?= addslashes(htmlspecialchars($migracion['migration'])) ?>')" 
											class="text-green-600 hover:text-green-900 font-medium"
										>
											Ejecutar
										</button>
									<?php else: ?>
										-
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</section>

			<footer class="text-center text-gray-500 text-sm mt-12 pt-6 border-t border-gray-200">
				<p>Sistema de Migraciones PHPost v1.0 • Total seguridad y control</p>
			</footer>
		</main>
	</div>

	<script>
		function ejecutarMigracion() {
			if (confirm('¿Estás seguro de que deseas ejecutar la próxima migración pendiente?')) {
				fetch(window.location.href, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: 'action=run_migration'
				})
				.then(response => response.json())
				.then(data => {
					const { success, message } = data;
					if (success) {
						alert('Migración ejecutada correctamente');
						location.reload(); // Recargar la página para ver los cambios
					} else {
						alert('Error: ' + message);
					}
				})
				.catch(error => {
					console.error('Error:', error);
					alert('Hubo un error al ejecutar la migración');
				});
			}
		}
		
		async function ejecutarMigracionIndividual(nombre) {
			if (confirm(`¿Estás seguro de que deseas ejecutar la migración "${nombre}"?`)) {
				fetch(window.location.href, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: `action=run_migration&migration=${encodeURIComponent(nombre)}`
				})
				.then(response => response.json())
				.then(data => {
					const { success, message } = data;
					if (success) {
						alert('Migración ejecutada correctamente');
						location.reload(); // Recargar la página para ver los cambios
					} else {
						alert('Error: ' + message);
					}
			
				}).catch(error => {
					console.error('Error:', error);
					alert('Hubo un error al ejecutar la migración');
				});
			}
		}
	</script>
</body>
</html>
