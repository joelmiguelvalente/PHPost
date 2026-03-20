<div class="searchFil">
	<div class="head flex justify-between items-center">
		<h3 class="searchTitle">
			{if $tsEngine == 'web'}{$tsConfig.titulo}{else}{$tsEngine|capitalize}{/if}
		</h3>
		<div class="searchTabs flex items-center gap-1">
			<span role="button" data-select="google" class="search-tab flex-inline items-center gap-1 py-2 px-3 rounded-sm{if $tsEngine == 'google'} active{/if}">Google</span>
			<span role="button" data-select="web" class="search-tab flex-inline items-center gap-1 py-2 px-3 rounded-sm{if !$tsEngine || $tsEngine == 'web'} active{/if}">Posts
				{if isset($tsCounts.posts) && $tsCounts.posts > 0}
					<span class="tab-badge">{$tsCounts.posts}</span>
				{/if}
			</span>
			<span role="button" data-select="tags"
				class="search-tab flex-inline items-center gap-1 py-2 px-3 rounded-sm{if $tsEngine == 'tags'} active{/if}">Tags
				{if isset($tsCounts.tags) && $tsCounts.tags > 0}
					<span class="tab-badge">{$tsCounts.tags}</span>
				{/if}
			</span>
			<span role="button" data-select="fotos"
				class="search-tab flex-inline items-center gap-1 py-2 px-3 rounded-sm{if $tsEngine == 'fotos'} active{/if}">Fotos
				{if isset($tsCounts.fotos) && $tsCounts.fotos > 0}
					<span class="tab-badge">{$tsCounts.fotos}</span>
				{/if}
			</span>
			<span role="button" data-select="usuarios"
				class="search-tab flex-inline items-center gap-1 py-2 px-3 rounded-sm{if $tsEngine == 'usuarios'} active{/if}">Usuarios
				{if isset($tsCounts.usuarios) && $tsCounts.usuarios > 0}
					<span class="tab-badge">{$tsCounts.usuarios}</span>
				{/if}
			</span>
			<span role="button" data-select="muro"
				class="search-tab flex-inline items-center gap-1 py-2 px-3 rounded-sm{if $tsEngine == 'muro'} active{/if}">Muro
				{if isset($tsCounts.muro) && $tsCounts.muro > 0}
					<span class="tab-badge">{$tsCounts.muro}</span>
				{/if}
			</span>
		</div>
	</div>
</div>
