<?php /** @var array $data */ ?>

<div class="finalize-screen">
	<div class="finalize-icon" aria-hidden="true">
		<svg width="48" height="48" viewBox="0 0 48 48" fill="none">
			<circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
			<path d="M14 24l7 7 13-13" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</div>

	<h2>¡Instalación completada!</h2>
	<p>Tu comunidad <strong><?= e(Config::app('app.name')) ?></strong> ya está lista. Iniciá sesión con tus datos y comenzá a disfrutarla.</p>
	<p>Seguinos en <a href="<?= e(Config::app('app.server')) ?>" target="_blank" rel="noopener">nuestro Discord</a> para estar al tanto de las actualizaciones.</p>

	<div class="alert alert-warning" role="alert" style="margin: 1.5rem 0; text-align: left;">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M8 2L14 13H2L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
			<path d="M8 6v3M8 11v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
		</svg>
		Antes de continuar, ingresá a tu FTP y <strong>eliminá la carpeta <code><?= e($data['installDir'] ?? 'install') ?></code></strong> para proteger tu instalación.
	</div>
</div>

<div class="form-actions">
	<button type="submit" class="btn btn-primary">
		Ir a mi sitio
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
</div>
