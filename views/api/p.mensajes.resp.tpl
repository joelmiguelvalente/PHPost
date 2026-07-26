1:
<div class="bubble-container px-3 py-2 flex justify-start items-start gap-3 flex-row-reverse">
	<a href="{$tsConfig.url}/@{$tsUser->nick}" class="autor-image block overflow-hidden rounded-full">
	   {include "blocks/Avatar.tpl" alt=$tsUser->nick id=$tsUser->uid size=48 class="ratio ratio-1x1" placeholder=false}
	</a>
	<div class="mensaje author text-right py-2 px-3 rounded-lg">
	   <div class="mensaje-autor">
		   <a href="{$tsConfig.url}/@{$mp.user_name}" class="autor-name">{$mp.user_name}</a>
	   </div>
	   <div class="mensaje-cuerpo my-1">{$mp.mp_body|raw|nl2br}</div>
	   <div class="mensaje-fecha">
		   {if $tsUser->is_admod && $mp.mp_ip != ''}
			   <a href="{$tsConfig.url}/moderacion/buscador/1/1/{$mp.mp_ip}">{$mp.mp_ip}</a> | 
		   {/if}
		   {$mp.mp_date|hace:true}
	   </div>
	</div>
</div>
