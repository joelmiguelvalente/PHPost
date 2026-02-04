<h1 class="text-xl font-semibold mb-4">Administrar Publicidad</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}
	
	<form method="post" autocomplete="off" class="space-y-6">
		<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
			{include "dashboard/Legend.tpl" text="C&oacute;digos"}

			{include "dashboard/FormGroupTextarea.tpl" id="ads_300" label="Banner 300x250" name="ads_300" value=$tsConfig.ads_300}

			{include "dashboard/FormGroupTextarea.tpl" id="ads_468" label="Banner 468x60" name="ads_468" value=$tsConfig.ads_468}

			{include "dashboard/FormGroupTextarea.tpl" id="ads_160" label="Banner 160x600" name="ads_160" value=$tsConfig.ads_160}

			{include "dashboard/FormGroupTextarea.tpl" id="ads_728" label="Banner 728x90" name="ads_728" value=$tsConfig.ads_728}

			{include "dashboard/FormGroup.tpl" id="ads_search" label="Search ID" helper="ID de tu buscador de GOOGLE" name="ads_search" value=$tsConfig.ads_search}

			{include "dashboard/Button.tpl" submit_text="Guardar Cambios"}
		</fieldset>
	</form>
</div>