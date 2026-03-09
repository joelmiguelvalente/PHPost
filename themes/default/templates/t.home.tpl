{include "main_header.tpl"}
{$tsInstall}
<div class="grid-home">
	<div id="izquierda">
		{include "m.home_last_posts.tpl"}
	</div>
	<div id="centro">
		{include "m.home_search.tpl"}
		{include "m.home_last_comments.tpl"}
		{include "m.home_top_posts.tpl"}
		{include "m.home_top_users.tpl"}
		<!--Poner aqui mas modulos-->
	</div>
	<div id="derecha">
		{include "widget.logs.tpl"}
		{if $tsConfig.c_fotos_private && $tsUser->is_member}
			{include "m.home_fotos.tpl"}
		{/if}
		{include "m.home_stats.tpl"}
		{include "m.home_afiliados.tpl"}
		<br class="spacer"/>
		{include "m.global_ads_160.tpl"}
	</div>
</div>
{include "main_footer.tpl"}