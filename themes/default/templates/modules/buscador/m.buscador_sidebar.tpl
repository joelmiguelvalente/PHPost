<form method="GET" name="buscador">
	<div class="form-group">
		<label class="form-label" for="engine">Que quieres buscar...?</label>
		<input type="text" value="{$tsQuery}" id="engine" class="form-control searchBar" placeholder="Buscar..." name="query"/>
		<input type="hidden" name="engine" value="{$tsEngine}" />
	</div>
	<div class="form-group">
		<label class="form-label" for="categoria">Categoria</label>
		<select id="categoria" name="category" class="form-control">
			<option value="0">Todas</option>
			{foreach from=$tsCategories item=c}
				<option value="{$c.cid}"{if $tsCategory == $c.cid} selected{/if}>{$c.c_nombre}</option>
			{/foreach}
		</select>
	</div>
	<div class="form-group">
		<label class="form-label" for="author">Usuario</label>
		<input type="text" name="autor" id="author" value="{$tsAutor}" class="form-control" placeholder="JhonDoe" />
	</div>
	<div class="buttons py-2 text-center">
		<input type="submit" title="Buscar" value="Buscar" class="btn btn-primary"/>
	</div>
</form>
