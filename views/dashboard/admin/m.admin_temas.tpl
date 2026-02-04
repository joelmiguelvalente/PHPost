<h1 class="text-xl font-semibold mb-4">Administrar Temas</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}
	
	{if $tsAct == ''}
		<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
			<table class="min-w-full border-collapse text-sm">
				{include "dashboard/table/Thead.tpl" fields=[
					"ID",
					"Vista previa",
					"Nombre",
					"Creado por",
					"En uso",
					"Opciones"
				]}
				<tbody class="divide-y dark:divide-gray-700">
					{foreach from=$tsTemas item=tema}
						<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
							<td class="px-3 py-2">{$tema.tid}</td>
							<td class="px-3 py-2 text-gray-600 dark:text-gray-400">
								<picture class="block shadow-sm rounded-lg" style="width:180px;height:100px;">
									<img src="{$tsRoutes.tema.images}/favicon.png" loading="lazy" data-src="{$tema.t_screen}" style="object-fit:cover;" class="w-full h-full" />
								</picture>
							</td>
							<td class="px-3 py-2">{$tema.t_name}</td>
							<td class="px-3 py-2">{$tema.t_copy}</td>
							<td class="px-3 py-2">
								<span class="inline-flex rounded-full{if $tsConfig.tema.t_path == $tema.t_path} bg-green-100 text-green-800{else} bg-purple-100 text-purple-800{/if} px-2 py-0.5 text-xs font-medium">{if $tsConfig.tema.t_path == $tema.t_path}Activo{else}Sin uso{/if}</span>
							</td>
							<td class="px-3 py-2">
								<div class="flex justify-center gap-2">
									{include "dashboard/table/Action.tpl" action="temas?act=editar&tid={$tema.tid}" title="Editar este tema" icon="edit"}
									{if $tsConfig.tema.t_path != $tema.t_path}
										{include "dashboard/table/Action.tpl" action="tema.usar({$tema.tid})" title="Usar este tema" icon="library_add_check" type="button"}
										{if $tema.tid != 1}
											{include "dashboard/table/Action.tpl" action="temas?act=borrar&tid={$tema.tid}&tt={$tema.t_name}" title="Borrar este tema" icon="delete"}
										{/if}
									{/if}
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
				{include "dashboard/table/Tfoot.tpl" span=6 link="tema.nuevo(false)" icon="add" text="Instalar nuevo tema" type="button"}
			</table>
		</div>
	{elseif $tsAct == 'editar'}
		<form method="post" autocomplete="off" class="space-y-6">

			{include "dashboard/FormGroup.tpl" id="path" label="Nombre del tema" name="path" value=$tsTema.t_path helper="Nombre de la carpeta donde esta el tema"}

			{include "dashboard/FormGroup.tpl" id="copy" label="Copyright" name="copy" value=$tsTema.t_copy helper="Por copyright no se pude modificar" disabled=true}

			{include "dashboard/FormGroup.tpl" id="url" label="Url completa del tema" name="url" value="{$tsConfig.url}/themes/{$tsTema.t_path}" disabled=true}

			<script>
				document.addEventListener('DOMContentLoaded', function () {
					const url = route.url + '/themes/';
					let val = $('#path');
					val.on('keyup', e => {
						let newurl = url + val.val();
						$('#url').val(newurl)
					})
				});
			</script>
			
			{include "dashboard/Button.tpl" submit_text="Guardar tema"}
		</form>
	{elseif $tsAct == 'borrar'}
		<form method="post" autocomplete="off">
			{if $tsAct == 'borrar'}
				{include "dashboard/Alert.tpl" text="Te recordamos que debes borrar la carpeta del Tema manualmente en el servidor." color="red" show=true}
			{/if}
			<h3 align="center">{$themeName}</h3>
			{include "dashboard/Button.tpl" submit_text="Continuar borrando este tema &raquo;" submit_name="confirm"}
		</form>
	{/if}
</div>