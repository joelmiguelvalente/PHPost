<div class="card px-4 py-2">
   <div class="card-head flex justify-between items-center py-2">
      Vaciar tablas seguras
     	<span style="font-size:.72rem;color:#C56A19">⚠ Esta acción es irreversible</span>
   </div>
   <div class="trunc-grid">
      {foreach $tsTruncaTables key=table item=desc}
         <div class="trunc-item rounded px-3 py-2 flex justify-between items-center">
            <div>
               <div class="name">{$table}</div>
               <div class="desc">{$desc}</div>
            </div>
            <button class="inline-flex items-center gap-1 rounded px-3 py-1 text-xs font-semibold bg-red-700 text-red-100 hover:bg-red-600 transition-colors cursor-pointer" onclick="confirmTruncate('{$table}')">Vaciar</button>
         </div>
     	{/foreach}
   </div>
</div>
<!-- Form oculto para truncar -->
<form method="POST" id="truncate-form">
  	<input type="hidden" name="action" value="truncate">
  	<input type="hidden" name="table"  id="truncate-table" value="">
</form>