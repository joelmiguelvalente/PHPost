<div id="topsPostBox" class="box">
	<div class="box-header">
		<span class="box_txt">TOPs posts <a class="size9" href="{$tsConfig.url}/top/" title="TOPs posts">(Ver m&aacute;s)</a></span>
		<div class="box_icon">
			<a href="{$tsConfig.url}/rss/top-post-semana" title="RSS | TOPs posts"><span class="systemicons sRss"></span></a>
		</div>
	</div>
	<div class="box-filter">
		<span data-box="topsPostBox" id="Ayer">Ayer</span>
		<span data-box="topsPostBox" id="Semana"{if $tsTopPosts.semana} class="active"{/if}>Semana</span>
		<span data-box="topsPostBox" id="Mes">Mes</span>
		<span data-box="topsPostBox" id="Historico"{if !$tsTopPosts.semana} class="active"{/if}>Hist&oacute;rico</span>
	</div>
	<div class="box-content" style="height:calc(24px * 15);">
		<ol id="filterAyer" class="filter-list tops" style="display:none;">
			{foreach from=$tsTopPosts.ayer key=i item=p}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a class="truncate" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title}</a>
					<span class="count">{$p.post_puntos}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops ayer</div>
			{/foreach}
		</ol>
		<ol id="filterSemana" class="filter-list tops" style="display:{if $tsTopPosts.semana}block{else}none{/if};">
			{foreach from=$tsTopPosts.semana key=i item=p}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a class="truncate" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title}</a>
					<span class="count">{$p.post_puntos}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops semana</div>
			{/foreach}
		</ol>
		<ol id="filterMes" class="filter-list tops" style="display:none;">
			{foreach from=$tsTopPosts.mes key=i item=p}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a class="truncate" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title}</a>
					<span class="count">{$p.post_puntos}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops mes</div>
			{/foreach}
		</ol>
		<ol id="filterHistorico" class="filter-list tops" style="display:{if !$tsTopPosts.semana}block{else}none{/if};">
			{foreach from=$tsTopPosts.historico key=i item=p}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a class="truncate" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title}</a>
					<span class="count">{$p.post_puntos}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops historicos</div>
			{/foreach}
		</ol>
	</div>
</div>