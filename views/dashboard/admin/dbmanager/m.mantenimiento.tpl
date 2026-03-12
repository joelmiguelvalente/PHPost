<form method="POST">
   <input type="hidden" name="action" id="maint-action" value="optimize">
   <div class="card px-4 py-2">
      <div class="card-head flex justify-between items-center">Seleccionar tablas</div>
      <div class="select-bar py-3 flex gap-3">
         <span class="color-indigo-800" role="button" onclick="selectAll('maint-form', true)">Seleccionar todo</span>
         <span class="color-indigo-800" role="button" onclick="selectAll('maint-form', false)">Deseleccionar todo</span>
      </div>
      <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
       	<table class="tbl min-w-full border-collapse text-sm" id="maint-form">
           	<thead class="bg-gray-100 dark:bg-surface-alt text-gray-700 dark:text-gray-300">
               <tr>
                  <th class="px-3 py-2 text-left font-medium" style="width:40px"></th>
                  <th class="px-3 py-2 text-left font-medium">Tabla</th>
                  <th class="px-3 py-2 text-left font-medium">Filas</th>
                  <th class="px-3 py-2 text-left font-medium">Tamaño</th>
                  {if !empty($checkResults)}
                     <th class="px-3 py-2 text-left font-medium">Estado</th>
                  {/if}
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
                        <td class="px-3 py-2">{$t.name}</td>
                        <td class="px-3 py-2">{$t.rows}</td>
                        <td class="px-3 py-2">{$t.size_kb} KB</td>
                        {if $checkResults && isset($checkResults[$t.name])}
                           <td class="px-3 py-2">
                           	{assign "cr"  $checkResults[$t.name]}
                              <span style="color:var(--{if $cr.status === 'status'}success{else}error{/if}); ?>">
                                 {$cr.status}: {$cr.message}
                              </span>
                           </td>
                        {/if}
                    	</tr>
                  {/foreach}
               {/foreach}
           	</tbody>
            <tfoot class="bg-gray-50 dark:bg-surface-alt">
               <tr>
                  <td colspan="{if $checkResults}5{else}4{/if}" class="px-3 py-3 text-right">
				         <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-success px-4 py-2 text-sm font-medium text-white hover:bg-success/90" onclick="document.getElementById('maint-action').value='optimize'">⚡ Optimizar</button>

				         <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-warning px-4 py-2 text-sm font-medium text-white hover:bg-warning/90" onclick="document.getElementById('maint-action').value='repair'">🔨 Reparar</button>

				         <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90" onclick="document.getElementById('maint-action').value='check'">🔍 Verificar</button>
                  </td>
               </tr>
            </tfoot>
       	</table>
      </div>
   </div>
</form>