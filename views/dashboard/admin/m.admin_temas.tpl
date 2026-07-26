<h1 class="text-xl font-semibold mb-4">Administrar Temas</h1>
<div class="rounded">
	{include "dashboard/Alert.tpl" text="Los themes se cargan de forma automática, si quieres eliminarlo, simplemente borralo desde <pre>{$tsRoutes['url']}/themes/<b>(la carpeta a eliminar)</b></pre>" color="orange" show=true}

	<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
		<table class="min-w-full border-collapse text-sm">
			{include "dashboard/table/Thead.tpl" fields=[
				"Vista previa",
				"Nombre",
				"Creado por",
				"En uso",
				"Opciones"
			]}
			<tbody class="divide-y dark:divide-gray-700">
				{foreach from=$tsTemas item=tema}
					<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
						<td class="px-3 py-2 text-gray-600 dark:text-gray-400">
							<picture class="block shadow-sm rounded-lg" style="width:180px;height:100px;">
								<img src="{$tsRoutes['tema:images']}/favicon.png" loading="lazy" data-src="{$tsConfig.url}/{$tema.t_screen}" style="object-fit:cover;" class="w-full h-full" />
							</picture>
						</td>
						<td class="px-3 py-2" style="width: 25%;">{$tema.t_name}
							{$tema.t_description ? "<br><small>{$tema.t_description}</small>" : ''}
						</td>
						<td class="px-3 py-2">{$tema.t_copy}
							{if $tema.t_link}
							    <br><small>Link: <a href="{$tema.t_link}" target="_blank" rel="external" title="Sitio oficial / Demo" class="text-decoration-none text-blue-600 font-bold">{$tema.t_link}</a></small>
							{/if}
							{if $tema.t_repo}
							    <br><small>Github: <a href="{$tema.t_repo}" target="_blank" rel="external" title="Repositorio en github" class="text-decoration-none text-blue-600 font-bold">{$tema.t_repo}</a></small>
							{/if}
						</td>
						<td class="px-3 py-2">
							<span class="inline-flex rounded-full{if $tsThemeCurrent == $tema.t_path} bg-green-100 text-green-800{else} bg-purple-100 text-purple-800{/if} px-2 py-0.5 text-xs font-medium">{if $tsThemeCurrent == $tema.t_path}Activo{else}Inactivo{/if}</span>
						</td>
						<td class="px-3 py-2">
							<div class="flex justify-center gap-2">
								{if $tsThemeCurrent != $tema.t_path}
									{include "dashboard/table/Action.tpl" action="tema.usar('{$tema.t_path}')" title="Usar este tema" icon="library_add_check" type="button"}
								{/if}
							</div>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
