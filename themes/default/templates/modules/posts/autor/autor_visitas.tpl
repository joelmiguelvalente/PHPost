<div class="box">
	<div class="box-header">
		<span class="box_txt">&Uacute;ltimos visitantes</span>
	</div>
	<div class="box-content">
		{if $tsPost.visitas}
			<div style="display: flex;justify-content: flex-start;align-items: flex-start;gap:.5rem;flex-wrap: wrap;">
				{foreach from=$tsPost.visitas item=v}
				 	<a href="{$tsConfig.url}/@{$v.user_name}" style="display:inline-block;width:2rem;height:2rem;border-radius:10em;overflow:hidden;">
				 		{include "blocks/Avatar.tpl" id=$v.user_id size=32 alt=$v.date|hace:true lazy=true class="avatar"}
				 	</a> 
				{/foreach}
			</div>
		{else}
	 		<div class="alert-empty">No hay visitas</div>
	  	{/if}
	</div>
</div>