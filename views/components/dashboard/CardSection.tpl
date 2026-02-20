<section class="bg-white dark:bg-slate-800 rounded-xl shadow p-5 max-h-[400px] overflow-auto">
   <header class="flex justify-between items-center border-b pb-2 mb-3">
      <h3 class="font-semibold text-lg">{$header}</h3>
      <span class="text-xl font-bold text-primary">{$total}</span>
   </header>

   <ul class="space-y-2 text-sm">
   	{foreach $list item=l}
        	<li class="flex justify-between">
            <span>{$l.title}</span>
            <span class="font-medium">{$l.total}</span>
        	</li>
      {/foreach}
</section>