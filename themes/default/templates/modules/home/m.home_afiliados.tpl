<script src="{$tsRoutes.assets.js}/afiliados.js"></script>
<div class="box">
	<div class="box-header">
		<span class="box_txt" title="Afiliados">Afiliados</span>
	</div>
	{if $tsAfiliados}
		<div class="box-content">
			<div class="grupo-afiliados">
				{foreach from=$tsAfiliados item=af}
					<div class="afiliado">
						<img src="{$af.a_banner}" width="190" height="40" onclick="afiliado.detalles({$af.aid}); return false;" title="{$af.a_titulo}"/>
					</div>
				{/foreach}
			</div>
		</div>
	{/if}
	<div class="box-footer">
		<span style="cursor: pointer;" onclick="afiliado.nuevo(); return false">Afiliate a {$tsConfig.titulo}</span>
	</div>
</div>