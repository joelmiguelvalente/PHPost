<div class="left">
	<div class="box">
		<div class="box-header">
			<span class="box_txt" title="Filtrar">Filtrar</span>
			<div class="box_icon">
				<span class="systemicons actualizar"></span>
			</div>
		</div>
		<div class="box-content filters-options">
			<h4>Categor&iacute;a</h4>
			<select class="form-select p-2" onchange="location.href='{$tsConfig.url}/top/{$tsAction}/?fecha={$tsFecha}&cat='+$(this).val()">
				<option value="0">Todas</option>
				{foreach from=$tsCategories item=c}
					<option value="&cat={$c.cid}"{if $tsCat == $c.cid} selected{/if}>{$c.c_nombre}</option>
				{/foreach}
			</select>
			<hr class="my-4">
			<h4>Per&iacute;odo</h4>
			<div class="filter-list">
				<a href="{$tsConfig.url}/top/{$tsAction}/?fecha=2&cat={$tsCat}&sub={$tsSub}" class="rounded p-2 block filter-item{if $tsFecha == 2} active{/if}">Ayer</a>
				<a href="{$tsConfig.url}/top/{$tsAction}/?fecha=1&cat={$tsCat}&sub={$tsSub}" class="rounded p-2 block filter-item{if $tsFecha == 1} active{/if}">Hoy</a>
				<a href="{$tsConfig.url}/top/{$tsAction}/?fecha=3&cat={$tsCat}&sub={$tsSub}" class="rounded p-2 block filter-item{if $tsFecha == 3} active{/if}">&Uacute;ltimos 7 d&iacute;as</a>
				<a href="{$tsConfig.url}/top/{$tsAction}/?fecha=4&cat={$tsCat}&sub={$tsSub}" class="rounded p-2 block filter-item{if $tsFecha == 4} active{/if}">Del mes</a>
				<a href="{$tsConfig.url}/top/{$tsAction}/?fecha=5&cat={$tsCat}&sub={$tsSub}" class="rounded p-2 block filter-item{if $tsFecha == 5} active{/if}">Todos los tiempos</a>
			</div>
		</div>
	</div>
</div>