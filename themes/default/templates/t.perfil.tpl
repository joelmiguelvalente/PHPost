{include "main_header.tpl"}
	{include "m.perfil_headinfo.tpl"}
	<div class="perfil-main grid gap-3 {$tsGeneral.stats.user_rango.1}">
		<div class="perfil-content p-3 general">
			<div id="info" pid="{$tsInfo.uid}"></div>
			<div id="perfil_content">
				{if $tsPrivacidad.muro.status == false}
					<div id="perfil_wall" status="activo" class="widget">
						<div class="alert-empty">{$tsPrivacidad.muro.message}</div>
					</div>
					<div id="loadInfoTab" data-tab="info" data-target="informacion"></div>
				{elseif $tsType == 'story'}
					{include "m.perfil_story.tpl"}
				{elseif $tsType == 'news'}
					{include "m.perfil_noticias.tpl"}
				{else}
					{include "m.perfil_muro.tpl"}
				{/if}
			</div>
			<div style="width:100%;text-align:center;display:none" id="perfil_load">
				<img src="{$tsRoutes.assets.images}/loader.gif" />
			</div>
		</div>
		<div class="perfil-sidebar p-3">
			{include "m.perfil_sidebar.tpl"}
		</div>
	</div>
{include "main_footer.tpl"}