<div class="step-intro">
    <p>Configurá el servidor de correo para que tu comunidad pueda enviar emails. Podés omitir este paso y configurarlo más tarde desde el panel de administración.</p>
</div>

<div class="form-grid">
    <div class="form-group">
        <label for="smtphost">SMTP Host</label>
        <input type="text" id="smtphost" name="smtphost" value="<?= $default['smtphost'] ?>" placeholder="smtp.gmail.com" autocomplete="off">
    </div>

    <div class="form-group">
        <label for="smtpuser">Usuario SMTP</label>
        <input type="text" id="smtpuser" name="smtpuser" value="<?= $default['smtpuser'] ?>" placeholder="noreply@example.com" autocomplete="off">
    </div>

    <div class="form-group">
        <label for="smtppass">Contraseña SMTP</label>
        <input type="password" id="smtppass" name="smtppass" value="<?= $default['smtppass'] ?>" placeholder="Contraseña de conexión" autocomplete="new-password">
    </div>

    <div class="form-group">
        <label for="smtpfrom">Nombre del remitente</label>
        <input type="text" id="smtpfrom" name="smtpfrom" value="<?= $default['smtpfrom'] ?>" placeholder="Mi Comunidad" autocomplete="off">
        <span class="help">Nombre que verán los destinatarios</span>
    </div>
</div>

<div class="form-actions">
    <button type="submit" name="omitir" class="btn btn-ghost">
        Omitir por ahora
    </button>
    <button type="submit" class="btn btn-primary">
        Guardar y continuar
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
</div>
