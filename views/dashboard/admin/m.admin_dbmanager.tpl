<h1 class="text-xl font-semibold mb-4">Administrar base de datos</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text=$tsMessage.msg color="{if $tsMessage.type == 'success'}green{else}red{/if}" show=$tsSave}

	<div class="flex gap-1 mb-4">
      <a href="{$tsConfig.url}/admin/dbmanager" class="tab px-4 py-2 text-sm font-medium text-{if empty($tsAct)}indigo{else}slate{/if}-400 {if empty($tsAct)}border-b-2 border-indigo-400{/if} -mb-px transition-colors">💾 Backup</a>
      <a href="{$tsConfig.url}/admin/dbmanager?act=mantenimiento" class="tab px-4 py-2 text-sm font-medium text-{if $tsAct == 'mantenimiento'}indigo{else}slate{/if}-400 {if $tsAct == 'mantenimiento'}border-b-2 border-indigo-400{/if} -mb-px transition-colors">🔧 Mantenimiento</a>
      <a href="{$tsConfig.url}/admin/dbmanager?act=truncar" class="tab px-4 py-2 text-sm font-medium text-{if $tsAct == 'truncar'}indigo{else}slate{/if}-400 {if $tsAct == 'truncar'}border-b-2 border-indigo-400{/if} -mb-px transition-colors">🗑 Vaciar tablas</a>
      <a href="{$tsConfig.url}/admin/dbmanager?act=historial" class="tab px-4 py-2 text-sm font-medium text-{if $tsAct == 'historial'}indigo{else}slate{/if}-400 {if $tsAct == 'historial'}border-b-2 border-indigo-400{/if} -mb-px transition-colors">📁 Historial de backups</a>
   </div>

   <div id="tab-{if empty($tsAct)}backup{else}{$tsAct}{/if}" class="tab-content">
   	{if empty($tsAct)}
   		{include "admin/dbmanager/m.backup.tpl"}
   	{else}
   		{include "admin/dbmanager/m.$tsAct.tpl"}
   	{/if}
   </div>

</div>