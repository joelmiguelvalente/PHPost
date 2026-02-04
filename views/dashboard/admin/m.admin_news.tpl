<h1 class="text-xl font-semibold mb-4">Noticias</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}
	{include "dashboard/Alert.tpl" text="Noticia eliminada." color="red" show=($tsDelete == 'true')}

	{if $tsAct == ''}
		<section class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-surface p-5 shadow-sm">
			<p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Si necesitas hacer un comunicado a todos los usuarios en general, desde aqu&iacute; podr&aacute;s administrar tus anuncios y los usuarios sin importar donde se encuentren navegando podr&aacute;n visualizarlos.</p>
		</section>

		<hr class="my-3" />

		<h2 class="text-lg font-semibold mb-4">Lista de noticias</h2>
		<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
			<table class="min-w-full border-collapse text-sm">
				{include "dashboard/table/Thead.tpl" fields=[
					"ID",
					"Noticia",
					"Autor",
					"Fecha",
					"Estado",
					"Acciones"
				]}
				<tbody class="divide-y dark:divide-gray-700">
					{foreach from=$tsNews item=n}
						<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
							<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$n.not_id}</td>
							<td class="px-3 py-2">{$n.not_body}</td>
							<td class="px-3 py-2">
								<a href="{$tsConfig.url}/perfil/{$n.user_name}" class="text-primary hover:underline hovercard">{$n.user_name}</a>
							</td>
							<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$n.not_date|hace:true}</td>
							<td class="px-3 py-2" id="status_noticia_{$n.not_id}">
								<span class="inline-flex rounded-full{if $n.not_active == 0} bg-purple-100 text-purple-800{else} bg-green-100 text-green-800{/if} px-2 py-0.5 text-xs font-medium">{if $n.not_active == 0}Inactiva{else}Activa{/if}</span>
							</td>

							<td class="px-3 py-2">
								<div class="flex justify-center gap-2">
									{include "dashboard/table/Action.tpl" action="news?act=editar&nid={$n.not_id}" title="Editar" icon="edit"}
									{include "dashboard/table/Action.tpl" action="noticias({$n.not_id})" type="button" title="Activar / Desactivar" icon="sync"}
									{include "dashboard/table/Action.tpl" action="news?act=borrar&nid={$n.not_id}" title="Borrar" icon="delete"}
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
				{include "dashboard/table/Tfoot.tpl" span=6 link="news?act=nueva" icon="add" text="Nueva noticia"}
			</table>
		</div>
	{elseif $tsAct == 'nueva' || $tsAct == 'editar'}
		<form method="post" autocomplete="off" class="space-y-6">
			<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
				{include file="dashboard/Legend.tpl" text="{if $tsAct == 'nueva'}Agregar nueva{else}Editar{/if} noticia"}

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
			      <div>
			        	<label for="not_body" class="font-medium text-gray-700 dark:text-gray-300">Noticia</label>
			        	<p class="mt-1 text-xs text-gray-500">Puedes utilizar los siguentes BBCodes [url], [i] [b] y [u]. El m&aacute;ximo de caracteres permitidos es de <b>190</b>.</p>
			      </div>
			      <div class="md:col-span-2 space-y-3">
			      	<textarea name="not_body" id="not_body" rows="3" cols="50" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">{$tsNew.not_body}</textarea>
			      </div>
			   </div>

				{include "dashboard/FormGroup.tpl" type="radio" id="not_active" label="Activar noticia" helper="Activar inmediatamente esta noticia." name="not_active" checked=$tsNew.not_active labels=["Sí", "No"] values=[1,0]}

				{include "dashboard/FormGroup.tpl" type="radio" id="not_type" label="Tipo de noticia" helper="Importancia de la noticia." name="not_type" checked=$tsNew.not_type labels=["Normal", "Importante", "Cambios"] values=[0,1,2]}

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
			      <div>
			        	<label for="not_body" class="font-medium text-gray-700 dark:text-gray-300">Color de noticia</label>
			        	<p class="mt-1 text-xs text-gray-500">El tipo de color para la noticia.</p>
			      </div>
			      <div class="md:col-span-2 space-y-3">
						{include "dashboard/Select.tpl" id="not_color" name="not_color" options=[
						   ['value'=>'info',      'label'=>'Info'],
						   ['value'=>'success',   'label'=>'Éxito'],
						   ['value'=>'warning',   'label'=>'Alerta'],
						   ['value'=>'danger',    'label'=>'Error'],
						   ['value'=>'primary',   'label'=>'Primario'],
						   ['value'=>'secondary', 'label'=>'Secundario']
						] selected=$tsNew.not_color}
			      </div>
			   </div>

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
			      <div>
			        	<label for="not_body" class="font-medium text-gray-700 dark:text-gray-300">Expiración de la noticia</label>
			        	<p class="mt-1 text-xs text-gray-500">Cuanto tiempo durará la noticia.</p>
			      </div>
			      <div class="md:col-span-2 space-y-3">
						{include "dashboard/Select.tpl" id="not_expires" name="not_expires" options=[
						   ['value'=> 0,   'label'=>'Siempre'],
						   ['value'=> 1,   'label'=>'Un día'],
						   ['value'=> 2,   'label'=>'Una Semana'],
						   ['value'=> 3,   'label'=>'Un mes']
						] selected=$tsNew.not_expires}
			      </div>
			   </div>
				{include "dashboard/Button.tpl" submit_text="{if $tsAct == 'new'}Agregar noticia{else}Guardar Cambios{/if}"}
			</fieldset>
		</form>
	
	{elseif $tsAct == 'borrar'}                                   
		<form method="post" id="admin_form" autocomplete="off" class="space-y-6">
			<fieldset>
				<div class="flex items-center">
					<span>¿Desea eliminar la noticia definitivamente?</span>
				</div>
				{include "dashboard/Button.tpl" submit_text="Volver" submit_name="confirmar"}
			</fieldset>
		</form>
	{/if}
</div>