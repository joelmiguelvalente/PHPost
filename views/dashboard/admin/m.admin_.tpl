{load file='github.widget' type="css"}
{load file=['timeago.min', 'github.widget'] type="js"}
<h1 class="text-xl font-semibold mb-4">Centro de Administración</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	<section class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-surface p-5 shadow-sm">
	  	<h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bienvenido(a), {$tsUser->nick} 👋</h1>
	  	<p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Este es tu <strong>Centro de Administración de PHPost</strong>. Desde aquí podés modificar la configuración del sitio, administrar usuarios, gestionar posts y mucho más.</p>
	  	<p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Si tenés algún problema, revisá la sección <a href="{$tsConfig.url}/admin/creditos" class="text-primary hover:underline font-medium">Soporte y Créditos</a>. Si esa información no es suficiente, podés <a href="https://github.com/joelmiguelvalente" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-medium">visitarnos</a> para solicitar ayuda. </p>
	</section>

	<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
		<div class="lg:col-span-2">
			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3">
				<h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">PHPost en directo</h5>
				<ul id="news_pp" class="pp_list divide-y divide-default">
					<div class="phpostAlfa flex gap-3 rounded-md border border-yellow-200 bg-yellow-50 text-yellow-800 p-4 text-sm align-center dark:border-yellow-900 dark:bg-yellow-950 dark:text-yellow-300">Cargando...</div>
				</ul>
			</div>

			<div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-surface overflow-hidden my-3 font-mono">
			   <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
			      <span class="text-sm">&#9906;</span>
			      <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 flex-1">Último Commit</span>
			      <select id="branchSelector" class="text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 cursor-pointer"></select>
			   </div>
			   <div id="lastCommit" class="p-3">
			      <div class="text-xs text-gray-400 text-center py-2">Cargando...</div>
			   </div>
			</div>

		</div>
		<div class="phpost version lg:col-span-1">
			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3 mb-4">
		      <h5 class="text-sm font-semibold uppercase tracking-wide mb-4 flex items-center">
		         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04m14.562 10.848a12.059 12.059 0 01-5.944 5.48m-5.944-5.48a12.059 12.059 0 01-5.944-5.48M12 7V12l3 3"></path></svg>
		         Salud del Sistema
		      </h5>

		      <div class="py-2 flex justify-between">
		         <span>Protocolo de Red</span>
		         <span class="font-medium {if $smarty.server.HTTPS}text-green-600{else}text-amber-600{/if}">
		            {if $smarty.server.HTTPS}Seguro (HTTPS){else}No Seguro (HTTP){/if}
		         </span>
		      </div>
		      <div class="py-2 flex justify-between">
		         <span>Exposición de Errores</span>
		         <span class="font-medium {if $tsVersion.php.display_errors}text-red-600{else}text-green-600{/if}">
		            {if $tsVersion.php.display_errors}Activo (Riesgo){else}Oculto (Seguro){/if}
		         </span>
		      </div>
		      <div class="py-2 flex justify-between">
		         <span>Límite de Memoria</span>
		         <span class="font-medium text-gray-800 dark:text-gray-200">{$tsVersion.php.memory_limit}</span>
		      </div>
		      <div class="py-2 flex justify-between">
		         <span>Límite de Subida</span>
		         <span class="font-medium text-gray-800 dark:text-gray-200">{$tsVersion.php.upload_max_filesize}</span>
		      </div>
		   </div>

			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3 mb-4">
				<h5 class="text-sm font-semibold uppercase tracking-wide mb-4 flex items-center">PHPost Risus</h5>
				<ul id="version_pp" class="pp_list">
					<li>
	            	<span class="text-lg font-medium text-heading">Versión instalada</span>
	            	<small class="text-body block">{$tsConfig.version}</small>
					</li>
				</ul>
			</div>
			
			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3 mb-4">
				<h5 class="text-sm font-semibold uppercase tracking-wide mb-4 flex items-center">Administradores</h5>
				<ul class="pp_list">                                    
					{foreach from=$tsAdmins item=admin}
						<li><div class="title"><a href="{$tsConfig.url}/perfil/{$admin.user_name}" class="hovercard" uid="{$admin.user_id}">{$admin.user_name}</a></div></li>
					{/foreach}
				</ul>
			</div>
			
			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3 mb-4">
				<h5 class="text-sm font-semibold uppercase tracking-wide mb-4 flex items-center">Instalaciones</h5>
				<ul>
					<li class="py-2 flex justify-between">
						<span>Fundación</span>
						<strong title="{$tsInstalled.foundation|fecha:'full_datetime'}">{$tsInstalled.foundation|hace:true}</strong>
					</li>
					<li class="py-2 flex justify-between">
						<span>Actualizado</span>
						<strong title="{$tsInstalled.upgrade|fecha:'full_datetime'}">{$tsInstalled.upgrade|hace:true}</strong>
					</li>
				</ul>
			</div>                                    
		</div>
	</div>
</div>
<script nonce="{CSP_NONCE}">
document.addEventListener('DOMContentLoaded', function () {
   if (typeof jQuery === 'undefined') {
      console.error('jQuery no cargó');
      return;
   }
   // {literal}
	$(function () {
	   const $news = $('#news_pp');
	   const $versions = $('#version_pp');
	   // ---- Feed version ----
	   $.get(route.url + '/feed-version').done(response => {
	      const { name, latest, required_php } = response;
	      let html = `<li class="flex flex-col py-2">
	         <span class="text-lg font-medium text-heading">Última versión</span>
	         <small class="text-body block">${name} ${latest} (php ${required_php})</small>
	      </li>`;
	      
	      $versions.append(html);
	   }).fail((_, status, error) => console.error('Error feed-version:', status, error));
	   // ---- Feed soporte ----
	   $.getJSON(route.url + '/feed-support').done(response => {
	      if (!Array.isArray(response)) {
	         console.error('Respuesta inválida feed-support', response);
	         return;
	      }
	      let html = '';
	      for (const { link, title, info } of response) {
	         html += `<li class="flex flex-col py-2">
	            <a href="${link}" class="text-lg font-medium text-heading" target="_blank" rel="noopener noreferrer">${title}</a>
	            <div class="text-body">${info}</div>
	         </li>`;
	      }
	      $news.html(html);
	   }).fail((_, status, error) => console.error('Error feed-support:', status, error));
	});
   // {/literal}
});
</script>
