<h1 class="text-xl font-semibold mb-4">Administrar Rangos de Usuarios</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
   {include "dashboard/Alert.tpl" text="Tus cambios han sido guardados" color="green" show=$tsSave}
   {include "dashboard/Alert.tpl" text=$tsError color="red" show=$tsError}
   {if $tsAct == ''}
      <div class="mb-3">
         <h3 class="py-3 text-lg font-bold">Rangos Especiales</h3>
         
         <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
            <table class="min-w-full border-collapse text-sm">
               {include "dashboard/table/Thead.tpl" fields=[
                  "Rango",
                  "Usuarios",
                  "Puntos para dar",
                  "Puntos por post",
                  "Imagen",
                  "Acciones"
               ]}
               <tbody class="divide-y dark:divide-gray-700">
                  {foreach from=$tsRangos.regular item=r}
                     <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400"><a href="{$tsConfig.url}/admin/rangos?act=list&rid={$r.id}&t=r" style="color:#{$r.color}">{$r.name}</a></td>
                        <td class="px-3 py-2 text-gray-600">{$r.num_members}</td>
                        <td class="px-3 py-2 text-gray-600">{$r.user_puntos}</td>
   							<td class="px-3 py-2 text-gray-600">{$r.max_points}</td>
                        <td class="px-3 py-2 text-gray-600"><img src="{$tsRoutes.assets.images}/icons/ranks/{$r.imagen}" /></td>
                        <td class="px-3 py-2">
                           <div class="flex justify-center gap-2">
                              {include "dashboard/table/Action.tpl" action="rangos?act=editar&rid={$r.id}&type=special" title="Editar Rango" icon="edit"}
                              {if $r.id > 3}
                                 {include "dashboard/table/Action.tpl" action="rangos?act=borrar&rid={$r.id}" title="Borrar Rango" icon="delete"}
                              {/if}
                              {if $tsPredeterminado == $r.id}
                                 <span class="material-symbols-outlined" title="Rango Predeterminado al registro">library_add_check</span>
                              {else}
                                 {include "dashboard/table/Action.tpl" action="rangos?act=setdefault&rid={$r.id}" title="Establecer Predeterminado" icon="published_with_changes"}
                              {/if}
                           </div>
                        </td>
                     </tr>
                  {/foreach}
               </tbody>
               {include "dashboard/table/Tfoot.tpl" span=6 link="rangos?act=nuevo&type=special" text="Agregar nuevo rango &raquo;"}
            </table>
         </div>
      </div>
      <div>
         <h3 class="py-3 text-lg font-bold">Rangos basados en el conteo de puntos y posts</h3>
         
         <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
            <table class="min-w-full border-collapse text-sm">
               {include "dashboard/table/Thead.tpl" fields=[
                  "Rango",
                  "Usuarios",
                  "Tipo",
                  "Cantidad requerida",
                  "Puntos para dar",
                  "Puntos por post",
                  "Imagen",
                  "Acciones"
               ]}
               <tbody class="divide-y dark:divide-gray-700">
                  {foreach from=$tsRangos.post item=r}
                     <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400"><a href="{$tsConfig.url}/admin/rangos?act=list&rid={$r.id}&t=p" style="color:#{$r.color}">{$r.name}</a></td>
                        <td class="px-3 py-2">{$r.num_members}</td>
                        <td class="px-3 py-2">{if $r.type == 1}Puntos{elseif $r.type == 2}Posts{elseif $r.type == 3}Fotos{elseif $r.type == 4}Comentarios{/if}</td>
                        <td class="px-3 py-2">{$r.cant}</td>
                        <td class="px-3 py-2">{$r.user_puntos}</td>
   							<td class="px-3 py-2">{$r.max_points}</td>
                        <td class="px-3 py-2"><img src="{$tsRoutes.assets.images}/icons/ranks/{$r.imagen}" /></td>
                        <td class="px-3 py-2">
                           <div class="flex justify-center gap-2">
                              {include "dashboard/table/Action.tpl" action="rangos?act=editar&rid={$r.id}&type=counted" title="Editar Rango" icon="edit"}
                              {if $r.id > 3}
                                 {include "dashboard/table/Action.tpl" action="rangos?act=borrar&rid={$r.id}" title="Borrar Rango" icon="delete"}
                              {/if}
                           </div>
                        </td>
                     </tr>
                  {/foreach}
               </tbody>
               {include "dashboard/table/Tfoot.tpl" span=8 link="rangos?act=nuevo&type=counted" text="Agregar nuevo rango &raquo;"}
            </table>
         </div>
   {elseif $tsAct == 'list'}
      {if !$tsMembers.data}
         <div class="mensajes error">Aun no hay usuarios en este rango.</div>
      {else}
         <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
            <table class="min-w-full border-collapse text-sm">
               {include "dashboard/table/Thead.tpl" fields=[
                  "Usuario",
                  "Email",
                  "&Uacute;ltima vez activo",
                  "Fecha de registro",
                  "Acciones"
               ]}
               <tbody class="divide-y dark:divide-gray-700">
               {foreach from=$tsMembers.data item=m}
                  <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
                     <td class="px-3 py-2 text-gray-600 dark:text-gray-400"><a href="{$tsConfig.url}/perfil/{$m.user_name}" style="color:#{$m.r_color};">{$m.user_name}</a></td>
                     <td class="px-3 py-2">{$m.user_email}</td>
                     <td class="px-3 py-2">{$m.user_lastlogin|hace:true}</td>
                     <td class="px-3 py-2">{$m.user_registro|fecha:'full_datetime'}</td>
                     <td class="px-3 py-2">
                        <div class="flex justify-center gap-2">
                           {include "dashboard/table/Action.tpl" action="users?act=show&uid={$m.user_id}&t=7" title="Editar Rango" icon="edit"}
                        </div>
                     </td>
                  </tr>
               {/foreach}
            </tbody>
            {include "dashboard/table/Tfoot.tpl" span=6 pages=$tsMembers.pages}
         </table>
      {/if}
   {elseif $tsAct == 'nuevo' || $tsAct == 'editar'}
      <form method="POST" autocomplete="off" class="space-y-6">
         <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
            {include "dashboard/Legend.tpl" text="Nuevo Rango"}
            <div class="flex justify-center items-center gap-3" id="newRank">
               <span role="button" class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none" data-target="basico">B&aacute;sico</span>
               <span role="button" class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none" data-target="permisos">Permisos</span>
            </div>

            <div id="basico">
               {include "dashboard/FormGroup.tpl" id="rName" label="Nombre del rango" required=true name="r_name" value=$tsRango.r_name}

               {include "dashboard/FormGroup.tpl" id="rColor" label="Color hexadecimal del rango" name="r_color" value="#{$tsRango.r_color|default:'000000'}" type="color"}

               {include "dashboard/FormGroup.tpl" id="gopfd" label="Puntos por d&iacute;a" helper="Puntos que puede otorgar este rango a otros usuarios al d&iacute;a." type="number" name="global-pointsforday" value=$tsRango.permisos.gopfd}

               {include "dashboard/FormGroup.tpl" id="gopfp" label="Puntos por post" helper="Puntos que puede dar en cada post." type="number" name="global-pointsforposts" value=$tsRango.permisos.gopfp}

               {include "dashboard/FormGroup.tpl" id="goaf" label="Anti-flood" helper="Tiempo que deben esperar entre acci&oacute;n." type="number" name="global-antiflood" value=$tsRango.permisos.goaf}
               
               {if $tsType != 'special'}
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
                     <div>
                        <label for="gocpr" class="font-medium text-gray-700 dark:text-gray-300">Condici&oacute;n especial:</label>
                        <p class="mt-1 text-xs text-gray-500">Cantidad requerida para obtener el rango. Elija especial si s&oacute;lo es asignado por un administrador.</p>
                     </div>
                     <div class="md:col-span-2 space-y-3">
                        <div class="flex gap-6 inputsRadios">
                           {include "dashboard/Check.tpl" type="radio" value=1 checked=$tsRango.r_type label="Puntos" name="global-type"}
                           {include "dashboard/Check.tpl" type="radio" value=2 checked=$tsRango.r_type label="Posts" name="global-type"}
                           {include "dashboard/Check.tpl" type="radio" value=3 checked=$tsRango.r_type label="Fotos" name="global-type"}
                           {include "dashboard/Check.tpl" type="radio" value=4 checked=$tsRango.r_type label="Comentarios" name="global-type"}
                        </div>
                        {include "dashboard/Input.tpl" name="global-cantidadrequerida" id="gocpr" value=$tsRango.r_cant}
                     </div>
                  </div>
               {else}
                  <input name="global-type" type="hidden" value="0"/>
               {/if}
               
               <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
                  <div>
                     <label for="c_img" class="font-medium text-gray-700 dark:text-gray-300">Icono del rango</label>
                  </div>
                  <script>
                     document.addEventListener('DOMContentLoaded', function () {
                        $('#c_img').on('change', function () {
                           const icon = $(this).val();
                           $('#c_icon').css({
                              background: "url('{$tsRoutes.assets.images}/icons/ranks/" + icon + "') no-repeat center center",
                              backgroundSize: '16px'
                           });
                        });
                     });
                  </script>
                  <div class="md:col-span-2 space-y-3 flex justify-start items-center">
                     <div style="background:url({$tsRoutes.assets.images}/icons/ranks/{if $tsRango.r_image}{$tsRango.r_image}{else}{$tsIcons.0}{/if}) no-repeat center center;background-size:16px;display:block;width:16px;height:16px;margin-right:10px;" id="c_icon"></div>
                     <select name="r_img" id="c_img" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        {foreach from=$tsIcons key=i item=img}
                           <option value="{$img}"{if $tsRango.r_image == $img} selected{/if}>{$img}</option>
                        {/foreach}
                     </select>
                  </div>
               </div>
              
               <input type="button" id="next" class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary" value="Continuar" data-target="permisos"/>
				</div>		
            <div id="permisos" style="display: none;">
               <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm mb-4">
                  {include "dashboard/Legend.tpl" text="Super Moderaci&oacute;n"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.suad label="Super Admin" helper="Si marca esto, los permisos p&uacute;blicos, de administraci&oacute;n y de moderaci&oacute;n estar&aacute;n inclu&iacute;dos." name="superadmin" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.sumo label="Super Moderador" helper="Si marca esto, todos los permisos p&uacute;blicos y de moderaci&oacute;n estar&aacute;n inclu&iacute;dos." name="supermod" class="mb-3"}
                 
					</fieldset>
               <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm mb-4">
                  {include "dashboard/Legend.tpl" text="Global"}
                  
                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.gopp label="Publicar Posts" helper="Podr&aacute;n publicar posts." name="global-publicarposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.godp label="Puntuar Posts" helper="Podr&aacute;n puntuar posts." name="global-darpuntos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.gopcp label="Publicar Comentarios en Posts" helper="Podr&aacute;n publicar comentarios posts." name="global-publicarcomposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.govpp label="Votar positivo" helper="Podr&aacute;n votar positivamente comentarios de posts." name="global-votarposipost" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.govpn label="Votar negativo" helper="Podr&aacute;n votar negativamente comentarios de posts." name="global-votarnegapost" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.goepc label="Editar comentarios propios" helper="Podr&aacute;n editar los comentarios que ellos hacen." name="global-editarpropioscomentarios" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.godpc label="Eliminar comentarios propios" helper="Podr&aacute;n eliminar los comentarios que ellos hacen." name="global-eliminarpropioscomentarios" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.gopf label="Publicar Fotos" helper="Podr&aacute;n publicar fotos." name="global-publicarfotos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.gopcf label="Publicar Comentarios en Fotos" helper="Podr&aacute;n publicar comentarios en fotos." name="global-publicarcomfotos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.gorpap label="Revisar Posts" helper="Si marca esto, cuando publiquen un post, antes de ser p&uacute;blico ser&aacute;n revisados." name="global-revisarposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.govwm label="Acceso en mantenimiento" helper="Podr&aacute;n navegar normalmente mientras la web est&aacute; en mantenimiento." name="global-vermantenimiento" class="mb-3"}
               </fieldset>
					<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm mb-4">
                  {include "dashboard/Legend.tpl" text="Panel de Moderaci&oacute;n"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moacp label="Acceso al Panel de Moderaci&oacute;n" helper="Podr&aacute;n entrar al panel de moderaci&oacute;n y ver posts y fotos denunciadas." name="mod-accesopanel" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.movub label="Usuarios baneados" helper="Podr&aacute;n ver usuarios baneados." name="mod-verusuariosbaneados" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moub label="Usar el buscador" helper="Podr&aacute;n usar el buscador de contenidos." name="mod-usarbuscador" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.morp label="Papelera de posts" helper="Podr&aacute;n ver la papelera de reciclaje de posts y los posts eliminados." name="mod-reciclajeposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.morf label="Papelera de fotos" helper="Podr&aacute;n ver la papelera de reciclaje de fotos y las fotos eliminadas." name="mod-reficlajefotos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocp label="Posts desaprobados" helper="Podr&aacute;n ver la secci&oacute;n y los posts ocultos." name="mod-contenidoposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocc label="Comentarios desaprobados" helper="Podr&aacute;n ver los comentarios ocultos." name="mod-contenidocomentarios" class="mb-3"}

                  <fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
                     {include "dashboard/Legend.tpl" text="Panel de Moderaci&oacute;n"}

                     {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocdu label="Cancelar denuncias de usuarios" helper="Podr&aacute;n ver y cancelar reportes de usuarios." name="mod-cancelardenunciasusuarios" class="mb-3"}

                     {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocdf label="Cancelar denuncias de fotos" helper="Podr&aacute;n rechazar reportes de fotos." name="mod-cancelardenunciasfotos" class="mb-3"}

                     {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocdp label="Cancelar denuncias de posts" helper="Podr&aacute;n rechazar reportes de posts." name="mod-cancelardenunciasposts" class="mb-3"}

                     {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moadm label="Aceptar denuncias de mensajes" helper="Podr&aacute;n aceptar reportes de mensajes." name="mod-aceptardenunciasmensajes" class="mb-3"}

                     {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocdm label="Cancelar denuncias de mensajes" helper="Podr&aacute;n rechazar reportes de mensajes." name="mod-cancelardenunciasmensajes" class="mb-3"}
                  </fieldset>
					</fieldset>
					<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm mb-4">
                  {include "dashboard/Legend.tpl" text="Moderaci&oacute;n Parcial"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.most    label="Fijar Posts" helper="Podr&aacute;n poner/quitar posts en sticky desde el formulario y el mismo post." name="mod-sticky" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moayca  label="Abrir/Cerrar Posts Ajax" helper="Podr&aacute;n abrir/cerrar posts r&aacute;pidamente desde el post." name="mod-abrirycerrarajax" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.movcud  label="Ver cuentas desactivadas" helper="Podr&aacute;n ver cuentas de usuarios desactivadas." name="mod-vercuentasdesactivadas" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.movcus  label="Ver cuentas baneadas" helper="Podr&aacute;n ver cuentas de usuarios baneados." name="mod-vercuentassuspendidas" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mosu    label="Suspender Usuarios" helper="Podr&aacute;n suspender usuarios desde formulario modal." name="mod-suspenderusuarios" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.modu    label="Desbanear Usuarios" helper="Podr&aacute;n desbanear usuarios." name="mod-desbanearusuarios" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moep    label="Eliminar Posts" helper="Podr&aacute;n eliminar posts de otros usuarios." name="mod-eliminarposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moedpo  label="Editar Posts" helper="Podr&aacute;n editar posts de otros usuarios (requiere permiso publicar post)." name="mod-editarposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moop    label="Ocultar Posts" helper="Podr&aacute;n ocultar posts de otros usuarios." name="mod-ocultarposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.mocepc  label="Comentarios en Post Cerrado" helper="Podr&aacute;n comentar en posts cerrados." name="mod-comentarpostcerrado" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moedcopo label="Editar Comentarios de Posts" helper="Podr&aacute;n editar comentarios de posts de otros usuarios." name="mod-editarcomposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moaydcp label="Acciones de revisi&oacute;n" helper="Aprobar/desaprobar comentarios en los posts y en la revisi&oacute;n de comentarios." name="mod-desyaprobarcomposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moecp   label="Eliminar Comentarios de Posts" helper="Podr&aacute;n eliminar comentarios en posts de otros usuarios." name="mod-eliminarcomposts" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moef    label="Eliminar Fotos" helper="Podr&aacute;n eliminar fotos de otros usuarios." name="mod-eliminarfotos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moedfo  label="Editar Fotos" helper="Podr&aacute;n editar fotos de otros usuarios (requiere publicar foto)." name="mod-editarfotos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moecf   label="Eliminar Comentarios de Fotos" helper="Podr&aacute;n eliminar comentarios en fotos de otros usuarios." name="mod-eliminarcomfotos" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moepm   label="Eliminar Publicaciones de Muros" helper="Podr&aacute;n eliminar publicaciones en muros de otros usuarios." name="mod-eliminarpubmuro" class="mb-3"}

                  {include "dashboard/Check.tpl" type="checkbox" checked=$tsRango.permisos.moecm   label="Eliminar Comentarios de Muros" helper="Podr&aacute;n eliminar comentarios en muros de otros usuarios." name="mod-eliminarcommuro" class="mb-3"}
               </fieldset>
               <input type="hidden" name="sp" value="{if $tsType == 'special'}1{else}0{/if}" />
               <p><input type="submit" name="save" value="Guardar Cambios" class="btn_g"/></p>
				</div>
         </fieldset>
      </form>
   {elseif $tsAct == 'borrar'}
                                <form action="" method="post" id="admin_form">
                                	<div class="mensajes error">Si borras este rango todos los usuarios que est&eacute;n en &eacute;l, ser&aacute;n asignados al rango 
									
									<select name="new_rango">{foreach from=$tsRangos item=r}<option value="{$r.rango_id}" style="color:#{$r.r_color}; padding:2px 20px 0;" {if $r.rango_id == 3}selected{/if}>{$r.r_name}</option>{/foreach}</select> <br /> &iquest;Realmente deseas borrar este rango?</div>
                                    
									<label>&nbsp;</label> <input type="submit" name="save" value="S&iacute;, Continuar &raquo;" class="mBtn btnCancel">
                                </form>
                                {/if}
                                </div>
