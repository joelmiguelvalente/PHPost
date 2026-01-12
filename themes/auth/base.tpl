<!DOCTYPE html>
<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{$tsTitle}</title>
<link rel="shortcut icon" href="{$tsRoutes.tema.images}/favicon.ico" type="image/x-icon" />
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
{load file=['jquery.min','jquery.plugins',$tsPage] type="js"}
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
</style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display min-h-screen transition-colors duration-300">

	<div class="layout-container flex h-full grow flex-col">
		<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-[#dce1e5] dark:border-[#2d3748] bg-white dark:bg-background-dark px-6 md:px-40 py-4 shadow-sm">
			<div class="flex items-center gap-4 text-primary">
				<h2 class="text-primary text-xl font-bold leading-tight tracking-tight">{$tsConfig.titulo}</h2>
			</div>
			<div class="hidden md:flex flex-1 justify-end gap-8">
				<div class="flex items-center gap-9">
					<a class="text-[#121517] dark:text-gray-300 text-sm font-medium leading-normal hover:text-primary transition-colors" href="{$tsRoutes.url}">Explorar</a>
				</div>
			</div>
		</header>
		<main class="flex-1 flex flex-col items-center justify-center p-4">
			<div class="w-full max-w-[440px] flex flex-col gap-6">
				{include "$tsPage.tpl"}
				<div class="flex flex-wrap justify-center gap-x-6 gap-y-2 pb-10">
					<a class="text-[#657686] text-xs hover:text-primary transition-colors" href="{$tsRoutes.url}/pages/terminos-y-condiciones/">Términos</a>
					<a class="text-[#657686] text-xs hover:text-primary transition-colors" href="{$tsRoutes.url}/pages/privacidad/">Privacidad</a>
					<a class="text-[#657686] text-xs hover:text-primary transition-colors" href="{$tsRoutes.url}/pages/protocolo/">Protocolo</a>
					<a class="text-[#657686] text-xs hover:text-primary transition-colors" href="{$tsRoutes.url}/pages/ayuda/">Ayuda</a>
				</div>
			</div>
		</main>
	</div>
	<div class="fixed top-0 left-0 w-full h-1 bg-primary z-50"></div>

</body>
</html>