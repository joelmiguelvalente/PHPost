<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Error — Instalador PHPost</title>
<link rel="stylesheet" href="./estilo.css">
</head>
<body>
<div class="installer-wrap">
	<div class="installer-error-page">
		<h1>Algo salió mal</h1>
		<p><?= htmlspecialchars($error ?? 'Error desconocido.', ENT_QUOTES, 'UTF-8') ?></p>
		<a href="index.php?step=bienvenida" class="btn">Volver al inicio</a>
	</div>
</div>
</body>
</html>
