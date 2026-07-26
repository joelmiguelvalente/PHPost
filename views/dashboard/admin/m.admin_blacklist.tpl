<h1 class="text-xl font-semibold mb-4">Administrar Lista Negra</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}
	{include "dashboard/Alert.tpl" text=$tsError color="red" show=$tsError}

	{if !$tsAct}
		{if !$tsBlackList.data}
			{include "dashboard/Alert.tpl" text="No hay nada en tu lista negra." color="orange" show=true}
			<a href="{$tsConfig.url}/admin/blacklist?act=nuevo" class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary" title="Agregar nuevo bloqueo">Agregar nuevo bloqueo</a>			
		{else}
			<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">
					{include "dashboard/table/Thead.tpl" fields=[
						"ID",
						"Tipo",
						"Texto",
						"Razón",
						"Autor",
						"Fecha",
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsBlackList.data item=b}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="block_{$b.id}">
								<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$b.id}</td>
								<td class="px-3 py-2">{if $b.type == 1}IP{elseif $b.type == 2}Email{elseif $b.type == 3}Proveedor{elseif $b.type == 4}Nombre{else}Indefinido{/if}</td>
								<td class="px-3 py-2">{$b.value}</td>
								<td class="px-3 py-2">{$b.reason}</td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/@{$b.user_name}" class="hovercard" uid="{$b.user_id}">{$b.user_name}</a></td>
								<td class="px-3 py-2">{$b.date|hace:true}</td>
								<td class="px-3 py-2">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="blacklist?act=editar&id={$b.id}" title="Editar" icon="edit"}
										{include "dashboard/table/Action.tpl" action="blacklist.borrar({$b.id})" type="button" title="Eliminar" icon="delete"}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					{include "dashboard/table/Tfoot.tpl" span=7 link="blacklist?act=nuevo" icon="add" text="Agregar nuevo bloqueo" pages=$tsBlackList.pages}
				</table>
			</div>
		{/if}
	{elseif $tsAct == 'editar' || $tsAct == 'nuevo'}
		<form method="POST" autocomplete="off" class="space-y-6">
			<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
				{include "dashboard/Legend.tpl" text="{if $tsAct == 'editar'}Editar{else}Agregar{/if} bloqueo"}

				{include "dashboard/Alert.tpl" text="Para bloquear correos masivos como <kdb>ejemplo@yopmail.com</kdb>, seleccione '<strong>proveedor de correo</strong>' e introduzca <kdb>yopmail.com</kdb> en valor." color="orange" show=true}

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
			      <div>
			        	<label for="type" class="font-medium text-gray-700 dark:text-gray-300">Tipo</label>
			        	<p class="mt-1 text-xs text-gray-500">Hace el sitio inaccesible y muestra un mensaje opcional.</p>
			      </div>
			      <div class="md:col-span-2 space-y-3">
						{include "dashboard/Select.tpl" id="type" name="type" options=[
						   ['value'=> 1, 'label'=> 'IP'],
						   ['value'=> 2, 'label'=> 'Email concreto'],
						   ['value'=> 3, 'label'=> 'Proveedor de correo'],
						   ['value'=> 4, 'label'=> 'Nombre'],
						] selected=$tsBloqueo.type}
			      </div>
			   </div>

				{include "dashboard/FormGroup.tpl" id="value" label="Valor" name="value" value=$tsBloqueo.value}
												
				{include "dashboard/FormGroupTextarea.tpl" id="reason" label="Razón" helper="Indica el motivo por el cual quiere agregarlo a la lista negra" name="reason" value=$tsBloqueo.reason}
				
				{include "dashboard/Button.tpl" submit_text="{if $tsAct == 'editar'}Guardar{else}Agregar{/if}"}
			</fieldset>
		</form>
	{/if}
</div>
