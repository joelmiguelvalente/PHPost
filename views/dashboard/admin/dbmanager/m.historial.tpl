<div class="card px-4 py-2">
   <div class="card-head flex justify-between items-center py-3">Backups guardados en storage/backups/</div>
   {if empty($backupsList)}
      <div class="empty">No hay backups generados todavía.</div>
   {else}
	   <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
	      <table class="min-w-full border-collapse text-sm">
	         {include "dashboard/table/Thead.tpl" fields=[
	            "Nombre",
	            "Peso",
	            "Fecha",
	            "Acciones"
	         ]}
	         <tbody class="divide-y dark:divide-gray-700">
      			{foreach $backupsList key=i item=b}
	               <tr id="btrbackup_{$i}" class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
           				<td class="px-3 py-2 text-gray-600 dark:text-gray-400 bname">{$b.filename}</td>
           				<td class="px-3 py-2 bmeta">{$b.size_kb} KB</td>
           				<td class="px-3 py-2 bdate">{$b.date}</td>
           				<td class="px-3 py-2 bactions">
                        <div class="flex justify-center gap-2">
                        	<form method="POST">
                        		<input type="hidden" name="action" value="download_backup">
                        		<input type="hidden" name="filename" value="{$b.filename}">
				            		<button type="submit" class="inline-flex items-center gap-1 rounded px-3 py-1 text-xs font-semibold bg-green-700 text-white hover:bg-green-600 transition-colors cursor-pointer">⬇ Descargar</button>
				            	</form>
				            	<button data-action="delete" data-filename="{$b.filename}" data-id="btrbackup_{$i}" class="inline-flex items-center gap-1 rounded px-3 py-1 text-xs font-semibold bg-red-700 text-white hover:bg-red-600 transition-colors cursor-pointer">🗑 Eliminar</button>
				            </div>
				         </td>
				      </tr>
				   {/foreach}
				</tbody>
			</table>
		</div>
   {/if}
</div>