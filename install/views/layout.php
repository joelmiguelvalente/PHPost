<?php

declare(strict_types=1);

/**
 * Layout principal del instalador.
 * Variables disponibles:
 *   $step    string         — slug del paso actual
 *   $data    array          — datos del handler
 *   $error   string|null    — mensaje de error
 *   $baseUrl string         — URL base del instalador
 *   $siteUrl string         — URL raíz del sitio
 */

// Helper de escape para las vistas
function e(mixed $val): string {
	return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
}

$appName    = Config::app('app.name');
$appVersion = Config::app('app.version');

$stepIndex  = StepRegistry::FLOW;
$currentPos = (int) array_search($step, $stepIndex, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalación · <?= e($appName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e($baseUrl) ?>/estilo.css?v=<?= filemtime(__DIR__ . '/../estilo.css') ?>">
</head>
<body>

<div class="installer-shell">

	<!-- Barra lateral de progreso -->
	<aside class="installer-sidebar">
		<div class="sidebar-brand">
			<a href="<?= e(Config::app('app.server')) ?>" target="_blank" rel="noopener">
				<img src="<?= e($baseUrl) ?>/logo.png" alt="PHPost" class="sidebar-logo">
			</a>
			<span class="sidebar-version">v<?= e($appVersion) ?></span>
		</div>

		<nav class="step-nav" aria-label="Pasos de instalación">
			<?php foreach (StepRegistry::FLOW as $i => $s): ?>
				<?php
				$isPast    = $i < $currentPos;
				$isCurrent = $i === $currentPos;
				$cls = $isPast ? 'done' : ($isCurrent ? 'active' : 'pending');
				?>
				<div class="step-nav-item <?= $cls ?>">
					<span class="step-nav-bullet">
						<?php if ($isPast): ?>
							<svg width="12" height="12" viewBox="0 0 12 12" fill="none">
								<path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						<?php else: ?>
							<?= $i + 1 ?>
						<?php endif; ?>
					</span>
					<span class="step-nav-label"><?= e(StepRegistry::LABELS[$s]) ?></span>
				</div>
			<?php endforeach; ?>
		</nav>

		<div class="sidebar-footer">
			<p>Powered by <a href="<?= e(Config::app('app.server')) ?>" target="_blank" rel="noopener">PHPost</a></p>
		</div>
	</aside>

	<!-- Contenido principal -->
	<main class="installer-main">
		<header class="installer-header">
			<h1 class="installer-title"><?= e(StepRegistry::LABELS[$step] ?? $step) ?></h1>
			<span class="installer-step-count"><?= $currentPos + 1 ?> / <?= count(StepRegistry::FLOW) ?></span>
		</header>

		<?php if ($error !== null): ?>
			<div class="alert alert-error" role="alert">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
					<circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
					<path d="M8 4.5v4M8 10.5v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
				</svg>
				<?= $error ?>
			</div>
		<?php endif; ?>

		<div class="installer-content">
			<form method="POST" id="install-form" novalidate>
				<?php require __DIR__ . '/steps/' . $step . '.php' ?>
			</form>
		</div>
	</main>

</div>

</body>
</html>
