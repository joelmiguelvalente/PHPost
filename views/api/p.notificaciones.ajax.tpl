{if $tsData}
	{foreach from=$tsData item=noti}
      <div class="dropdown-item{if $noti.unread > 0} unread{/if}">
         <div class="icon">
            <span class="monac_icons ma_{$noti.style}"></span>
         </div>
         <div class="info">
            {if $noti.total == 1 && $noti.user != ''}<a href="{$tsConfig.url}/@{$noti.user}" class="dropdown-link inline-block" title="{$noti.user}">{$noti.user}</a> {/if}{$noti.text}{if $noti.link} <a title="{$noti.ltit}" class="dropdown-link" href="{$noti.link}">{$noti.ltext}</a>{/if}
         </div>
      </div>
   {/foreach}
{else}
   <div class="dropdown-empty">
      No hay notificaciones
   </div>
{/if}