<h1 class="text-xl font-semibold mb-4">Censurar palabras</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}
	{include "dashboard/Alert.tpl" text=$tsError color="red" show=$tsError}

	{if !$tsAct}
		{if !$tsBadWords.data}
			{include "dashboard/Alert.tpl" text="No hay filtros de palabras" color="orange" show=true}
			<a href="{$tsConfig.url}/admin/badwords?act=nuevo" class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary" title="Agregar nuevo filtro">Agregar nuevo filtro</a>	
		{else}
			<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">
					{include "dashboard/table/Thead.tpl" fields=[
						"ID",
						"Método",
						"Tipo",
						"Antes",
						"Después",
						"Razón",
						"Autor",
						"Fecha",
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsBadWords.data item=b}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="wid_{$b.wid}">
								<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$b.wid}</td>
								<td class="px-3 py-2">{if $b.method == 1}Exacto{else}Parcial{/if}</td>
								<td class="px-3 py-2">{if $b.type == 1}Smiley{else}Texto{/if}</td>
								<td class="px-3 py-2">{$b.word}</td>
								<td class="px-3 py-2">{if $b.type == 1}<img src="{$b.swop}" style="max-width:32px; max-height:32px;"/>{else}{$b.swop}{/if}</td>
								<td class="px-3 py-2">{$b.reason}</td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/@{$b.user_name}" >{$b.user_name}</a></td>
								<td class="px-3 py-2">{$b.date|hace:true}</td>
								<td class="px-3 py-2">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="badwords?act=editar&id={$b.wid}" title="Editar" icon="edit"}
										{include "dashboard/table/Action.tpl" action="badwords.borrar({$b.wid})" type="button" title="Eliminar" icon="delete"}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					{include "dashboard/table/Tfoot.tpl" span=9 link="badwords?act=nuevo" icon="add" text="Agregar nuevo filtro" pages=$tsBadWords.pages}
				</table>
			</div>
		{/if}
	{elseif $tsAct == 'editar' || $tsAct == 'nuevo'}
		<form method="POST" autocomplete="off" class="space-y-6">
			<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
				{include "dashboard/Legend.tpl" text="{if $tsAct == 'editar'}Editar{else}Agregar{/if} filtro de palabra"}

				{include "dashboard/Alert.tpl" text="El método exacto filtra sólo palabras completas, mientras que el parcial filtra todas las coincidencias, aunque forme parte de una palabra. Si opta por usar un smiley, introduzca el enlace directo hacia la imagen." color="orange" show=true}

				<!-- before -->
				{include "dashboard/FormGroup.tpl" id="word" label="Antes" name="word" value=$tsBadWord.word}
								
				<!-- after -->
				{include "dashboard/FormGroup.tpl" id="swop" label="Después" name="swop" value=$tsBadWord.swop}
				
				{include "dashboard/FormGroup.tpl" type="radio" id="method" label="Método" name="method" checked=$tsBadWord.method labels=["Parcial", "Exacto"] values=[0,1]}

				{include "dashboard/FormGroup.tpl" type="radio" id="type" label="Tipo" name="type" checked=$tsBadWord.type labels=["Texto", "Smiley"] values=[0,1]}

				{if $tsAct == 'nuevo'}
					{include "dashboard/FormGroupTextarea.tpl" id="reason" label="Razón" helper="Indica el motivo por el cual quiere agregar este filtro." name="reason" value=$tsBadWord.reason}
				{/if}
				
				{include "dashboard/Button.tpl" submit_text="{if $tsAct == 'editar'}Guardar{else}Agregar{/if}"}
			</fieldset>
		</form>
	{/if}
</div>
