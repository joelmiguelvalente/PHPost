<div class="box">
	<div class="box-header">
		<span class="box_txt">Posts relacionados</span>
	</div>
	<div class="box-content">
		{if $tsRelated}
			{foreach from=$tsRelated item=p}
				<div class="categoriaPost p-1 mb-2">
					<a class="truncate{if $p.post_private} privado{/if}" title="{$p.post_title}" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" rel="dc:relation">{$p.post_title}</a>
					<a href="{$tsConfig.url}/posts/{$p.c_seo}" class="flex justify-start items-center gap-1 font-bold"><img src="{$tsRoutes.assets.images}/icons/cat/{$p.c_img}" alt="{$p.c_nombre}"> {$p.c_nombre}</a>
				</div>
			{/foreach}
		{else}
			<div class="emptyData">No se encontraron posts relacionados.</div>
		{/if}
	</div>
</div>