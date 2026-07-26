<div class="form-group">
    <label for="hostname">Servidor</label>
    <input type="text" value="<?= $default['hostname'] ?>" name="hostname" id="hostname" placeholder="ej: localhost" autocomplete="off">
    <small class="help">Nombre del servidor de la base de datos</small>
</div>

<div class="form-group">
    <label for="username">Usuario de conexión</label>
    <input type="text" value="<?= $default['username'] ?>" name="username" id="username" placeholder="ej: root" autocomplete="off">
    <small class="help">Usuario con permisos de escritura</small>
</div>

<div class="form-group">
    <label for="password">Contraseña de la conexión</label>
    <input type="password" value="<?= $default['password'] ?>" name="password" id="password" placeholder="ej: 123456mipass" autocomplete="off">
    <small class="help">Deja vacío si no tiene contraseña</small>
</div>

<div class="form-group">
    <label for="database">Base de datos</label>
    <input type="text" value="<?= $default['database'] ?>" name="database" id="database" placeholder="ej: mydatabase" autocomplete="off">
    <small class="help">Nombre de la base de datos a usar</small>
</div>

<div class="form-actions">
    <?php if($success): ?>
        <input type="hidden" name="comprobado" value="true">
        <button type="submit" class="btn btn-primary">Continuar</button>
    <?php else: ?>
        <button type="submit" class="btn btn-ghost">Comprobar conexión</button>
    <?php endif; ?>
</div>
