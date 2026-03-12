<form method="POST">
   <input type="hidden" name="action" value="backup">
   <div class="card px-4 py-2">
      <div class="card-head flex justify-between items-center">
         Seleccionar tablas para respaldar
         <span style="font-size:.75rem;color:var(--muted)">{$tsTablesTotal} tablas</span>
      </div>
      <div class="select-bar py-3 flex gap-3">
         <span role="button" onclick="selectAll('backup-form', true)">Seleccionar todo</span>
         <span role="button" onclick="selectAll('backup-form', false)">Deseleccionar todo</span>
      </div>
      <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
         <table class="tbl min-w-full border-collapse text-sm" id="backup-form">
            <thead class="bg-gray-100 dark:bg-surface-alt text-gray-700 dark:text-gray-300">
               <tr>
                  <th class="px-3 py-2 text-left font-medium" style="width:40px"></th>
                  <th class="px-3 py-2 text-left font-medium">Tabla</th>
                  <th class="px-3 py-2 text-left font-medium">Filas (aprox)</th>
                  <th class="px-3 py-2 text-left font-medium">Tamaño</th>
                  <th class="px-3 py-2 text-left font-medium">Motor</th>
               </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
               {foreach $tsTablesInfo key=prefix item=tables}
                  <tr class="prefix-header py-4 px-3">
                     <td colspan="5">Grupo: {$prefix}</td>
                  </tr>
                  {foreach $tables item=t}
                     <tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400"><input type="checkbox" name="tables[]" value="{$t.name}" checked></td>
                        <td class="px-3 py-2">
                           {$t.name}
                           {if $t.protected}<span class="badge badge-protected">protegida</span>{/if}
                        </td>
                        <td class="px-3 py-2">{$t.rows}</td>
                        <td class="px-3 py-2">{$t.size_kb} KB</td>
                        <td class="px-3 py-2">{$t.engine}</td>
                     </tr>
                  {/foreach}
               {/foreach}
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-surface-alt">
               <tr>
                  <td colspan="5" class="px-3 py-3 text-right">
                     <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">💾 Generar backup</button>
                  </td>
               </tr>
            </tfoot>
         </table>
      </div>
    </div>
</form>