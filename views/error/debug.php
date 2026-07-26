<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<meta name="description" content="Error al cargar el recurso solicitado">
<title>Error: <?= htmlspecialchars($title ?? 'Error') ?></title>
<link rel="stylesheet" href="assets/css/error.css">
<style>
		:root {
			--primary: #3498db;
			--primary-dark: #2980b9;
			--secondary: #95a5a6;
			--secondary-dark: #7f8c8d;
			--error: #e74c3c;
			--error-dark: #c0392b;
			--background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			--surface: #ffffff;
			--text: #2c3e50;
			--text-light: #7f8c8d;
			--border: #e9ecef;
			--shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
			--shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
		}
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
			line-height: 1.6;
			color: var(--text);
			min-height: 100vh;
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 20px;
			background: var(--background);
		}
		.error-container {
			background: var(--surface);
			border-radius: 24px;
			box-shadow: var(--shadow);
			padding: 40px;
			max-width: 600px;
			width: 100%;
			height: fit-content;
		}
		.error-icon {
			font-size: 72px;
			margin-bottom: 24px;
			display: block;
			animation: pulse 2s infinite;
		}
		@keyframes pulse {
			0% { transform: scale(1); }
			50% { transform: scale(1.05); }
			100% { transform: scale(1); }
		}
		.error-code {
			font-size: 96px;
			font-weight: 700;
			color: var(--error);
			margin: 0 0 15px;
			letter-spacing: -1px;
		}
		.error-title {
			font-size: 32px;
			font-weight: 700;
			color: var(--text);
			margin: 0 0 16px;
			line-height: 1.2;
		}
		.error-message {
			font-size: 18px;
			color: var(--text-light);
			margin: 0 0 32px;
			line-height: 1.6;
		}
		.error-details {
			background: #f8f9fa;
			border-radius: 12px;
			padding: 24px;
			margin: 32px 0;
			text-align: left;
			border: 1px solid var(--border);
		}
		.error-details h4 {
			margin: 0 0 16px;
			color: var(--text);
			font-size: 16px;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.error-file {
			font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
			font-size: 13px;
			color: var(--text-light);
			margin: 5px 0;
		}
		.error-actions {
			display: flex;
			gap: 16px;
			justify-content: center;
			flex-wrap: wrap;
			margin-top: 40px;
		}
		.btn {
			padding: 14px 32px;
			border: none;
			border-radius: 10px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			display: inline-flex;
			align-items: center;
			justify-content: center;
			text-decoration: none;
			min-width: 140px;
		}
		.btn-primary {
			background: var(--primary);
			color: white;
		}
		.btn-primary:hover {
			background: var(--primary-dark);
			transform: translateY(-3px);
			box-shadow: var(--shadow-hover);
		}
		.btn-secondary {
			background: var(--surface);
			color: var(--secondary);
			border: 2px solid var(--secondary);
		}
		.btn-secondary:hover {
			background: var(--secondary);
			color: white;
			transform: translateY(-3px);
			box-shadow: var(--shadow-hover);
		}
		.error-support {
			margin-top: 25px;
			padding-top: 20px;
			border-top: 1px solid var(--border);
			font-size: 13px;
			color: var(--text-light);
		}
		.error-support a {
			color: var(--primary);
			text-decoration: none;
		}
		.error-support a:hover {
			text-decoration: underline;
		}
		@media (max-width: 480px) {
			.error-container {
				padding: 25px;
			}
			.error-code {
				font-size: 48px;
			}
			.error-title {
				font-size: 22px;
			}
			.error-actions {
				flex-direction: column;
			}
			.btn {
				width: 100%;
			}
		}
</style>
</head>
<body>
	<main class="error-page">
		<section class="error-header">
			<div class="error-badge error-500">ERROR 500</div>
			<h1 class="error-title">Error del servidor</h1>
			<p class="error-description">Ocurrió un error inesperado. Por favor, intente nuevamente más tarde.</p>
		</section>
	
		<section class="error-content">
			<?php if ($file || $line): ?>
				<div class="error-location">
					<?php if ($file): ?>
						<div class="error-item">
							<span class="error-label">Archivo:</span>
							<code class="error-code"><?php echo $file; ?></code>
						</div>
					<?php endif; ?>
					<?php if ($line): ?>
						<div class="error-item">
							<span class="error-label">Línea:</span>
							<code class="error-code"><?php echo (int)$line; ?></code>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($trace)): ?>
				<div class="error-trace">
					<div class="trace-header">
						<span class="trace-title">Stack Trace</span>
						<button class="copy-btn" onclick="copyToClipboard('trace-content')" title="Copiar traza">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
								<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
							</svg>
						</button>
					</div>
					<div class="trace-content" id="trace-content">
						<?= htmlspecialchars($trace) ?>
					</div>
				</div>
			<?php endif; ?>
		</section>

		<section class="error-actions">
			<a href="." class="btn btn-primary">Volver al inicio</a>
			<a href="javascript:history.back()" class="btn btn-secondary">Volver atrás</a>
			<div class="error-id" id="error-id">
				Error ID: <code>error-<?= strtoupper(substr(md5(microtime()), 0, 8)) ?>-<?= date('YmdHis') ?></code>
				<button class="copy-id-btn" onclick="copyErrorId()" title="Copiar ID">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
						<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
					</svg>
				</button>
			</div>
		</section>
	</main>

	<script>
	function copyToClipboard(elementId) {
		const element = document.getElementById(elementId);
		if (!element) return;
		
		const text = element.textContent;
		
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(text).then(() => {
				showToast('Traza copiada al portapapeles');
			});
		} else {
			fallbackCopy(text, 'Traza');
		}
	}
	
	function copyErrorId() {
		const errorIdElement = document.getElementById('error-id');
		const codeElement = errorIdElement.querySelector('code');
		if (!codeElement) return;
		
		const errorIdText = codeElement.textContent;
		
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(errorIdText).then(() => {
				showToast('ID de error copiado al portapapeles');
			});
		} else {
			fallbackCopy(errorIdText, 'ID de error');
		}
	}
	
	function fallbackCopy(text, type) {
		const textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'absolute';
		textArea.style.left = '-999999px';
		textArea.style.top = '-999999px';
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();
		try {
			document.execCommand('copy');
			showToast(type + ' copiado al portapapeles');
		} catch (err) {
			console.error('Error al copiar:', err);
			showToast('Error al copiar');
		}
		finally {
			document.body.removeChild(textArea);
		}
	}
	
	function showToast(message) {
		const toast = document.createElement('div');
		toast.className = 'toast-message';
		toast.textContent = message;
		toast.style.cssText = `
			position: fixed;
			bottom: 20px;
			left: 50%;
			transform: translateX(-50%);
			background: #10b981;
			color: white;
			padding: 12px 24px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			z-index: 1000;
			font-size: 14px;
			font-weight: 500;
			opacity: 0;
			transition: opacity 0.3s ease;
		`;
		document.body.appendChild(toast);
		setTimeout(() => {
			toast.style.opacity = '1';
		}, 10);
		setTimeout(() => {
			toast.style.opacity = '0';
			setTimeout(() => {
				document.body.removeChild(toast);
			}, 300);
		}, 2000);
	}
	</script>
