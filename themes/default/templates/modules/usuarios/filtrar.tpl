<form method="GET" class="clear">
	<span class="title pb-2 text-center block">Filtrar:</span>
	<div class="filters mb-3"> 
		<label class="filter-item{if $tsFiltro.online} active{/if}">
			<input type="checkbox" name="online" value="true"{if $tsFiltro.online} checked{/if}/>
			<span>En linea</span>
		</label>
		<label class="filter-item{if $tsFiltro.avatar} active{/if}">
			<input type="checkbox" name="avatar" value="true"{if $tsFiltro.avatar} checked{/if}/>
			<span>Con foto</span>
		</label>
	</div>
	<span class="title py-2 text-center block">Género:</span>
	<div class="filters radios mb-3"> 
		<label class="filter-item{if $tsFiltro.sex == 'male'} active{/if}">
			<input type="radio" name="sexo" value="male"{if $tsFiltro.sex == 'male'} checked{/if}/>
			<span>Hombre</span>
		</label>
		<label class="filter-item{if $tsFiltro.sex == 'female'} active{/if}">
			<input type="radio" name="sexo" value="female"{if $tsFiltro.sex == 'female'} checked{/if}/>
			<span>Mujer</span>
		</label>
		<label class="filter-item{if $tsFiltro.sex == 'none'} active{/if}">
			<input type="radio" name="sexo" value="none"{if $tsFiltro.sex == 'none'} checked{/if}/>
			<span>Ambos / sin decir</span>
		</label>
	</div>
	<span class="title py-2 text-center block">Otros:</span>
	<div class="filters selects mb-3"> 
		<label class="filter-item{if $tsFiltro.pais} active{/if} mb-2">
			<select name="pais" class="form-select py-1 px-2" id="pais">
				<option value="">Todos los Pa&iacute;ses...</option>
				{foreach from=$tsPaises key=code item=pais}
					<option value="{$code}"{if $tsFiltro.pais == $code} selected{/if}>{$pais}</option>
				{/foreach}
			</select>
		</label>
		<label class="filter-item{if $tsFiltro.rango} active{/if}">
			<select name="rango" class="form-select py-1 px-2" id="rango">
				<option value="">Todos los Rangos...</option>
				{foreach from=$tsRangos item=r}
					<option value="{$r.rango_id}"{if $tsFiltro.rango == $r.rango_id} selected{/if}>{$r.r_name}</option>
				{/foreach}
			</select>
		</label>
	</div>
	<div class="buttons text-center py-2">
		<input type="submit" class="mBtn btnOk block" value="Filtrar" />
	</div>
</form>