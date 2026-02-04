<aside id="sidebar" class="hidden md:flex flex-col bg-white dark:bg-background-dark border-r border-gray-200 dark:border-gray-700 transition-all duration-300 w-64 shrink-0 overflow-y-auto">
  <!-- Header sidebar -->
  	<div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
    	<span class="font-semibold text-sm">Menú</span>
    	<button onclick="toggleSidebar()" class="text-gray-500 hover:text-primary" title="Colapsar">
      	<span class="material-symbols-outlined">chevron_left</span>
    	</button>
  	</div>

  	<!-- Nav -->
  	<nav class="flex-1 p-2 space-y-2 text-sm">
  		{include "$tsPage/m.{$tsPage}_sidemenu.tpl"}
	</nav>

</aside>