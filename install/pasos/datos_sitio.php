<div class="step-intro">
    <p>Configurá los datos principales de tu sitio. Estos datos serán los que identifiquen a tu comunidad en toda la web.</p>
</div>

<div class="form-grid">
    <div class="form-group">
        <label for="titulo">Título del sitio</label>
        <input type="text" id="titulo" name="titulo" value="<?= $default['titulo'] ?>" placeholder="Mi comunidad" autocomplete="off" required>
        <span class="help">Nombre que aparecerá en el navegador y en el header</span>
    </div>

    <div class="form-group">
        <label for="slogan">Slogan / Descripción</label>
        <input type="text" id="slogan" name="slogan" value="<?= $default['slogan'] ?>" placeholder="Tu eslogan aquí" autocomplete="off">
        <span class="help">Breve descripción de tu sitio (aparece en el meta description)</span>
    </div>

    <div class="form-group">
        <label for="url">URL del sitio</label>
        <input type="url" id="url" name="url" value="<?= ($default['url'] ?: createURL()) ?>" placeholder="https://tudominio.com" autocomplete="off" required>
        <span class="help">URL completa donde estará alojado tu sitio</span>
    </div>

    <div class="form-group">
        <label for="email">Email de contacto</label>
        <input type="email" id="email" name="email" value="<?= $default['email'] ?>" placeholder="admin@tudominio.com" autocomplete="off" required>
        <span class="help">Correo principal del sitio (para notificaciones y contacto)</span>
    </div>
</div>

<!-- Captcha -->
<div class="form-group captcha-group">
    <label>Protección Captcha <span class="optional">(opcional)</span></label>
    <div class="captcha-options" role="radiogroup" aria-label="Proveedor de captcha">
        <label class="radio-option <?= ($default['captcha_provider'] ?? 'recaptcha') === 'recaptcha' ? 'selected' : '' ?>">
            <input type="radio" name="captcha_provider" value="recaptcha" <?= ($default['captcha_provider'] ?? 'recaptcha') === 'recaptcha' ? 'checked' : '' ?>>
            <span>reCAPTCHA</span>
            <small>Google</small>
        </label>
        <label class="radio-option <?= ($default['captcha_provider'] ?? '') === 'hcaptcha' ? 'selected' : '' ?>">
            <input type="radio" name="captcha_provider" value="hcaptcha" <?= ($default['captcha_provider'] ?? '') === 'hcaptcha' ? 'checked' : '' ?>>
            <span>hCaptcha</span>
            <small>hcaptcha.com</small>
        </label>
        <label class="radio-option <?= ($default['captcha_provider'] ?? '') === 'turnstile' ? 'selected' : '' ?>">
            <input type="radio" name="captcha_provider" value="turnstile" <?= ($default['captcha_provider'] ?? '') === 'turnstile' ? 'checked' : '' ?>>
            <span>Turnstile</span>
            <small>Cloudflare</small>
        </label>
        <label class="radio-option <?= ($default['captcha_provider'] ?? '') === 'none' ? 'selected' : '' ?>">
            <input type="radio" name="captcha_provider" value="none" <?= ($default['captcha_provider'] ?? '') === 'none' || empty($default['captcha_provider'] ? 'checked' : '') ?>>
            <span>Sin captcha</span>
            <small>No recomendado</small>
        </label>
    </div>
    <span class="help">Elige un proveedor de captcha para proteger tu sitio de spam</span>
</div>

<div class="form-group captcha-keys <?= ($default['captcha_provider'] ?? 'recaptcha') === 'none' ? 'hidden' : '' ?>">
    <label for="public_key">Clave Pública / Site Key</label>
    <input type="text" id="public_key" name="public_key" value="<?= $default['public_key'] ?>" placeholder="Clave pública del captcha" autocomplete="off">
    <span class="help">Key que identifica tu sitio en el servicio de captcha</span>
</div>

<div class="form-group captcha-keys <?= ($default['captcha_provider'] ?? 'recaptcha') === 'none' ? 'hidden' : '' ?>">
    <label for="secret_key">Clave Secreta / Secret Key</label>
    <input type="text" id="secret_key" name="secret_key" value="<?= $default['secret_key'] ?>" placeholder="Clave secreta del captcha" autocomplete="new-password">
    <span class="help">Key privada para validar las respuestas del captcha</span>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        Guardar y continuar
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
</div>

<script>
document.querySelectorAll('input[name="captcha_provider"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const keys = document.querySelectorAll('.captcha-keys');
        const isNone = this.value === 'none';
        keys.forEach(el => el.classList.toggle('hidden', isNone));
        // Actualizar estado visual
        document.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('selected'));
        this.closest('.radio-option')?.classList.add('selected');
    });
});
</script>
