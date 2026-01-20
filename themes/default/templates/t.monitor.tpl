{include "main_header.tpl"}
	{if $tsAction == ''}
		{include "m.monitor_content.tpl"}
		{include "m.monitor_sidebar.tpl"}
   {else}
   	{include "m.monitor_menu.tpl"}
   	{include "m.monitor_listado.tpl"}
   {/if}
   <div style="clear: both;"></div>
{include "main_footer.tpl"}