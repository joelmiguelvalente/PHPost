{include file='sections/main_header.tpl'}
<input type="hidden" name="avatarUid" value="{$tsUser->uid}">
<input type="hidden" name="avatarCurrent" value="{$tsUser->avatar}">
<div class="tabbed-d">
   <div class="tabbed-left">
      <ul class="menu-tab">
         {foreach $tsMenuCuenta key=item item=label}
            <li class="menu-item{if $tsAccion == $item} active{/if}">
               <a href="{$tsConfig.url}/cuenta/{$item}" class="menu-link">{$label}</a>
            </li>
         {/foreach}
      </ul>
      <form class="horizontal" method="post" name="editarcuenta">
         <input type="hidden" name="pagina" value="{$tsAccion}">
         {include file="modules/m.cuenta_$tsAccion.tpl"}
      </form>
   </div>
   <div class="tabbed-right">
	   {include file='modules/m.cuenta_sidebar.tpl'}
   </div>
</div>         
{include file='sections/main_footer.tpl'}