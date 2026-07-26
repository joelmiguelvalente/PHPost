<div class="step-intro">
    <p>Creá la cuenta de administrador principal. Este usuario tendrá acceso total al panel de administración.</p>
</div>

<div class="form-grid">
    <div class="form-group">
        <label for="nickname">Nickname</label>
        <input type="text" id="nickname" name="nickname" value="<?= $default['nickname'] ?>" placeholder="Ej: admin" autocomplete="off" required>
        <span class="help">Nombre de usuario del administrador</span>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= $default['email'] ?>" placeholder="admin@tudominio.com" autocomplete="off" required>
        <span class="help">Correo electrónico del administrador</span>
    </div>

    <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" value="<?= $default['password'] ?>" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
        <span class="help">Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números</span>
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirmar contraseña</label>
        <input type="password" id="confirm_password" name="confirm_password" value="<?= $default['confirm_password'] ?>" placeholder="Repite la contraseña" autocomplete="new-password" required>
        <span class="help">Vuelve a escribir la contraseña para confirmar</span>
    </div>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        Crear cuenta y continuar
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
</div>
