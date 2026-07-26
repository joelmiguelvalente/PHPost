{if $tsAct == ''}
   <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
{/if}
{if $tsAct == '' || $tsAct == 'editar' || $tsAct == 'nueva'}
<script>
document.addEventListener('DOMContentLoaded', function () {
   /* {if $tsAct == ''} */
   new Sortable(document.getElementById('cats_orden'), {
      animation: 150,
      dragClass: "arrastrar",
      selectedClass: 'seleccionado',
      fallbackTolerance: 3,
      multiDrag: true,
      store: {
         // Guardar orden
         set: sortable => $.post(route.url + '/admin-ordenar-categorias', 'cats=' + sortable.toArray().join(','))
      }
   });
   /* {/if} */
   $('#c_img').on('change', function () {
      const icon = $(this).val();
      $('#c_icon').css({
         background: "url('{$tsRoutes.assets.images}/icons/cat/" + icon + "') no-repeat center center",
         backgroundSize: '16px'
      });
   });
});
</script>
{/if}
<h1 class="text-xl font-semibold mb-4">Administrar Categorías</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
   {include "dashboard/Alert.tpl" text="Tus cambios han sido guardados" color="green" show=$tsSave}
   {if $tsAct == ''}
      {if !$tsSave}
         {include "dashboard/Alert.tpl" text="Puedes cambiar el orden de las categorías tan sólo con arrastrarlas con el puntero." color="orange" show=true}
      {/if}
      <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
         <table class="min-w-full border-collapse text-sm">
               {include "dashboard/table/Thead.tpl" fields=[
                  "Orden",
                  "Imagen",
                  "Nombre",
                  "Slug",
                  "Privado",
                  "Acciones"
               ]}
            <tbody class="divide-y dark:divide-gray-700" id="cats_orden">
               {foreach from=$tsCategories item=c}
                  <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="{$c.cid}" data-id="{$c.cid}">
                     <td class="px-3 py-2 text-gray-600 dark:text-gray-400" width="30">{$c.c_orden}</td>
                     <td class="px-3 py-2"><img src="{$tsRoutes.assets.images}/icons/cat/{$c.c_img}" alt="{$c.c_nombre}"></td>
                     <td class="px-3 py-2 font-bold">{$c.c_nombre}</td>
                     <td class="px-3 py-2">{$c.c_seo}</td>
                     <td class="px-3 py-2"><span class="material-symbols-outlined text-sm">lock{if $c.c_privada == 0}_open_right{/if}</span></td>
                     <td class="px-3 py-2">
                        <div class="flex justify-center gap-2">
                           {include "dashboard/table/Action.tpl" action="cats?act=editar&cid={$c.cid}" title="Editar Categoría" icon="edit"}
                           {include "dashboard/table/Action.tpl" action="cats?act=borrar&cid={$c.cid}" title="Borrar Categoría" icon="delete"}
                        </div>
                     </td>
                  </tr>
               {/foreach}
            </tbody>
         </table>
      </div>

      <div class="flex space-x-4 py-4">
         <a href="{$tsConfig.url}/admin/cats?act=nueva" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm">add</span> Agregar Nueva Categoría</a>

         <a href="{$tsConfig.url}/admin/cats?act=change" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm">sync_alt</span> Mover Posts</a>
      </div>							
	{elseif $tsAct == 'editar' || $tsAct == 'nueva'}
      <form method="post" autocomplete="off" class="space-y-6">
         <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">

            {include file="dashboard/Legend.tpl" text="{if $tsAct == 'nueva'}Agregar nueva{else}Editar{/if} categoria"}

            {include "dashboard/Alert.tpl" text="Si deseas más iconos para las categorías debes subirlos al directorio: {$tsRoutes.assets.images}/cat/" color="orange" show=true}

            {include "dashboard/FormGroup.tpl" id="c_nombre" label="Nombre de la categoría" required=true name="c_nombre" value=$tsCat.c_nombre}

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-2 mb-3">
               <label for="c_img" class="font-medium text-gray-700 dark:text-gray-300">Icono de la categoría</label>
               <div class="md:col-span-2 flex items-center">
                  <div style="background:url({$tsRoutes.assets.images}/icons/cat/{if $tsCat.c_img == ''}book.png{else}{$tsCat.c_img}{/if}) no-repeat center center;background-size:16px;display:block;width:16px;height:16px;margin-right:10px;" id="c_icon"></div>
                  <select name="c_img" id="c_img" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary" style="width: 160px;">
                     {html_options values=$tsIcons output=$tsIcons selected=$tsCat.c_img}
                  </select>
               </div>
            </div>

            {include "dashboard/FormGroup.tpl" type="radio" id="c_privada" label="La categoría es..." name="c_privada" checked=$tsCat.c_privada labels=["Pública", "Privada"] values=[0,1]}

            {include "dashboard/Button.tpl" submit_text="{if $tsAct == 'nueva'}Crear categoria{else}Guardar cambios{/if}"}
         </fieldset>
      </form>
   {elseif $tsAct == 'borrar'}
      {if $tsError}<div class="mensajes error">{$tsError}</div>{/if}
      {if $tsType == 'cat'}
         <form action="" method="post" id="admin_form">
            <label for="h_mov" style="width:500px;">Borrar categoría y mover las subcategorías y demas datos a otra categoría diferente. Mover datos a:</label>
            <select name="ncid">
             	<option value="-1">Categorías</option>
             	{foreach from=$tsConfig.categorias item=c}
                  {if $c.cid != $tsCID}
                 	   <option value="{$c.cid}">{$c.c_nombre}</option>
                  {/if}
               {/foreach}
            </select>
            <hr />
            <input type="submit" name="save" value="Guardar cambios" class="mBtn btnOk">
         </form>
      {/if}
	{elseif $tsAct == 'change'}
      {if $tsError}<div class="mensajes error">{$tsError}</div>{/if}
      <form action="" method="post" id="admin_form">
         <label style="width:500px;">Mover todos los posts de la categoría </label>
         <select name="oldcid">
            <option value="-1">Categorías</option>
            {foreach from=$tsConfig.categorias item=c}
               {if $c.cid != $tsCID}
                  <option value="{$c.cid}">{$c.c_nombre}</option>
               {/if}
            {/foreach}
         </select>
			<label style="width:500px;"> a </label>
			<select name="newcid">
            <option value="-1">Categorías</option>
            {foreach from=$tsConfig.categorias item=c}
               {if $c.cid != $tsCID}
                  <option value="{$c.cid}">{$c.c_nombre}</option>
               {/if}
            {/foreach}
         </select>
         <hr />
         <input type="submit" name="save" value="Guardar cambios" class="mBtn btnOk">
      </form>
   {/if}
</div>
