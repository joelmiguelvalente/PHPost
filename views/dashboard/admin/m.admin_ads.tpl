<h1 class="text-xl font-semibold mb-4">{if $tsAct == 'editar'}Editar{else}Administrar{/if} Publicidad</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{if !$tsAct}
	    <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
	        <table class="min-w-full border-collapse text-sm">
               	{include "dashboard/table/Thead.tpl" fields=[
                	"Espacio",
                	"Título",
                	"Estado",
                	"Orden",
                	""
               	]}
	            <tbody class="divide-y dark:divide-gray-700">
	                {foreach $tsAds as $ad}
	                    <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
	                        <td class="px-3 py-2">{$ad.nombre}</td>
	                        <td class="px-3 py-2">{$ad.titulo}</td>
	                        <td class="px-3 py-2">
	                            {if $ad.activo}
	                                <span class="text-green-600">Activo</span>
	                            {else}
	                                <span class="text-red-600">Inactivo</span>
	                            {/if}
	                        </td>
	                        <td class="px-3 py-2">{$ad.orden}</td>
	                        <td class="text-right">
	                        	<div class="flex justify-center gap-2">
	                        		{include "dashboard/table/Action.tpl" action="ads?act=editar&id={$ad.ads_id}" title="Editar publicidad" icon="edit"}
	                        	</div>
	                        </td>
	                    </tr>
	                {/foreach}
	            </tbody>
	        </table>
	    </div>
	{elseif $tsAct == 'editar'}
	    <div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">

	        {include "dashboard/Alert.tpl" text="Los cambios se han guardado correctamente." color="green" show=$tsSave}

	        <form method="post" autocomplete="off" class="space-y-6">

	            <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">

	                {include "dashboard/Legend.tpl" text="Información"}

	                {include "dashboard/FormGroup.tpl" id="nombre" label="Nombre" value=$tsAd.nombre readonly=true}

	                {include "dashboard/FormGroup.tpl" id="titulo" label="Título" name="titulo" value=$tsAd.titulo}

	                {include "dashboard/FormGroup.tpl" id="orden" type="number" label="Orden" name="orden" value=$tsAd.orden}

	                {include "dashboard/Check.tpl" type="checkbox" checked=$tsAd.activo label="Publicidad activa" name="activo" class="mb-3"}

	            </fieldset>

	            <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
	                {include "dashboard/Legend.tpl" text="Código"}
	                {include "dashboard/FormGroupTextarea.tpl" id="codigo" label="HTML / JavaScript" name="codigo" value=$tsAd.codigo rows=12}
	            </fieldset>
	            {include "dashboard/Button.tpl" submit_text="Guardar Cambios"}
	        </form>

	    </div>

	{/if}
</div>
