<div class="form-group">
    <div class="license-box" role="document" aria-label="Términos de licencia">
        <?= htmlspecialchars($license, ENT_QUOTES, 'UTF-8'); ?>
    </div>
</div>
<input type="hidden" name="license" value="true">
<div class="form-actions">
    <button type="submit" class="btn btn-primary">Acepto los términos</button>
</div>
