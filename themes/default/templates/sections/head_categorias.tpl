<div class="floatR filterCat">
	<label for="categorias">Filtrar por Categorías:</label>
	<select id="categorias" onchange="irACategoria(this.value)" style="margin:-2px 0 0;">
		<option selected="selected" value="root">Seleccionar categoría</option>
		<option value="{if $tsConfig.c_allow_portal == 0}-1{else}-2{/if}">Ver Todas</option>
		<option value="linea">-----</option>
		{foreach from=$tsCategories item=c}
			<option value="{$c.c_seo}"{if $tsCategoria == '$c.c_seo'} selected{/if}>{$c.c_nombre}</option>
		{/foreach}
	</select>
</div>