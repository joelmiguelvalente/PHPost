<div class="header py-1 px-3 flex justify-between items-start gap-2">
	<div class="navegation flex justify-center items-end gap-2 mt-2">
		<a title="Post Anterior (m&aacute;s viejo)" href="{$tsConfig.url}/posts/?action=prev&id={$tsPost.post_id}"><i class="icons anterior" arial-hidden="true"></i></a>
		<a title="Post Aleatorio" href="{$tsConfig.url}/posts/?action=random"><img title="Post aleatorio" src="{$tsRoutes.tema.images}/arrow-join.png"/></a>
		<a title="Post Siguiente (m&aacute;s nuevo)" href="{$tsConfig.url}/posts/?action=next&id={$tsPost.post_id}"><i class="icons siguiente" arial-hidden="true"></i></a>
	</div>
	<div class="heading mb-3">
		<h1 class="title mb-2">{$tsPost.post_title}</h1>
		<div class="flex justify-start items-center gap-3">
			<a class="badge" href="{$tsConfig.url}/posts/{$tsPost.categoria.c_seo}/" title="Categoria {$tsPost.categoria.c_nombre}">{$tsPost.categoria.c_nombre}</a>
			<time datetime="{$tsPost.post_date|fecha:'iso'}">{$tsPost.post_date|hace:true}</time>
		</div>
	</div>
	
</div>
{if !$tsUser->is_member}{include "m.global_ads_728.tpl"}{/if}
{include "contenido/acciones.tpl"}
<div class="post-read p-3">
	{$tsPost.post_body}
	<div class="tags-block flex justify-start items-center gap-3 py-3 mt-3">
		{foreach from=$tsPost.post_tags key=i item=tag}
			<a class="block px-3 rounded" rel="tag" href="{$tsConfig.url}/buscador/?query={$tag|seo}&engine=tags">#{$tag}</a>
		{/foreach}
	</div>
	<div class="grid gap-3 p-3" style="grid-template-columns: repeat(2, 1fr);">
		<div>
			<h4 class="py-1 px-0 m-0">Anterior</h4>
			{if $PrevPost}
				<a class="block" href="{$PrevPost.post_url}" title="{$PrevPost.post_title}">{$PrevPost.post_title}</a>
			{/if}
		</div>
		<div class=" text-right">
			<h4 class="py-1 px-0 m-0">Siguiente</h4>
			{if $NextPost}
				<a class="block" href="{$NextPost.post_url}" title="{$NextPost.post_title}">{$NextPost.post_title}</a>
			{/if}
		</div>
	</div>	
</div>
{include "m.global_ads_728.tpl"}
{include "m.posts_metadata.tpl"}