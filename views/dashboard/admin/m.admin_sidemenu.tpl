<div class="sidebar-group">
  	<button type="button" onclick="toggleGroup(this)" class="flex w-full items-center gap-3 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 font-medium text-left">
    	<span class="material-symbols-outlined text-base">dashboard</span>
    	<span class="sidebar-text flex-1">General</span>
    	<span class="material-symbols-outlined text-sm sidebar-text transition-transform">expand_more</span>
  	</button>

  	<!-- SUBMENU -->
   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
      <a href="{$tsConfig.url}/admin/" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Centro de Administraci&oacute;n</a>
      <a href="{$tsConfig.url}/admin/creditos" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Soporte y Cr&eacute;ditos</a>
   </div>
</div>

<div class="sidebar-group">
	<button type="button" onclick="toggleGroup(this)" class="flex w-full items-center gap-3 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 font-medium text-left">
	 	<span class="material-symbols-outlined text-base">settings</span>
	 	<span class="sidebar-text flex-1">Configuraci&oacute;n</span>
	 	<span class="material-symbols-outlined text-sm sidebar-text transition-transform">expand_more</span>
	</button>
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		<a href="{$tsConfig.url}/admin/configs" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Configuraci&oacute;n </a>
		<a href="{$tsConfig.url}/admin/temas" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Temas y apariencia</a>
		<a href="{$tsConfig.url}/admin/news" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Noticias</a>
		<a href="{$tsConfig.url}/admin/ads" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Publicidad</a>
	</div>
</div>

<div class="sidebar-group">
	<button type="button" onclick="toggleGroup(this)" class="flex w-full items-center gap-3 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 font-medium text-left">
	 	<span class="material-symbols-outlined text-base">security</span>
	 	<span class="sidebar-text flex-1">Control</span>
	 	<span class="material-symbols-outlined text-sm sidebar-text transition-transform">expand_more</span>
	</button>
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		<a href="{$tsConfig.url}/admin/medals" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Medallas</a>
		<a href="{$tsConfig.url}/admin/afs" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Afiliados</a>
		<a href="{$tsConfig.url}/admin/stats" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Estad&iacute;sticas</a>
		<a href="{$tsConfig.url}/admin/blacklist" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Bloqueos</a>
		<a href="{$tsConfig.url}/admin/badwords" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Censuras</a>
	</div>
</div>

<div class="sidebar-group">
	<button type="button" onclick="toggleGroup(this)" class="flex w-full items-center gap-3 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 font-medium text-left">
	 	<span class="material-symbols-outlined text-base">article</span>
	 	<span class="sidebar-text flex-1">Contenido</span>
	 	<span class="material-symbols-outlined text-sm sidebar-text transition-transform">expand_more</span>
	</button>
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		<a href="{$tsConfig.url}/admin/posts" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Todos los Posts</a>
		<a href="{$tsConfig.url}/admin/fotos" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Todas las Fotos</a>
		<a href="{$tsConfig.url}/admin/cats" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Categor&iacute;as</a>
	</div>
</div>

<div class="sidebar-group">
	<button type="button" onclick="toggleGroup(this)" class="flex w-full items-center gap-3 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 font-medium text-left">
	 	<span class="material-symbols-outlined text-base">group</span>
	 	<span class="sidebar-text flex-1">Usuarios</span>
	 	<span class="material-symbols-outlined text-sm sidebar-text transition-transform">expand_more</span>
	</button>
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		<a href="{$tsConfig.url}/admin/users" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Todos los Usuarios</a>
		<a href="{$tsConfig.url}/admin/sesiones" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Sesiones</a>
		<a href="{$tsConfig.url}/admin/nicks" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Cambios de Nicks</a>
		<a href="{$tsConfig.url}/admin/rangos" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800">Rangos de Usuarios</a>
	</div>
</div>