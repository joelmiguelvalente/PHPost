<div class="list-fotos grid gap-2">
	{foreach from=$tsGeneral.fotos item=f key=i}
		{if $f.foto_id}
			<div class="list-foto-item w-full">
				<a href="{$tsConfig.url}/fotos/{$tsInfo.nick}/{$f.foto_id}/{$f.f_title|seo}.html" title="{$f.f_title}" class="block">
					<img class="rounded" src="{$f.f_url}"/>
				</a>
			</div>
		{else}
			<div class="list-foto-item"></div>
		{/if}
	{/foreach}
</div>