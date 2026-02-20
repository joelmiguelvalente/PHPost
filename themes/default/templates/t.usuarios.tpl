{include "main_header.tpl"}
<div id="resultados"> 
	<div class="filterFull grid gap-3">
		<div>
			<div class="box p-2">
				{include "filtrar.tpl"}
			</div>
		</div>
		<div class="xResults">
			<h3 class="p-0 m-0 mb-3 text-center">Mostrando <strong>{$tsTotal}</strong> resultados de <strong>{$tsPages.total}</strong></h3>
			<div id="showResult">
				{if $tsUsers}
					<div class="users grid gap-3">
						{foreach from=$tsUsers item=u}
							{include "user-info.tpl"}
						{/foreach}
					</div>
				{else}
					<div class="alert-empty">No se encontraro usuarios con los filtros seleccionados.</div>
				{/if}
				<div class="paginador flex justify-center items-center gap-3 p-3">
					{if $tsPages.prev != 0}
						<div style="text-align:left">
							<a href="{$tsConfig.url}/usuarios/?page={$tsPages.prev}{if $tsFiltro.online == 'true'}&online=true{/if}{if $tsFiltro.avatar == 'true'}&avatar=true{/if}{if $tsFiltro.sex}&sex={$tsFiltro.sex }{/if}{if $tsFiltro.pais}&pais={$tsFiltro.pais}{/if}{if $tsFiltro.rango}&rango={$tsFiltro.rango}{/if}">&laquo; Anterior</a>
						</div>
					{/if}
					{if $tsPages.next != 0}
						<div style="text-align:right">
							<a href="{$tsConfig.url}/usuarios/?page={$tsPages.next}{if $tsFiltro.online == 'true'}&online=true{/if}{if $tsFiltro.avatar == 'true'}&avatar=true{/if}{if $tsFiltro.sex}&sex={$tsFiltro.sex }{/if}{if $tsFiltro.pais}&pais={$tsFiltro.pais}{/if}{if $tsFiltro.rango}&rango={$tsFiltro.rango}{/if}">Siguiente &raquo;</a>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
</div>
{include "main_footer.tpl"}