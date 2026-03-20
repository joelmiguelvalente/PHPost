{include "main_header.tpl"}

	<div class="stats-grid grid gap-3 grid-cols-3">
		<div class="mb-2">
			<a class="flex justify-center items-center flex-col{if $tsAction == 'seguidores'} active{/if}" href="{$tsConfig.url}/monitor/seguidores">
				<span>Seguidores</span> <strong>{$tsData.stats.seguidores}</strong>
			</a>
		</div>
		<div class="mb-2">
			<a class="flex justify-center items-center flex-col{if $tsAction == 'siguiendo'} active{/if}" href="{$tsConfig.url}/monitor/siguiendo">
				<span>Siguiendo</span> <strong>{$tsData.stats.siguiendo}</strong>
			</a>
		</div>
		<div class="mb-2">
			<a class="flex justify-center items-center flex-col{if $tsAction == 'posts'} active{/if}" href="{$tsConfig.url}/monitor/posts">
				<span>Posts</span> <strong>{$tsData.stats.posts}</strong>
			</a>
		</div>
	</div>
	
	
	{if $tsAction == ''}
		<div class="grid gap-3" style="grid-template-columns:1fr 250px;">
			{include "m.monitor_content.tpl"}
			{include "m.monitor_sidebar.tpl"}
		</div>
	{else}
		{include "m.monitor_listado.tpl"}
	{/if}
   
{include "main_footer.tpl"}