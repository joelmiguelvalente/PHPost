<?php
/** @var array $data */
$db = $data['db'] ?? [];
?>

<div class="step-intro">
	<p>Ingresá los datos de conexión a tu base de datos MySQL. Si estás en localhost podés dejar la contraseña vacía.</p>
</div>

<div class="form-grid">
	<div class="form-field">
		<label for="hostname">Servidor</label>
		<input type="text" id="hostname" name="hostname"
			value="<?= e($db['hostname'] ?? '') ?>"
			placeholder="localhost" autocomplete="off" required>
		<span class="field-hint">Generalmente <code>localhost</code></span>
	</div>

	<div class="form-field">
		<label for="dbusername">Usuario</label>
		<input type="text" id="dbusername" name="username"
			value="<?= e($db['username'] ?? '') ?>"
			placeholder="root" autocomplete="off" required>
	</div>

	<div class="form-field">
		<label for="dbpassword">Contraseña</label>
		<input type="password" id="dbpassword" name="password"
			placeholder="Contraseña de la base de datos" autocomplete="new-password">
		<span class="field-hint">Puede estar vacía en entornos locales</span>
	</div>

	<div class="form-field">
		<label for="database">Base de datos</label>
		<input type="text" id="database" name="database"
			value="<?= e($db['database'] ?? '') ?>"
			placeholder="mi_base_de_datos" autocomplete="off" required>
	</div>
</div>

<div class="form-actions">
	<button type="submit" class="btn btn-primary">
		Conectar y continuar
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
</div>
