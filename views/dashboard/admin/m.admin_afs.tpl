<h1 class="text-xl font-semibold mb-4">Administrar Afiliados</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}
	
	{if !$tsAct}
		{if !$tsAfiliados}
			{include "dashboard/Alert.tpl" text="No hay afiliados." color="orange" show=true}
			<button onclick="afiliado.nuevo()" class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary">Agregar nuevo afiliado</button>
		{else}
			<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">
					{include "dashboard/table/Thead.tpl" fields=[
						"ID",
						"Afiliado",
						"Cuando",
						"Entrada",
						"Salida",
						"Estado",
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsAfiliados item=af}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="few_{$af.aid}">
								<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$af.aid}</td>
								<td class="px-3 py-2"><a href="{$af.a_url}" id="a_url_{$af.aid}" target="_blank"><span id="a_name_{$af.aid}">{$af.a_titulo}</span></a></td>
								<td class="px-3 py-2">{$af.a_date|hace:true}</td>
								<td class="px-3 py-2">{$af.a_hits_in}</td>
								<td class="px-3 py-2">{$af.a_hits_out}</td>
								<td class="px-3 py-2" id="status_afiliado_{$af.aid}">
									<span class="inline-flex rounded-full{if $af.a_active == 0} bg-purple-100 text-purple-800{else} bg-green-100 text-green-800{/if} px-2 py-0.5 text-xs font-medium">{if $af.a_active == 0}Inactiva{else}Activa{/if}</span>
								</td>
								<td class="px-3 py-2">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="afs?act=editar&aid={$af.aid}" title="Editar" icon="edit"}
										{include "dashboard/table/Action.tpl" action="afiliado.detalles({$af.aid})" type="button" title="Detalles" icon="assignment"}
										{include "dashboard/table/Action.tpl" action="afiliado.activar({$af.aid})" type="button" title="Activar/Desactivar Afiliado" icon="sync"}
										{include "dashboard/table/Action.tpl" action="afiliado.borrar({$af.aid})" type="button" title="Eliminar" icon="delete"}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					{include "dashboard/table/Tfoot.tpl" type="button" span=7 link="afiliado.nuevo()" icon="add" text="Agregar nuevo afiliado"}
				</table>
			</div>
		{/if}
	{elseif $tsAct == 'editar'}
		<form method="POST" autocomplete="off" class="space-y-6">
			<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">

				{include "dashboard/Legend.tpl" text="Editar afiliado"}

				{include "dashboard/FormGroup.tpl" id="a_titulo" label="T&iacute;tulo de afiliado" required=true name="a_titulo" value=$tsAfiliado.a_titulo}

				{include "dashboard/FormGroup.tpl" id="a_url" label="Direcci&oacute;n" required=true name="a_url" value=$tsAfiliado.a_url}

				{include "dashboard/FormGroup.tpl" id="a_banner" label="Banner" helper="Imagen del afiliado" required=true name="a_banner" value=$tsAfiliado.a_banner}

				{include "dashboard/FormGroupTextarea.tpl" id="a_descripcion" label="Descripci&oacute;n" helper="Descripci&oacute;n de la comunidad afiliada" name="a_descripcion" value=$tsAfiliado.a_descripcion}

				{include "dashboard/Button.tpl" submit_text="Guardar Cambios" back_url="/admin/afs"}
			</fieldset>
		</form>
	{/if}
</div>