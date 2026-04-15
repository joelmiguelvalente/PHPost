<!DOCTYPE html>
<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>{$tsTitle}</title>
<link rel="shortcut icon" href="{$tsRoutes.tema.images}/favicon.ico" type="image/x-icon" />
<link rel="preload" href="{$tsRoutes.assets.base}/fonts/Inter.woff2" as="font" type="font/woff2" crossorigin="anonymous">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
{load file=['dialog'] type="css"}
<script>
const global_data = {
	app: {
		domain:'{$tsRoutes.domain}',
		title: '{$tsConfig.titulo}',
		slogan: '{$tsConfig.slogan}'
	}
};
const route = {
	url:'{$tsConfig.url}',
	img:'{$tsRoutes.tema.images}',
	smiles:'{$tsConfig.url}/files/smiles'
}
</script>
{load file=['jquery.min','jquery.plugins','mantenimiento','login'] type="js" cache=true}
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
</style>
</head>
<body class="font-sans bg-background-light dark:bg-background-dark font-display min-h-screen transition-colors duration-300">

	<div class="layout-container flex h-full grow flex-col">
		<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-[#dce1e5] dark:border-[#2d3748] bg-white dark:bg-background-dark px-6 md:px-40 py-4 shadow-sm">
			<div class="flex items-center gap-4 text-primary">
				<h2 class="text-primary text-xl font-bold leading-tight tracking-tight">{$tsConfig.titulo}</h2>
			</div>
			<div class="hidden md:flex flex-1 justify-end gap-8">
				{if $tsLogin}
					<div class="flex items-center gap-9">
						<a class="text-[#121517] dark:text-gray-300 text-sm font-medium leading-normal hover:text-primary transition-colors" href="{$tsRoutes.url}/login-salir">Cerrar sesión</a>
					</div>
				{/if}
			</div>
		</header>
		<main class="flex-1 flex flex-col items-center justify-center p-4">
		   <div class="w-full max-w-4xl rounded-lg bg-white shadow-sm">
		      <div class="grid grid-cols-1{if !$tsLogin} md:grid-cols-2{/if}">
		       
		         <div class="flex flex-col items-center justify-center p-6 text-center border-b md:border-b-0 md:border-r">
		            <h3 class="mb-4 text-xl font-semibold text-gray-800">{$tsConfig.offline_message}</h3>
		            <img src="{$tsRoutes.tema.images}/mantenimiento.gif" width="310" height="324" class="mx-auto" alt="Mantenimiento"/>
		         </div>
		         {if !$tsLogin}
			         <div class="flex flex-col justify-center p-6">
			            <h4 class="mb-3 text-lg font-semibold text-gray-700">Iniciar sesión</h4>
			            <form method="POST" id="LoginForm" class="space-y-3">
			               <div class="flex flex-col gap-2">
									<label class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal" for="username">Usuario o Correo Electrónico</label>
									<input class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 text-base font-normal transition-all" placeholder="JohnDoe / johndoe@example.com" name="username" id="username" type="text"/>
									<span data-label="username" class="text-xs italic font-medium ml-1"></span>
								</div>
								<div class="flex flex-col gap-2">
									<label class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal" for="password">Contraseña</label>
									<input class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 pr-10 text-base font-normal transition-all" placeholder="••••••••" name="password" id="password" type="password"/>
									<span data-label="password" class="text-xs italic font-medium ml-1"></span>
								</div>

			               <button type="submit" class="w-full rounded bg-gray-800 py-2 text-sm font-medium text-white hover:bg-gray-700">Ingresar</button>
			            </form>
			         </div>
			      {/if}
		      </div>

		      <!-- Links legales -->
		      <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 border-t p-4">
		         <a class="text-xs text-gray-500 hover:text-gray-700" href="{$tsRoutes.url}/pages/terminos-y-condiciones/">Términos</a>
		         <a class="text-xs text-gray-500 hover:text-gray-700" href="{$tsRoutes.url}/pages/privacidad/">Privacidad</a>
		         <a class="text-xs text-gray-500 hover:text-gray-700" href="{$tsRoutes.url}/pages/protocolo/">Protocolo</a>
		         <a class="text-xs text-gray-500 hover:text-gray-700" href="{$tsRoutes.url}/pages/ayuda/">Ayuda</a>
		      </div>

		   </div>
		</main>

	</div>
	<div class="fixed top-0 left-0 w-full h-1 bg-primary z-50"></div>

</body>
</html>
