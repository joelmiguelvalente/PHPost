<ol id="filterTodos" class="filter-list" style="display:block;">
   {foreach from=$tsComments key=i item=c}
      <li>
         {if $c.post_status != 0 || $c.user_activo == 0 || $c.user_baneado || $c.c_status != 0}<a href="{$tsConfig.url}/@{$c.user_name}"><strong style="color: {if $c.post_status == 3} #BBB {elseif $c.post_status == 1} purple {elseif $c.post_status == 2} rosyBrown {elseif $c.c_status == 1} coral{elseif $c.user_activo == 0} brown {elseif $c.user_baneado == 1} orange {/if};" class="qtip" title="{if $c.post_status == 3} El post se encuentra en revisi&oacute;n{elseif $c.post_status == 1} El post se encuentra oculto por acumulaci&oacute;n de denuncias {elseif $c.post_status == 2} El post se encuentra eliminado {elseif $c.c_status == 1} El comentario est&aacute; oculto{elseif $c.user_activo == 0}El autor del comentario tiene la cuenta desactivada{elseif $c.user_baneado == 1}El autor del comentario tiene la cuenta suspendida{/if}">{$c.user_name}</strong></a>{else}<a href="{$tsConfig.url}/@{$c.user_name}"><strong>{$c.user_name}</strong></a> {/if} &raquo; 
         <a href="{$tsConfig.url}/posts/{$c.c_seo}/{$c.post_id}/{$c.post_title|seo}.html#comentarios-abajo" class="truncate">{$c.post_title}</a>
      </li>
   {foreachelse}
      <li class="alert-empty" style="text-align: center;display: block;">Sin comentarios</li>
   {/foreach}
</ol>