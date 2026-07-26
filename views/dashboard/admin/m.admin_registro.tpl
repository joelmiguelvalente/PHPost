<h1 class="text-xl font-semibold mb-4">Administrar Registro</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Configuraciones guardadas" color="green" show=$tsSave}
	<form method="post" autocomplete="off" class="space-y-6">
		<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
			{include "dashboard/Legend.tpl" text="Configuración del Registro"}

			{include "dashboard/FormGroup.tpl" type="number" id="c_allow_edad" label="Edad requerida" helper="A partir de que edad los usuarios pueden registrarse." name="c_allow_edad" value=$tsRegistro.c_allow_edad group=true suffix="a&ntilde;os" maxlength=3}

			<!-- Modo mantenimiento -->
		   <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
		      <div>
		        	<label for="c_message_welcome" class="font-medium text-gray-700 dark:text-gray-300">Mensaje de Bienvenida</label>
		        	<p class="mt-1 text-xs text-gray-500">[usuario] => Nombre del registrado <br /> [welcome] => Bienvenido/a depende del sexo <br /> [web] => Nombre de esta web <br /> <br />(Se aceptan BBCodes y Smileys)</p>
		      </div>
		      <div class="md:col-span-2 space-y-3">
		        	<div class="flex gap-6">
						{include "dashboard/Select.tpl" id="c_met_welcome" name="c_met_welcome" options=[
						   ['value'=>0, 'label'=>'No dar bienvenida'],
						   ['value'=>1, 'label'=>'Muro'],
						   ['value'=>2, 'label'=>'Mensaje privado'],
						   ['value'=>3, 'label'=>'Aviso']
						] selected=$tsRegistro.c_met_welcome}
		        	</div>
		        	{include "dashboard/Input.tpl" name="c_message_welcome" id="c_message_welcome" value=$tsRegistro.c_message_welcome placeholder="{$tsRegistro.c_message_welcome}"}
		      </div>
		   </div>

		   {include "dashboard/FormGroup.tpl" type="radio" id="c_reg_active" label="Registro abierto" helper="Permitir el registro de nuevos usuarios" name="c_reg_active" checked=$tsRegistro.c_reg_active labels=["Sí", "No"] values=[1,0]}

		   {include "dashboard/FormGroup.tpl" type="radio" id="c_reg_activate" label="Activar usuarios" helper="Activar automáticamente la cuenta de usuario." name="c_reg_activate" checked=$tsRegistro.c_reg_activate labels=["Sí", "No"] values=[1,0]}

		   <hr>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
		      <div>
		        	<label for="captcha_provider" class="font-medium text-gray-700 dark:text-gray-300">Proveedor de captcha</label>
		        	<p class="mt-1 text-xs text-gray-500">reCaptcha Enterprise: Google podría cobrarte para evitar limitaciones.</p>
		      </div>
		      <div class="md:col-span-2 space-y-3">
		        	<div class="flex gap-6">
						{include "dashboard/Select.tpl" id="captcha_provider" name="captcha_provider" options=[
						   ['value'=>'recaptcha', 'label'=>'reCaptcha v3'],
						   ['value'=>'hcaptcha', 'label'=>'hCaptcha']
						] selected=$tsRegistro.captcha_provider}
		        	</div>
		      </div>
		   </div>

			<h3 class="my-3">Para reCaptcha accede a <a href="https://console.cloud.google.com/security/recaptcha" target="_blank"><strong>console.cloud.google.com/security/recaptcha</strong></a></h3>

			{include "dashboard/FormGroup.tpl" id="public_key" label="Clave publica" name="public_key" value=$tsRegistro.public_key}

			{include "dashboard/FormGroup.tpl" id="secret_key" label="Clave secreta" name="secret_key" value=$tsRegistro.secret_key}

			{include "dashboard/Button.tpl" submit_text="Guardar Cambios"}
		</fieldset>
	</form>
</div>