</body>
</html>

<style>
	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
	}

	body {
		font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
		background: #1e1e1e;
		color: #d4d4d4;
		line-height: 1.6;
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px;
	}

	.error-page {
		width: 100%;
		max-width: 800px;
		background: #252525;
		border: 1px solid #333;
		border-radius: 8px;
		box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
		overflow: hidden;
	}

	.error-header {
		padding: 32px;
		border-bottom: 1px solid #333;
		text-align: center;
	}

	.error-badge {
		display: inline-block;
		padding: 4px 12px;
		background: #d32f2f;
		color: #fff;
		font-size: 12px;
		font-weight: 600;
		border-radius: 4px;
		margin-bottom: 16px;
		text-transform: uppercase;
		letter-spacing: 1px;
	}

	.error-title {
		font-size: 24px;
		font-weight: 400;
		color: #fff;
		margin-bottom: 8px;
	}

	.error-description {
		font-size: 14px;
		color: #999;
		max-width: 480px;
		margin: 0 auto;
	}

	.error-content {
		padding: 24px 32px;
	}

	.error-location {
		margin-bottom: 24px;
	}

	.error-item {
		margin-bottom: 8px;
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.error-label {
		color: #999;
		font-size: 13px;
		min-width: 80px;
		text-align: right;
	}

	.error-code {
		color: #c586c0;
		font-size: 13px;
		font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
	}

	.error-trace {
		background: #1e1e1e;
		border: 1px solid #333;
		border-radius: 4px;
		overflow: hidden;
	}

	.trace-header {
		padding: 12px 16px;
		background: #2d2d2d;
		border-bottom: 1px solid #333;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.trace-title {
		font-size: 13px;
		color: #999;
		font-weight: 600;
		text-transform: uppercase;
	}

	.copy-btn {
		background: none;
		border: none;
		color: #999;
		cursor: pointer;
		padding: 4px;
		border-radius: 4px;
		transition: all 0.2s;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.copy-btn:hover {
		background: #333;
		color: #fff;
	}

	.trace-content {
		padding: 16px;
		font-size: 12px;
		line-height: 1.5;
		overflow-x: auto;
		white-space: pre;
		color: #d4d4d4;
	}

	.error-actions {
		padding: 24px 32px;
		border-top: 1px solid #333;
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 16px;
	}

	.btn {
		padding: 10px 20px;
		border: none;
		border-radius: 4px;
		font-size: 14px;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.2s;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.btn-primary {
		background: #0e639c;
		color: #fff;
	}

	.btn-primary:hover {
		background: #1177bb;
	}

	.btn-secondary {
		background: #3c3c3c;
		color: #ccc;
		border: 1px solid #555;
	}

	.btn-secondary:hover {
		background: #464646;
		color: #fff;
	}

	.error-id {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
		color: #999;
	}

	.error-id code {
		color: #c586c0;
		background: #2d2d2d;
		padding: 2px 6px;
		border-radius: 3px;
		font-size: 12px;
	}

	.copy-id-btn {
		background: none;
		border: none;
		color: #999;
		cursor: pointer;
		padding: 4px;
		border-radius: 4px;
		transition: all 0.2s;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.copy-id-btn:hover {
		background: #333;
		color: #fff;
	}

	.toast-message {
		position: fixed;
		bottom: 20px;
		left: 50%;
		transform: translateX(-50%);
		background: #10b981;
		color: white;
		padding: 12px 24px;
		border-radius: 8px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
		z-index: 1000;
		font-size: 14px;
		font-weight: 500;
		pointer-events: none;
	}

	@media (max-width: 640px) {
		.error-actions {
			flex-direction: column;
			align-items: stretch;
		}
		.error-id {
			justify-content: center;
			margin-top: 16px;
		}
		.error-item {
			flex-direction: column;
			align-items: flex-start;
			gap: 4px;
		}
		.error-label {
			text-align: left;
			min-width: auto;
		}
	}
</style>
</head>
<body>
	<div id="style-container" style="display: none;">
		<div class="error-page">
			<section class="error-header">
				<div class="error-badge error-500">ERROR 500</div>
				<h1 class="error-title">Error del servidor</h1>
				<p class="error-description">Ocurrió un error inesperado. Por favor, intente nuevamente más tarde.</p>
			</section>

			<section class="error-content">
				<?php if ($file || $line): ?>
					<div class="error-location">
						<?php if ($file): ?>
							<div class="error-item">
								<span class="error-label">Archivo:</span>
								<code class="error-code">$file</code>
							</div>
						<?php endif; ?>
						<?php if ($line): ?>
							<div class="error-item">
								<span class="error-label">Línea:</span>
								<code class="error-code"><?php echo (int)$line; ?></code>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($trace)): ?>
					<div class="error-trace">
						<div class="trace-header">
							<span class="trace-title">Stack Trace</span>
							<button class="copy-btn" onclick="copyToClipboard('trace-content')" title="Copiar traza">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
									<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
								</svg>
							</button>
						</div>
						<div class="trace-content" id="trace-content">
							<?= htmlspecialchars($trace) ?>
						</div>
					</div>
				<?php endif; ?>
			</section>

			<section class="error-actions">
				<a href="." class="btn btn-primary">Volver al inicio</a>
				<a href="javascript:history.back()" class="btn btn-secondary">Volver atrás</a>
				<div class="error-id" id="error-id">
					Error ID: <code>error-<?= strtoupper(substr(md5(microtime()), 0, 8)) ?>-<?= date('YmdHis') ?></code>
					<button class="copy-id-btn" onclick="copyErrorId()" title="Copiar ID">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
							<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
						</svg>
					</button>
				</div>
			</section>
		</div>
	</div>
</body>
</html>
</div>
