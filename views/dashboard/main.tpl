<!DOCTYPE html>
<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{$tsTitle}</title>
<link rel="shortcut icon" href="{$tsRoutes.tema.images}/favicon.ico" type="image/x-icon" />
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preload" href="{$tsRoutes.assets.base}/fonts/Inter.woff2" as="font" type="font/woff2" crossorigin="anonymous">
{load file=['main','dialog'] type="css"}
<script>
const global_data = {
   app: {
      domain:'{$tsRoutes.domain}',
      title: '{$tsConfig.titulo}',
      slogan: '{$tsConfig.slogan}',
   	publicKey: '{$publicKey}'
   }
};
const route = {
   url:'{$tsConfig.url}',
   img:'{$tsRoutes.tema.images}',
   smiles:'{$tsConfig.url}/files/smiles'
}
</script>
{if $tsPage == 'registro'}
<script src="https://www.google.com/recaptcha/api.js?render={$publicKey}"></script>
{/if}
{load file=['jquery.min','jquery.plugins','main',$tsPage] type="js"}
<script id="tailwind-config"> 
tailwind.config = { 
	darkMode: "class", 
	theme: { 
		extend: { 
			colors: { 
				"primary": "#1e6fb8", 
				"background-light": "#f5f6f7", 
				"background-dark": "#121920", 
				"error": "#dc2626", 
				"success": "#0FB232", 
				"secondary": "#D2D2D2" 
			}, 
			fontFamily: {
			   sans: ["Inter", "ui-sans-serif", "system-ui"]
			},
			borderRadius: { 
				"DEFAULT": "0.25rem", 
				"lg": "0.5rem", 
				"xl": "0.75rem", 
				"full": "9999px" 
			}, 
		}, 
	}, 
} 
</script>
</head>
<body class="font-sans bg-background-light dark:bg-background-dark font-display min-h-screen transition-colors duration-300 min-h-screen">

	<div class="layout-container flex min-h-screen flex-col">
		<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-[#dce1e5] dark:border-[#2d3748] bg-white dark:bg-background-dark px-6 py-4 shadow-sm">
			<div class="flex items-center gap-4 text-primary">
				<button onclick="toggleSidebarMobile()" class="md:hidden text-gray-600 dark:text-gray-300">
					<span class="material-symbols-outlined">menu</span>
				</button>
				<h2 class="text-primary text-xl font-bold leading-tight tracking-tight">{$tsConfig.titulo}</h2>
			</div>
			<div class="hidden md:flex flex-1 justify-end gap-8">
				<div class="flex items-center gap-9">
					<a class="text-[#121517] dark:text-gray-300 text-sm font-medium leading-normal hover:text-primary transition-colors" href="{$tsRoutes.url}">Página principal</a>
				</div>
			</div>
		</header>
		<main class="flex flex-1 overflow-hidden w-full">
			<!-- Sidebar -->
			{include "aside.tpl"}

			<!-- Section -->
			<section class="flex-1 min-w-0 overflow-y-auto p-4 md:p-6 bg-background-light dark:bg-background-dark">
			  	<div class="max-w-6xl mx-auto">
			  		{if $tsPage === 'moderacion'}
			  			{include "$tsPage/m.{$tsPage}_{$tsPlantilla}.tpl"}
			  		{else}
			  			{include "$tsPage/m.{$tsPage}_{$tsAction}.tpl"}
			  		{/if}
			  	</div>
			</section>

		</main>
		<footer class="bg-white bottom-0 left-0 z-20 w-full p-4 bg-neutral-primary-soft border-t border-default shadow-sm md:flex md:items-center md:justify-between md:p-6">
		   <span class="text-sm text-body sm:text-center">© 2025-{$smarty.now|date_format:'Y'} <a href="{$tsConfig.url}" class="hover:underline">{$tsConfig.titulo}</a>. All Rights Reserved.</span>
		   <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-body sm:mt-0">
		      <li><a href="{$tsRoutes.url}/pages/terminos-y-condiciones/" class="hover:underline me-4 md:me-6">Términos</a></li>
		      <li><a href="{$tsRoutes.url}/pages/privacidad/" class="hover:underline me-4 md:me-6">Privacidad</a></li>
		      <li><a href="{$tsRoutes.url}/pages/protocolo/" class="hover:underline me-4 md:me-6">Protocolo</a></li>
		      <li><a href="{$tsRoutes.url}/pages/ayuda/" class="hover:underline">Ayuda</a></li>
		   </ul>
		</footer>
	</div>

</body>
</html>