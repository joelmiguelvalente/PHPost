<div class="permissions-grid">
    <div>
        <h3>Permisos de directorios</h3>
        <?php foreach($permisos as $p => $permiso): ?>
            <div class="perm-item">
                <span class="perm-icon" aria-hidden="true">📁</span>
                <div class="perm-name">
                    <?= ucfirst($p) ?>
                    <small><?= htmlspecialchars($permiso['root']) ?></small>
                </div>
                <span class="perm-status <?= $permiso['css'] ?>">
                    <?php if (!$permiso['exists']): ?>
                        No existe
                    <?php elseif (!$permiso['writable']): ?>
                        <?= $permiso['chmod'] ?> (Requerido: <?= $permiso['chmod_ok'] ?>)
                    <?php else: ?>
                        <?= $permiso['chmod'] ?>
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
    <div>
        <h3>Extensiones PHP</h3>
        <?php foreach($extensiones as $extension): ?>
            <div class="perm-item">
                <span class="perm-icon" aria-hidden="true">🔌</span>
                <div class="perm-name">
                    <?= htmlspecialchars($extension['name']) ?>
                </div>
                <span class="perm-status<?= ($extension['hability'] ? ' ok' : ' fail') ?>">
                    <?= htmlspecialchars($extension['message']) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="form-actions">
    <?php if($hasErrors): ?>
        <button type="submit" class="btn btn-primary">
            Volver a verificar
        </button>
    <?php else: ?>
        <button type="submit" class="btn btn-primary" <?= !empty($errors) ? 'disabled' : '' ?>>
            Aceptar y continuar
        </button>
    <?php endif; ?>
</div>
