{if $tsMensajes.data}
	{foreach from=$tsMensajes.data item=mp}
		<div class="dropdown-item block py-1{if $mp.mp_read_to == 0 || $mp.mp_read_mon_to == 0} unread{/if}">
         <a href="{$tsConfig.url}/mensajes/leer/{$mp.mp_id}" class="dropdown-link flex justify-start items-start gap-2" title="{$mp.mp_subject}">
            {include "blocks/Avatar.tpl" alt=$mp.user_name id=$mp.user_id size=32 placeholder=false}
            <div class="content">
               <div class="subject">{$mp.mp_subject}</div>
               <div class="preview">{$mp.mp_preview}</div>
               <div class="time"><span class="autor">{$mp.user_name}</span> | {$mp.mp_date|hace:true}</div>
            </div>
         </a>
      </div>
	{/foreach}
{else}
   <div class="dropdown-empty">No tienes mensajes</div>
{/if}