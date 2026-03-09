<h1 class="text-xl font-semibold mb-4">Administrar PHPMailer</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Configuraciones guardadas" color="green" show=$tsSave}
	<form method="post" autocomplete="off" class="space-y-6">
		<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
			{include "dashboard/Legend.tpl" text="Configuraci&oacute;n del PHPMailer"}

			{include "dashboard/FormGroup.tpl" id="SMTP_HOST" label="Servidor SMTP" helper="Dirección del servidor SMTP. Ej: smtp.gmail.com" name="SMTP_HOST" value=$tsPHPMailer.SMTP_HOST}

			{include "dashboard/FormGroup.tpl" id="SMTP_USER" label="Usuario SMTP" helper="Correo con el que te autenticás en el servidor. Ej: tucorreo@gmail.com" name="SMTP_USER" value=$tsPHPMailer.SMTP_USER}

			{include "dashboard/FormGroup.tpl" id="SMTP_PASS" label="Contraseña SMTP" helper="Contraseña o contraseña de aplicación del usuario SMTP." name="SMTP_PASS" value=$tsPHPMailer.SMTP_PASS}

			{include "dashboard/FormGroup.tpl" id="SMTP_FROM" label="Correo remitente" helper="Dirección que aparecerá como remitente. Ej: no-reply@tudominio.com" name="SMTP_FROM" value=$tsPHPMailer.SMTP_FROM}

			{include "dashboard/FormGroup.tpl" id="SMTP_SECURE" label="Cifrado" helper="Protocolo de cifrado: 'tls' (puerto 587), 'ssl' (puerto 465) o vacío si no usás cifrado." name="SMTP_SECURE" value=$tsPHPMailer.SMTP_SECURE}

			{include "dashboard/FormGroup.tpl" id="SMTP_PORT" label="Puerto SMTP" helper="Puerto de conexión: 587 (TLS), 465 (SSL) o 25 (sin cifrado)." name="SMTP_PORT" value=$tsPHPMailer.SMTP_PORT}

			{include "dashboard/Button.tpl" submit_text="Guardar Cambios"}
		</fieldset>
	</form>
</div>