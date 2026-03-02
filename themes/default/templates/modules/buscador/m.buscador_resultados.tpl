<div id="resultados" >
	<div id="showResult">
		{if $tsResults.data}
			{foreach from=$tsResults.data item=r}
				<div id="div_{$r.post_id}">
					<a class="titlePost flex justify-start items-center gap-1" href="{$tsConfig.url}/posts/{$r.c_seo}/{$r.post_id}/{$r.post_title|seo}.html"><img src="{$tsRoutes.assets.images}/icons/cat/{$r.c_img}"/> {$r.post_title}</a>
					<div class="info flex justify-start items-center gap-3 py-1">
						<span><img alt="Creado hace" src="{$tsRoutes.assets.images}/icons/clock.png"/> 
						<strong>{$r.post_date|hace:true}</strong></span> -
						<span><img alt="Posts relacionados" src="{$tsRoutes.assets.images}/icons/relacionados.png"/> <a href="{$tsConfig.url}/buscador/?query={$r.post_title}&engine={$tsEngine}&category={$tsCategory}&autor={$tsAutor}">Post Relacionados</a></span> -
						<span><img alt="Creado por" src="{$tsRoutes.assets.images}/icons/autor.png"/> <a href="{$tsConfig.url}/@{$r.user_name}">{$r.user_name}</a></span>
					</div>
				</div>
			{foreachelse}
				<h4 class="empty">Lo siento, no se encontraron resultados...</h4>
			{/foreach}
		{else}
			<h4 class="empty">¿Que tipo de busqueda quieres realizar?</h4>
		{/if}
	</div>
	{if $tsResults.data}
		<div class="paginadorCom">
			{if $tsResults.pages.prev != 0}<div style="display: block; margin: 5px 0pt; width: 110px;text-align:left" class="floatL before"><a href="{$tsConfig.url}/buscador/?page={$tsResults.pages.prev}{if $tsQuery}&q={$tsQuery}{/if}{if $tsEngine}&e={$tsEngine}{/if}{if $tsCategory}&cat={$tsCategory}{/if}{if $tsAutor}&autor={$tsAutor}{/if}">&laquo; Anterior</a></div>{/if}
			{if $tsResults.pages.next != 0}<div style="display: block; margin: 5px 0pt; width: 110px;text-align:right" class="floatR next"><a href="{$tsConfig.url}/buscador/?page={$tsResults.pages.next}{if $tsQuery}&q={$tsQuery}{/if}{if $tsEngine}&e={$tsEngine}{/if}{if $tsCategory}&cat={$tsCategory}{/if}{if $tsAutor}&autor={$tsAutor}{/if}">Siguiente &raquo;</a></div>{/if}
		</div>
	{/if}
</div>