<div id="search_box" class="new-search posts">
	<div class="bar-options">
		<div class="bar-item" data-search="google">Google</div>
		<div class="bar-item active" data-search="web">Posts</div>
		<div class="bar-item" data-search="tags">Tags</div>
		<div class="bar-item" data-search="autor">Autor</div>
	</div>
	<form action="{$tsConfig.url}/buscador/" name="search" gid="{$tsConfig.ads_search}">
		<input type="hidden" name="engine" value="web" />
		<div class="search-group">
			<input type="text" id="query" placeholder="Buscar en web..." name="query"/>
			<button type="submit" aria-label="Botón buscar">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14"/></svg>
			</button>
		</div>
		<div class="category">
			<label for="category" id="search-home-cat-filter" class="more-cats">Categor&iacute;a:</label>
			<select id="category" name="category">
				<option value="0">Todas</option>
				{foreach from=$tsCategories item=c}
					<option value="{$c.cid}"{if $tsCategoria == '$c.c_seo'} selected{/if}>{$c.c_nombre}</option>
				{/foreach}
			</select>
		</div>
	</form>
	<button id="sh_options">Opciones</button>
</div>
<!-- onclick="$('#search-home-cat-filter').show()" -->