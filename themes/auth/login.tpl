<!-- Headline -->
<div class="text-center">
	<h1 class="text-[#121517] dark:text-white tracking-light text-[32px] font-bold leading-tight pb-2 pt-6">¡Hola de nuevo!</h1>
	<p class="text-[#657686] dark:text-gray-400 text-base font-normal">Accede a tu cuenta para continuar en la inteligencia colectiva.</p>
</div>
<!-- Login Card -->
<div class="bg-white dark:bg-[#1c2632] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.08)] overflow-hidden border border-gray-100 dark:border-gray-800">
	<div class="py-4 text-center display-message p-8" style="display:none;"></div>
	<form method="POST" class="p-8 flex flex-col gap-6" id="LoginForm">
		<!-- Username Field -->
		<div class="flex flex-col gap-2">
			<label class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal" for="username">Usuario o Correo Electrónico</label>
			<input class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 text-base font-normal transition-all" placeholder="JohnDoe / johndoe@example.com" name="username" id="username" type="text"/>
			<span data-label="username" class="text-xs italic font-medium ml-1"></span>
		</div>
		<!-- Password Field -->
		<div class="flex flex-col gap-2">
			<div class="flex justify-between items-center">
				<label class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal" for="password">Contraseña</label>
				<span class="text-primary text-xs font-semibold hover:underline cursor-pointer" id="forgotPassword">¿La olvidaste?</span>
			</div>
			<div class="relative">
				<input class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 pr-10 text-base font-normal transition-all" placeholder="••••••••" name="password" id="password" type="password"/>
				<div class="absolute right-3 top-1/2 -translate-y-2 text-[#657686] cursor-pointer seePassword" data-icon="visibility">
					<span class="material-symbols-outlined" style="pointer-events: none;">visibility</span>
				</div>
			<span data-label="password" class="text-xs italic font-medium ml-1"></span>
			</div>
		</div>
		<!-- Options -->
		<div class="flex items-center gap-2">
			<input class="w-4 h-4 rounded text-primary border-gray-300 focus:ring-primary" name="remember" id="remember" type="checkbox"/>
			<label class="text-[#657686] dark:text-gray-400 text-sm font-medium cursor-pointer" for="remember">Recordarme en este dispositivo</label>
		</div>
		<!-- Login Button -->
		<button class="w-full h-12 bg-primary hover:bg-[#1a62a3] text-white rounded-lg font-bold text-base transition-all shadow-md active:scale-[0.98]" id="btn-login">Ingresar</button>

      <div class="text-center py-1">
         <!-- <a href="javascript:remind_password();">&#191;Olvidaste tu contrase&#241;a?</a> -->
         <span class="text-[#121517] dark:text-gray-300 text-sm cursor-pointer" id="emailVerify">&#191;No lleg&oacute; el correo de validaci&oacute;n?</span>
      </div>

		<div class="flex items-center gap-4 py-1">
			<div class="flex-1 h-[1px] bg-[#dce1e5] dark:bg-[#374151]"></div>
			<p class="text-[#121517] dark:text-gray-300 text-sm">¿Aún no eres parte de la comunidad? <a class="text-primary font-bold hover:underline ml-1" href="{$tsRoute.url}/registro/">Regístrate gratis</a></p>
			<div class="flex-1 h-[1px] bg-[#dce1e5] dark:bg-[#374151]"></div>
		</div>

		{*
		<div class="flex items-center gap-4 py-2">
			<div class="flex-1 h-[1px] bg-[#dce1e5] dark:bg-[#374151]"></div>
			<span class="text-[#657686] text-xs font-medium uppercase tracking-wider">o continuar con</span>
			<div class="flex-1 h-[1px] bg-[#dce1e5] dark:bg-[#374151]"></div>
		</div>
		<!-- Social Buttons -->
		<div class="flex gap-4">
			<button class="flex-1 flex items-center justify-center h-11 rounded-lg border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
				<svg class="w-5 h-5" viewbox="0 0 24 24">
					<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
					<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
					<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
					<path d="M12 5.38c1.62 0 3.06.56 4.21 1.66l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
				</svg>
			</button>
			<button class="flex-1 flex items-center justify-center h-11 rounded-lg border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
				<svg class="w-5 h-5 text-black dark:text-white" fill="currentColor" viewbox="0 0 24 24">
					<path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.92.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.137 20.164 22 16.418 22 12c0-5.523-4.477-10-10-10z"></path>
				</svg>
			</button>
		</div>*}
	</form>
</div>