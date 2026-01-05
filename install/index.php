<?php

/**
 * @name install.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);
//
require_once __DIR__ . '/backend.php';
?>
<!DOCTYPE html>
<html data-install="default">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="author" content="Miguel92" />
<title><?= $tsTitle ?></title>
<link href="<?= $url ?>/themes/default/images/favicon.png" rel="icon" type="image/png">
<link href="<?= $base ?>/estilo.css?t=<?= time() ?>" rel="stylesheet" type="text/css" />
</head>

<body>
	<div class="container">
		<header>
			<h1>
				<a href="<?= Config::app('app.server') ?>" title="Servidor de Discord" target="_blank">
					<img src="./logo.png" title="Logo antiguo de PHPost" />
				</a>
			</h1>
			<h2>Programa de instalaci&oacute;n: <span><?= Config::app('app.name') ?></span></h2>
		</header>
		<div class="content">
			<div class="col_left">
				<ul class="menu">
					<?php foreach($menu as $item => $link): ?>
						<li class="item<?= ($step >= $item ? ' active' : '') ?>"><?= $link ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="col_right">
				<?php if (!empty($message)): ?>
					<div class="error"><?= $message ?></div>
				<?php endif; ?>

				<form method="POST" id="form">
					<fieldset>
						<?php if ($step === 0): ?>
		
							<legend>Licencia</legend>
							<p>Para utilizar <?= Config::app('app.name') ?> debes estar de acuerdo con nuestra licencia de uso.</p>
							<textarea name="license" rows="20"><?= $license; ?></textarea>
							<p><input type="submit" class="gbqfb" value="Acepto"/></p>
						
						<?php elseif ($step === 1): ?>

							<legend>Permisos de escritura</legend>
							<p>Los siguientes archivos y directorios requieren de permisos especiales, debes cambiarlos desde tu cliente FTP, los archivos deben tener permiso <strong>666</strong> y los direcorios <strong>777</strong></p>
							<?php foreach($permisos as $name => $value): ?>
								<dl>
									<dt><label for="<?= $name ?>"><?= ucfirst($name) ?><small><?= $value['route'] ?></small></label></dt>
									<dd><span class="status <?= $value['css'] ?>"><?= $value['text'] ?></span></dd>
								</dl>
							<?php endforeach; ?>
								
							<p><input type="submit" class="gbqfb" value="<?= ($next ? 'Continuar &raquo;' : 'Volver a verificar') ?>"/></p>
					
						<?php elseif ($step === 2): ?>

							<legend>Base de datos</legend>
							<p>Ingresa tus datos de conexi&oacute;n a la base de datos.</p>
						
							<dl>
								<dt><label for="hostname">Servidor:</label><span>Donde est&aacute; la base de datos, ej: <strong>localhost</strong></span></dt>
								<dd><input type="text" autocomplete="off" id="hostname" name="hostname" placeholder="localhost" value="<?= $db['hostname'] ?>" required/></span></dd>
							</dl>
							<dl>
								<dt><label for="username">Usuario:</label><span>El usuario de tu base de datos.</span></dt>
								<dd><input type="text" autocomplete="off" id="username" name="username" placeholder="root" value="<?= $db['username'] ?>" required/></span></dd>
							</dl>
							<dl>
								<dt><label for="password">Contrase&ntilde;a:</label><span>Para acceder a la base de datos.</span></dt>
								<dd><input type="password" autocomplete="off" id="password" name="password" placeholder="Contraseña de la base" value="<?= $db['password'] ?>" /></span></dd>
							</dl>
							<dl>
								<dt><label for="database">Base de datos</label><span>Nombre de la base de datos para tu web.</span></dt>
								<dd><input type="text" autocomplete="off" id="database" name="database" placeholder="mydatabase" value="<?= $db['database'] ?>" required/></span></dd>
							</dl>
							<p><input type="submit" class="gbqfb" value="Continuar &raquo;"/></p>
			
						<?php elseif($step === 3): ?>
							<legend>Datos del sitio</legend>
							
							<dl>
								<dt><label for="titulo">Nombre:</label><span>El t&iacute;tulo de tu web.</span></dt>
								<dd><input type="text" id="titulo" name="titulo" value="<?= $site['titulo']; ?>" placeholder="<?= Config::app('app.name') ?>" required/></dd>
							</dl>
							<dl>
								<dt><label for="slogan">Lema:</label><span>Ej: Inteligencia recargada.</span></dt>
								<dd><input type="text" id="slogan" name="slogan" value="<?= $site['slogan']; ?>" placeholder="<?= Config::app('app.slogan') ?>" required/></span></dd>
							</dl>
							<dl>
								<dt><label for="url">Direcci&oacute;n:</label><span>Ingresa la url donde  est&aacute; alojada tu web, sin la &uacute;ltima diagonal <strong>/</strong> </span></dt>
								<dd><input type="url" id="url" name="url" value="<?= $site['url'] ?>" required/></dd>
							</dl>
							<dl>
								<dt><label for="email">Email:</label><span>Email de la web o del administrador.</span></dt>
								<dd><input type="email" id="email" name="email" value="<?= $site['email'] ?>" placeholder="noreply@example.com" required/></dd>
							</dl>
							<br>
							<legend>Datos de reCAPTCHA <small>(opcional)</small></legend>
							<p>Completalo solo si querés habilitar protección antispam. Obten la clave desde <a href="https://www.google.com/recaptcha/admin" target="_blank"><strong>google.com/recaptcha/admin</strong></a></p>
							<dl>
								<dt><label for="pkey">Clave pública del sitio:</label></dt>
								<dd><input type="text" id="pkey" name="pkey" value="<?= $site['pkey'] ?>" placeholder="6LfFFiMdAAAAAAQjDafWXZ0FeyesKYjVm4DSUoao" required /></dd>
							</dl>
							<dl>
								<dt><label for="skey">Clave secreta:</label></dt>
								<dd><input type="text" id="skey" name="skey" value="<?= $site['skey'] ?>" placeholder="6LfFFiMdAAAAAFIP4oNFLQx5Fo1FyorTzNps8ChE" required/></dd>
							</dl>
							<p><input type="submit" class="gbqfb" value="Continuar &raquo;"/></p>
					
				
					<?php elseif ($step === 4): ?>
						<legend>Administrador</legend>
						<p>Ingresa tus datos de usuario, m&aacute;s adelante debes editar tu cuenta para ingresar datos como, fecha de nacimiento, lugar de residencia, etc.</p>

						<dl>
							<dt><label for="username">Nombre de usuario:</label></dt>
							<dd><input type="text" id="username" name="user_name" value="<?= $user['user_name'] ?>" placeholder="JohnDoe" required/></span></dd>
						</dl>
						<dl>
							<dt><label for="userpassword">Contrase&ntilde;a:</label></dt>
							<dd><input type="password" id="userpassword" name="user_password" value="<?= $user['user_password'] ?>" placeholder="@john1234" required/></span></dd>
						</dl>
						<dl>
							<dt><label for="userconfirm">Confirmar contrase&ntilde;a:</label><span>Ingresa tu contrase&ntilde;a nuevamente.</span></dt>
							<dd><input type="password" id="userconfirm" name="user_confirm" value="<?= $user['user_confirm'] ?>" placeholder="@john1234" required/></span></dd>
						</dl>
						<dl>
							<dt><label for="useremail">Email:</label><span>Ingresa tu direcci&oacute;n de email.</span></dt>
							<dd><input type="email" id="useremail" name="user_email" value="<?= $user['user_email'] ?>" placeholder="johndoe@gmail.com" required/></span></dd>
						</dl>
						<p><input type="submit" class="gbqfb" value="Continuar &raquo;"/></p>
				
					<?php elseif ($step == 5): ?>
						<h2 class="s16">Bienvenido a PHPost Risus</h2>
						<!-- ESTADISTICAS -->
						<form action="http://download.phpost.net/feed/install.php" method="post" id="form">
						<div class="error">Ingresa a tu FTP y borra la carpeta <strong><?php echo basename(getcwd()); ?></strong> antes de usar el script.</div>
						
							Gracias por instalar <strong>PHPost Risus</strong>, ya est&aacute; lista tu nueva comunidad <strong>Link Sharing System</strong>. S&oacute;lo inicia sesi&oacute;n con tus datos y comienza a disfrutar. Ahora no dejes de <a href="<?= Config::app('app.server') ?>" target="_blank"><u>visitarnos</u></a> para estar pendiente de futuras actualizaciones. Recuerda reportar cualquier bug que encuentres, de esta manera todos ganamos.<br /><br />
						
							<input type="hidden" name="key" value="<?php echo $key; ?>" />
							<input type="submit" value="Finalizar" class="gbqfb" />
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div id="footer">
			<p>Powered by <a href="<?= Config::app('app.server') ?>" target="_blank">PHPost</a></p>
		</div>
	</div>
</body>
</html>
