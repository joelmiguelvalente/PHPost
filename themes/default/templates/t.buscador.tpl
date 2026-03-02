{include "main_header.tpl"}
	{include "m.buscador_top.tpl"}
	<div class="buscador-columns">
		{include "m.buscador_sidebar.tpl"}
		<div class="buscador-content">
			{if $tsEngine == 'google'}
				<div class="google">
					{if !empty($tsConfig.ads_search)}
						<!-- https://programmablesearchengine.google.com/cse/all -->
						<script async src="https://cse.google.com/cse.js?cx={$tsConfig.ads_search}"></script>
						<div class="gcse-search"></div>
					{else}
						{if $tsUser->is_member and $tsUser->is_admod}
							<div class="empty">No configuraste el buscardor desde google, tienes que agregar el ID del buscador, para ello accede a <a href="https://programmablesearchengine.google.com/cse/all">https://programmablesearchengine.google.com/cse/all</a></div>
						{else}
							<div class="empty">El buscador de google no esta configurado aún!</div>
						{/if}
					{/if}
				</div>
			{else}
				{include "m.buscador_resultados.tpl"}
			{/if}
		</div>
	</div>
{include "main_footer.tpl"}