<div id="perfil_wall" status="activo">
	{if $tsGeneral.fotos_total > 0}
		<div id="perfil-foto-bar" class="p-2">
			{include "m.perfil_muro_fotos.tpl"}
		</div>
	{/if}
	<div id="perfil-form" class="widget">
		{if $tsPrivacidad.muro_firma.status == true}
			{include "m.perfil_muro_form.tpl"}
		{else}
			<div class="alert-empty" style="border-top:none">{$tsPrivacidad.muro_firma.message}</div>
		{/if}
	</div>
	<div class="widget clearfix" id="perfil-wall">
		<div id="wall-content">
			{include "m.perfil_muro_story.tpl"}
		</div>
		<!-- more -->
		{if $tsMuro.total >= 10}
			<div class="more-pubs">
				<div class="content">
					<button role="button" class="btnAction" data-action="loadMore" data-argument="wall">Publicaciones m&aacute;s antiguas</button>
					<img width="20" height="20" style="display: none;" alt="Cargando publicaciones antiguas" src="{$tsRoutes['assets:images']}/loader.gif"/>
				</div>
			</div>
		{elseif $tsMuro.total == 0 && $tsUser->is_member}
			<div class="alert-empty">Este usuario no tiene comentarios, se el primero.</div>
		{/if}
	</div>
</div>
