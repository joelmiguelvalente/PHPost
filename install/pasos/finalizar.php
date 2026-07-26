<div class="step-intro">
    <p>¡Instalación completada con éxito! Tu sitio <strong><?= htmlspecialchars($datos['titulo']) ?></strong> está listo para usar.</p>
</div>

<div class="final-section">
    <div class="final-details" role="list">
        <div class="detail-item" role="listitem">
            <span class="detail-label">Título</span>
            <span class="detail-value"><?= htmlspecialchars($datos['titulo']) ?></span>
        </div>
        <div class="detail-item" role="listitem">
            <span class="detail-label">URL</span>
            <span class="detail-value"><?= htmlspecialchars($datos['url']) ?></span>
        </div>
        <div class="detail-item" role="listitem">
            <span class="detail-label">Administrador</span>
            <span class="detail-value"><?= htmlspecialchars($user['user_name']) ?></span>
        </div>
        <div class="detail-item" role="listitem">
            <span class="detail-label">Email</span>
            <span class="detail-value"><?= htmlspecialchars($user['user_email']) ?></span>
        </div>
    </div>

    <div class="final-actions">
        <button type="submit" name="finish_site" class="btn btn-primary">
            Ir al sitio
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button type="submit" name="finish_admin" class="btn btn-ghost">
            Panel de administración
        </button>
    </div>

    <div class="final-tip" role="alert">
        <small>⚠️ Recuerda eliminar la carpeta <strong>/install</strong> por seguridad.</small>
    </div>
</div>
