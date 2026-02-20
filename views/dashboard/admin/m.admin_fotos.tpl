<h1 class="text-xl font-semibold mb-4">Administrar Fotos</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
   
   {if $tsAct == ''}
		{if !$tsAdminPosts.data}
			{include "dashboard/Alert.tpl" text="No hay fotos." color="orange" show=true}
		{else}
			<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">					
					{include "dashboard/table/Thead.tpl" fields=[
						"ID",
						"T&iacute;tulo",
						"Autor",
						"Fecha",
						"IP",
						"Comentarios",
						"Estado",
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsAdminFotos.data item=f}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="foto_{$f.foto_id}">
								<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$f.foto_id}</td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/fotos/{$f.user_name}/{$f.foto_id}/{$f.f_title|seo}.html" target="_blank">{$f.f_title|truncate:30}</a></td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/perfil/{$f.user_name}" class="hovercard" uid="{$f.user_id}">{$f.user_name}</a></td>
								<td class="px-3 py-2">{$f.f_date|hace:true}</td>                
   							<td class="px-3 py-2"><a href="{$tsConfig.url}/moderacion/buscador/1/1/{$f.f_ip}" class="geoip" target="_blank">{$f.f_ip}</a></td>
								<td class="px-3 py-2" id="comments_foto_{$f.foto_id}">{if $f.f_closed == 1}<font color="red">Cerrados</font>{else}<font color="green">Abiertos</font>{/if}</td>
								<td class="px-3 py-2" id="status_foto_{$f.foto_id}">{if $f.f_status == 1}<font color="purple">Oculta</font>{elseif $f.f_status == 0}<font color="green">Visible</font>{else}<font color="red">Eliminada</font>{/if}</td>
								<td class="px-3 py-2 admin_actions">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="fotos/editar/{$f.foto_id}" title="Editar foto" icon="edit"}
										<a {if $f.f_status != 2}onclick="admin.fotos.setOpenClosed({$f.foto_id}); return false;"{/if}><img src="{$tsRoutes.tema.images}/icons/comment.png" title="{if $f.f_status == 2}No disponible{else}Abrir/Cerrar Comentarios{/if}" /></a>
										<a {if $f.f_status != 2}onclick="admin.fotos.setShowHide({$f.foto_id}); return false;"{/if}><img src="{$tsRoutes.tema.images}/reactivar.png" title="{if $f.f_status == 2}No disponible{else}Mostrar/Ocultar Foto{/if}" /></a>
										{include "dashboard/table/Action.tpl" action="fotos.borrar({$f.foto_id})" type="button" title="Borrar Foto
										" icon="delete"}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					{include "dashboard/table/Tfoot.tpl" span=9 pages=$tsAdminFotos.pages}
				</table>
			</table>
		{/if}
	{/if}
</div>