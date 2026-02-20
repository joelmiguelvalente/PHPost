{if $tsMensajes.data}
	{foreach from=$tsMensajes.data item=mp}
		<div class="dropdown-item{if $mp.mp_read_to == 0 || $mp.mp_read_mon_to == 0} unread{/if}">
         <a href="{$tsConfig.url}/mensajes/leer/{$mp.mp_id}" class="dropdown-link" title="{$mp.mp_subject}">
            <img class="dropdown-avatar" src="{$tsRoutes.storage.avatar}/user_{$mp.user_id}/avatar.webp" alt="{$mp.user_name}"/>
            <div class="content clearfix">
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