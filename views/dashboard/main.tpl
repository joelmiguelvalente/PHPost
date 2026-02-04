<!DOCTYPE html>
<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{$tsTitle}</title>
<link rel="shortcut icon" href="{$tsRoutes.tema.images}/favicon.ico" type="image/x-icon" />
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preload" href="{$tsRoutes.assets.base}/fonts/Inter.woff2" as="font" type="font/woff2" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
{load file=['dialog'] type="css"}
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
               "display": ["Inter"]
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
<style type="text/tailwindcss">
	:root {
	 --font-base: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
	 --font-size-base: 14px;
	 --font-size-sm: 13px;
	 --font-size-lg: 16px;
	 --font-weight-normal: 400;
	 --font-weight-medium: 500;
	 --font-weight-bold: 600;

	 --color-bg: #f4f6f8;
	 --color-surface: #ffffff;
	 --color-surface-alt: #f1f3f5;

	 --color-text: #2b2f33;
	 --color-text-muted: #6b7280;
	 --color-border: #dcdfe3;

	 --color-primary: #1a73e8;
	 --color-primary-contrast: #ffffff;

	 --color-success: #1e8e3e;
	 --color-danger: #d93025;
	 --color-warning: #f9ab00;
	 --color-info: #1b9bd7;
	 --color-secondary: #5f6368;

	 --color-hover: color-mix(in srgb, currentColor 85%, #000);
	 --color-surface-hover: color-mix(in srgb, var(--color-surface) 92%, #000);
	 --color-primary-hover: color-mix(in srgb, var(--color-primary) 85%, #000);
	 --color-danger-hover: color-mix(in srgb, var(--color-danger) 85%, #000);
	 --color-success-hover: color-mix(in srgb, var(--color-success) 85%, #000);

	 --radius-xs: 3px;
	 --radius-sm: 4px;
	 --radius-md: 6px;
	 --radius-lg: 10px;

	 --border-width: 1px;
	 --border-base: var(--border-width) solid var(--color-border);

	 --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.08);
	 --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
	 --shadow-lg: 0 12px 30px rgba(0, 0, 0, 0.18);

	 --transition-fast: 0.15s ease;
	 --transition-base: 0.25s ease;
}
[data-theme="dark"] {
	 --color-bg: #0f1419;
	 --color-surface: #161b22;
	 --color-surface-alt: #1c222b;

	 --color-text: #e6e8eb;
	 --color-text-muted: #9aa0a6;
	 --color-border: #2a2f36;

	 --color-primary: #4c8dff;
	 --color-primary-contrast: #0f1419;

	 --color-success: #34a853;
	 --color-danger: #ea4335;
	 --color-warning: #fbbc04;
	 --color-info: #5bbcff;
	 --color-secondary: #9aa0a6;

	 --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.4);
	 --shadow-md: 0 6px 18px rgba(0, 0, 0, 0.6);
	 --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.7);
}
   body {
      font-family: 'Inter', sans-serif;
   }
   .form-input:focus {
      border-color: #1e70b8 !important;
      box-shadow: 0 0 0 2px rgba(30, 112, 184, 0.2);
	   &.input-error {
	      border-color: #dc2626 !important;
		   &:focus {
		      box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
		   }
	   }
   }
  .sidebar-link-active {
    background-color: rgba(30, 111, 184, 0.12);
    color: #1e6fb8;
    font-weight: 500;
  }
  .dark .sidebar-link-active {
    background-color: rgba(76, 141, 255, 0.2);
    color: #4c8dff;
  }
</style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display min-h-screen transition-colors duration-300 min-h-screen">

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
			  		{include "$tsPage/m.{$tsPage}_{$tsAction}.tpl"}
			  	</div>
			</section>

		</main>
		<footer class="bg-white bottom-0 left-0 z-20 w-full p-4 bg-neutral-primary-soft border-t border-default shadow-sm md:flex md:items-center md:justify-between md:p-6">
		   <span class="text-sm text-body sm:text-center">© 2025-{$smarty.now|date_format:'Y'} <a href="{$tsConfig.url}" class="hover:underline">{$tsConfig.titulo}</a>. All Rights Reserved.</span>
		   <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-body sm:mt-0">
		      <li><a href="#" class="hover:underline me-4 md:me-6">About</a></li>
		      <li><a href="#" class="hover:underline me-4 md:me-6">Privacy Policy</a></li>
		      <li><a href="#" class="hover:underline me-4 md:me-6">Licensing</a></li>
		      <li><a href="#" class="hover:underline">Contact</a></li>
		   </ul>
		</footer>
	</div>

</body>
</html>