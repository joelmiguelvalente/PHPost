{load file='github.widget' type="css"}
{load file=['timeago.min', 'github.widget'] type="js"}
<h1 class="text-xl font-semibold mb-4">Centro de Administraci&oacute;n</h1>
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
				<h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">PHPost Risus</h5>
				<ul id="version_pp" class="pp_list">
					<li>
	            	<span class="text-lg font-medium text-heading">Versi&oacute;n instalada</span>
	            	<small class="text-body block">{$tsConfig.version}</small>
					</li>
				</ul>
			</div>
			
			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3 mb-4">
				<h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">Administradores</h5>
				<ul class="pp_list">                                    
					{foreach from=$tsAdmins item=admin}
						<li><div class="title"><a href="{$tsConfig.url}/perfil/{$admin.user_name}" class="hovercard" uid="{$admin.user_id}">{$admin.user_name}</a></div></li>
					{/foreach}
				</ul>
			</div>
			
			<div class="bg-neutral-primary-soft block border border-default rounded-base shadow-xs p-3 mb-4">
				<h5 class="mb-3 text-2xl font-semibold tracking-tight text-heading leading-8">Instalaciones</h5>
				<ul>
					<li class="py-2 flex justify-between">
						<span>Fundaci&oacute;n</span>
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
<script>
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
	   $.get(route.url + '/feed-version.php').done(response => {
	      const { name, latest, required_php } = response;
	      let html = `<li class="flex flex-col py-2">
	         <span class="text-lg font-medium text-heading">Última versión</span>
	         <small class="text-body block">${name} ${latest} (php ${required_php})</small>
	      </li>`;
	      
	      $versions.append(html);
	   }).fail((_, status, error) => console.error('Error feed-version:', status, error));
	   // ---- Feed soporte ----
	   $.getJSON(route.url + '/feed-support.php').done(response => {
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