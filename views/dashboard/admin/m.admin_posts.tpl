<h1 class="text-xl font-semibold mb-4">Administrar Posts</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
   
   {if $tsAct == ''}
		{if !$tsAdminPosts.data}
			{include "dashboard/Alert.tpl" text="No hay posts." color="orange" show=true}
		{else}
			<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">					
					{include "dashboard/table/Thead.tpl" fields=[
						"ID",
						"Título",
						"Autor",
						"Fecha",
						'<a class="qtip" title="Ordenar por estado ascendente" href="{$tsConfig.url}/admin/posts?order=estado&modo=asc"><</a> Estado <a class="qtip" title="Ordenar por estado descendente" href="{$tsConfig.url}/admin/posts?order=estado&modo=desc">></a>',
						'<a class="qtip" title="Ordenar por IP ascendente" href="{$tsConfig.url}/admin/posts?o=ip&modo=asc"><</a> IP <a class="qtip" title="Ordenar por IP descendente" href="{$tsConfig.url}/admin/posts?o=ip&modo=desc">></a>',
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsAdminPosts.data item=p}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="post_{$p.post_id}">
								<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$p.post_id}</td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" target="_blank">{$p.post_title|truncate:30}</a></td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/perfil/{$p.user_name}" class="hovercard" uid="{$p.user_id}">{$p.user_name}</a></td>
								<td class="px-3 py-2">{$p.post_date|hace:true}</td>
								<td class="px-3 py-2" id="status_post_{$p.post_id}">
									<span class="inline-flex rounded-full
									{if $p.post_status == 3} bg-purple-100 text-purple-800
									{elseif $p.post_status == 2} bg-red-100 text-red-800
									{elseif $p.post_status == 1} bg-orange-100 text-orange-800
									{else} bg-green-100 text-green-800
									{/if} px-2 py-0.5 text-xs font-medium">{if $p.post_status == 3}Oculto{elseif $p.post_status == 2}Eliminado{elseif $p.post_status == 1}En revisión{else}Activo{/if}</span>
								</td>
   							<td class="px-3 py-2" id="moreinfo1_2">
   								<a href="{$tsConfig.url}/moderacion/buscador/1/1/{$p.post_ip}" target="_blank">{$p.post_ip}</a>
   							</td>
								<td class="px-3 py-2">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="../editar?id={$p.post_id}" title="Editar Post" icon="edit"}
										{if $p.post_status == 2}
											{include "dashboard/table/Action.tpl" action="posts.borrar({$p.post_id})" type="button" title="Borrar Post permanentemente" icon="delete"}
										{else}
											{include "dashboard/table/Action.tpl" action="moderacion.posts.borrar({$p.post_id}, 'posts', null)" type="button" title="Borrar Post" icon="delete"}
										{/if}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					{include "dashboard/table/Tfoot.tpl" span=7 pages=$tsAdminPosts.pages}
				</table>
			</div>
		{/if}
	{/if}
</div>
