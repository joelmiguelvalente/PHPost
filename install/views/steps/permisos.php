<?php /** @var array $data */ ?>

<div class="step-intro">
    <p>
        Los siguientes directorios requieren permisos de escritura.
        Si alguno aparece en rojo, ajustalo desde tu cliente FTP.
    </p>
    <p class="step-note">
        Los permisos varían según el directorio: <code>755</code> para archivos públicos,
        <code>750</code> para cache y logs, <code>700</code> para backups.
    </p>
</div>

<div class="permission-list">
    <?php foreach ($data['checks'] ?? [] as $name => $check): ?>
        <div class="permission-row <?= $check['ok'] ? 'ok' : 'fail' ?>">
            <div class="permission-info">
                <span class="permission-name"><?= e(ucfirst($name)) ?></span>
                <code class="permission-path"><?= e($check['route']) ?></code>
            </div>
            <div class="permission-status">
                <?php if ($check['ok']): ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?= e($check['chmod']) ?> — Correcto</span>
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M5.5 5.5l5 5M10.5 5.5l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span><?= e($check['chmod']) ?> — Necesita <?= e($check['expected']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="form-actions">
    <button type="submit" class="btn <?= ($data['allOk'] ?? false) ? 'btn-primary' : 'btn-secondary' ?>">
        <?= ($data['allOk'] ?? false) ? 'Continuar' : 'Volver a verificar' ?>
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <?php if ($data['allOk'] ?? false): ?>
                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <?php else: ?>
                <path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M13 3v5h-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <?php endif; ?>
        </svg>
    </button>
</div>
