<div class="searchFil">
	<div class="head flex justify-between items-center">
		<h3>{if $tsEngine == 'web'}{$tsConfig.titulo}{else}{$tsEngine}{/if}</h3>
		<div class="searchWith flex justify-end items-center gap-3">
			<span role="button" data-select="google" class="search-item rounded{if $tsEngine == 'google'} active{/if}">Google</span>
			<span role="button" data-select="web" class="search-item rounded{if $tsEngine || $tsEngine == 'web'} active{/if}">{$tsConfig.titulo}</span>
			<span role="button" data-select="Tags" class="search-item rounded{if $tsEngine == 'Tags'} active{/if}">Etiquetas(tags)</span>
		</div>
	</div>
</div>