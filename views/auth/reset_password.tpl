{if isset($tsAviso)}
	<div class="text-center">
		<h1 class="text-[#121517] dark:text-white tracking-light text-[32px] font-bold leading-tight pb-2 pt-6">{$tsAviso.titulo}</h1>
		<p class="text-[#657686] dark:text-gray-400 text-base font-normal">{$tsAviso.mensaje}</p>
	</div>
	<div class="bg-white dark:bg-[#1c2632] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.08)] overflow-hidden border border-gray-100 dark:border-gray-800 p-8 text-center">
		<a class="inline-block h-12 px-6 bg-primary hover:bg-[#1a62a3] text-white rounded-lg font-bold text-base leading-[48px] transition-all shadow-md active:scale-[0.98]" href="{$tsRoutes.url}/login/">Iniciar sesión</a>
	</div>
{elseif isset($tsForm)}
	<div class="text-center">
		<h1 class="text-[#121517] dark:text-white tracking-light text-[32px] font-bold leading-tight pb-2 pt-6">Restablecer contraseña</h1>
		<p class="text-[#657686] dark:text-gray-400 text-base font-normal">Ingresa tu nueva contraseña para recuperar el acceso a tu cuenta.</p>
	</div>
	<div class="bg-white dark:bg-[#1c2632] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.08)] overflow-hidden border border-gray-100 dark:border-gray-800">
		<form method="POST" class="p-8 flex flex-col gap-5">
			<div class="flex flex-col gap-1.5">
				<label class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal" for="pass">Nueva contraseña</label>
				<div class="relative">
					<input class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 pr-10 text-base font-normal transition-all" placeholder="••••••••" type="password" name="pass" id="pass" required/>
					<div class="absolute right-3 top-1/2 -translate-y-2 text-[#657686] cursor-pointer seePassword">
						<span class="material-symbols-outlined" style="pointer-events: none;">visibility</span>
					</div>
				</div>
			</div>
			<div class="flex flex-col gap-1.5">
				<label class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal" for="pass_confirm">Confirmar contraseña</label>
				<div class="relative">
					<input class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 pr-10 text-base font-normal transition-all" placeholder="••••••••" type="password" name="pass_confirm" id="pass_confirm" required/>
					<div class="absolute right-3 top-1/2 -translate-y-2 text-[#657686] cursor-pointer seePassword">
						<span class="material-symbols-outlined" style="pointer-events: none;">visibility</span>
					</div>
				</div>
			</div>
			<button type="submit" class="w-full h-12 bg-primary hover:bg-[#1a62a3] text-white rounded-lg font-bold text-base transition-all shadow-md active:scale-[0.98]">Cambiar contraseña</button>
			<div class="flex items-center gap-4 py-1">
				<div class="flex-1 h-[1px] bg-[#dce1e5] dark:bg-[#374151]"></div>
				<p class="text-[#657686] dark:text-gray-400 text-sm"><a class="text-primary font-bold hover:underline" href="{$tsRoutes.url}/login/">Volver al inicio</a></p>
				<div class="flex-1 h-[1px] bg-[#dce1e5] dark:bg-[#374151]"></div>
			</div>
		</form>
	</div>
{/if}
