{include "main_header.tpl"}
<div class="grid gap-3 grid-{if $tsAction != 'ver'}home{elseif $tsAction == 'album'}album{else}ver{/if}">
   {if $tsAction == ''}
      {include "m.fotos_home_content.tpl"}
      {include "m.fotos_home_sidebar.tpl"}
   {elseif $tsAction == 'agregar' || $tsAction == 'editar'}
      {include "m.fotos_add_form.tpl"}
      {include "m.fotos_add_sidebar.tpl"}
   {elseif $tsAction == 'ver'}
      {include "m.fotos_ver_left.tpl"}
      {include "m.fotos_ver_content.tpl"}
      {include "m.fotos_ver_right.tpl"}
   {elseif $tsAction == 'album'}
      {include "m.fotos_album.tpl"}
   {elseif $tsAction == 'favoritas'}
      <div class="alert-empty">En construcci&oacute;n</div>
   {/if}
</div>
{include "main_footer.tpl"}
