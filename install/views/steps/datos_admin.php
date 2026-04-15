<?php /** @var array $data */ $u = $data['user'] ?? []; ?>

<div class="step-intro">
	<p>Creá tu cuenta de administrador. Más adelante podés completar el perfil con más información.</p>
</div>

<div class="form-grid">
	<div class="form-field">
		<label for="user_name">Nombre de usuario</label>
		<input type="text" id="user_name" name="user_name"
			value="<?= e($u['user_name'] ?? '') ?>"
			placeholder="JohnDoe" autocomplete="username" required>
		<span class="field-hint">Solo letras y números, sin espacios</span>
	</div>

	<div class="form-field">
		<label for="user_email">Email</label>
		<input type="email" id="user_email" name="user_email"
			value="<?= e($u['user_email'] ?? '') ?>"
			placeholder="admin@misitio.com" autocomplete="email" required>
	</div>

	<div class="form-field">
		<label for="user_password">Contraseña</label>
		<input type="password" id="user_password" name="user_password"
			placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
		<span class="field-hint">Debe tener mayúsculas, números y un carácter especial</span>
	</div>

	<div class="form-field">
		<label for="user_confirm">Confirmar contraseña</label>
		<input type="password" id="user_confirm" name="user_confirm"
			placeholder="Repetí la contraseña" autocomplete="new-password" required>
	</div>
</div>

<div class="form-actions">
	<button type="submit" class="btn btn-primary">
		Crear administrador
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
</div>
