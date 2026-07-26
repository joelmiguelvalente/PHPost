<h1 class="text-xl font-semibold mb-4">Proveedor de imagenes</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
    {include "dashboard/Alert.tpl" text="Configuraciones guardadas" color="green" show=$tsSave}

     {if $tsAct == ''}
        <section class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-surface p-5 shadow-sm">
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Si necesitas hacer un comunicado a todos los usuarios en general, desde aquí podrás administrar tus anuncios y los usuarios sin importar donde se encuentren navegando podrán visualizarlos.</p>
        </section>

        <hr class="my-3" />

        <h2 class="text-lg font-semibold mb-4">Lista de proveedores</h2>
        <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
            <table class="min-w-full border-collapse text-sm">
                {include "dashboard/table/Thead.tpl" fields=[
                    "ID",
                    "Proveedor",
                    "Api key",
                    "Activo",
                    "Acciones"
                ]}
                <tbody class="divide-y dark:divide-gray-700">
                    {foreach from=$tsProviders item=p}
                        <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$p.provider_id}</td>
                            <td class="px-3 py-2">{$p.provider_name}</td>
                            <td class="px-3 py-2">{$p.api_key}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-full{if $p.is_active == 0} bg-purple-100 text-purple-800{else} bg-green-100 text-green-800{/if} px-2 py-0.5 text-xs font-medium">{if $p.is_active == 0}Inactiva{else}Activa{/if}</span>
                            </td>

                            <td class="px-3 py-2">
                                <div class="flex justify-center gap-2">
                                    {include "dashboard/table/Action.tpl" action="imageprovider?act=editar&id={$p.provider_id}" title="Editar" icon="edit"}
                                    {include "dashboard/table/Action.tpl" action="imageprovider?act=activar&id={$p.provider_id}&api={$p.api_key}" title="Activar / Desactivar" icon="sync"}
                                    {include "dashboard/table/Action.tpl" action="imageprovider?act=borrar&id={$p.provider_id}" title="Borrar" icon="delete"}
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
                {include "dashboard/table/Tfoot.tpl" span=6 link="imageprovider?act=nuevo" icon="add" text="Nuevo proveedor"}
            </table>
        </div>
    {elseif $tsAct == 'editar' || $tsAct == 'nuevo'}
        <form method="POST" autocomplete="off" class="space-y-6">
            <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
                {include file="dashboard/Legend.tpl" text="{if $tsAct == 'nuevo'}Agregar nuevo{else}Editar{/if} proveedor"}
                {if $tsAct == 'editar'}
                    <input type="hidden" name="provider_id" value="{$tsProvider.provider_id}">
                {/if}

                {include "dashboard/FormGroup.tpl" id="name_provider" label="Nombre del proveedor" required=true name="provider_name" value=$tsProvider.provider_name placeholder="Imgur | ImgBB | Cloudinary"}

                {include "dashboard/FormGroup.tpl" id="apikey" label="API KEY" required=true name="api_key" value=$tsProvider.api_key placeholder="API KEY | mi_cloud:849302kdjf:s3cr3t_apisecret" helper="El único detalle de Cloudinary es que necesita 3 credenciales en lugar de una API key simple, eje: mi_cloud:849302kdjf:s3cr3t_apisecret"}

                {include "dashboard/FormGroup.tpl" type="radio" id="active" label="Activar proveedor" helper="Activar inmediatamente este proveedor." name="is_active" checked=$tsProvider.is_active labels=["Sí", "No"] values=[1,0]}

                {include "dashboard/Button.tpl" submit_text="{if $tsAct == 'nuevo'}Agregar nuevo{else}Guardar Cambios{/if}"}
            </fieldset>
        </form>
    {else}
        <form method="POST" autocomplete="off" class="space-y-6">
            <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
                {include file="dashboard/Legend.tpl" text="Eliminar proveedor"}
                <input type="hidden" name="provider_id" value="{$tsProvider.provider_id}">

                <section class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-surface p-5 shadow-sm">
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Estas completamente seguro de que quieres eliminar el proveedor <strong>{$tsProvider.provider_name}</strong>.</p>
                </section>

                {include "dashboard/Button.tpl" submit_text="Eliminar ahora..."}
            </fieldset>
        </form>
    {/if}
</div>
