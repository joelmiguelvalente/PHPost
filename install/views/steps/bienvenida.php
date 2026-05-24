<div class="step-intro">
	<p>Para utilizar <strong><?= e(Config::app('app.name')) ?></strong> debés estar de acuerdo con nuestra licencia de uso. Leé el texto completo antes de continuar.</p>
</div>

<div class="license-box">
	<pre><?= e($data['license'] ?? '') ?></pre>
</div>

<div class="form-actions">
	<button type="submit" class="btn btn-primary">
		Acepto los términos
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
</div>
