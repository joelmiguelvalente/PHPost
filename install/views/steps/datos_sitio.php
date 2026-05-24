<?php
/** @var array $data */
$s = $data['site'] ?? [];
?>

<div class="form-grid">
	<div class="form-field">
		<label for="titulo">Nombre del sitio</label>
		<input type="text" id="titulo" name="titulo"
			value="<?= e($s['titulo'] ?? '') ?>"
			placeholder="<?= e(Config::app('app.name')) ?>" required>
	</div>

	<div class="form-field">
		<label for="slogan">Lema</label>
		<input type="text" id="slogan" name="slogan"
			value="<?= e($s['slogan'] ?? '') ?>"
			placeholder="<?= e(Config::app('app.slogan')) ?>" required>
	</div>

	<div class="form-field">
		<label for="site-url">URL del sitio</label>
		<input type="url" id="site-url" name="url"
			value="<?= e($s['url'] ?? '') ?>" required>
		<span class="field-hint">Sin barra final. Ej: <code>https://misitio.com</code></span>
	</div>

	<div class="form-field">
		<label for="site-email">Email de contacto</label>
		<input type="email" id="site-email" name="email"
			value="<?= e($s['email'] ?? '') ?>"
			placeholder="contacto@misitio.com" required>
	</div>
</div>

<div class="form-section-divider">
	<span>reCAPTCHA <em>(opcional)</em></span>
</div>

<p class="step-intro" style="margin-bottom: 1.25rem;">
	Habilitá la protección antispam. Obtené las claves en
	<a href="https://console.cloud.google.com/security/recaptcha" target="_blank" rel="noopener">console.cloud.google.com</a>.
</p>

<div class="form-grid">
	<div class="form-field">
		<label for="pkey">Clave pública</label>
		<input type="text" id="pkey" name="pkey"
			value="<?= e($s['pkey'] ?? '') ?>"
			placeholder="6LfXXXXXAAAAAAXXXXXXXXXXXXXXXXXXXXXXXXX">
	</div>

	<div class="form-field">
		<label for="skey">Clave secreta</label>
		<input type="text" id="skey" name="skey"
			value="<?= e($s['skey'] ?? '') ?>"
			placeholder="6LfXXXXXAAAAAAXXXXXXXXXXXXXXXXXXXXXXXXX">
	</div>
</div>

<div class="form-actions">
	<button type="submit" class="btn btn-primary">
		Guardar y continuar
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
</div>
