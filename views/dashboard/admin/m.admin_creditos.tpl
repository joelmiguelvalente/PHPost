<h1 class="text-xl font-semibold mb-4">Soporte y Cr&eacute;ditos</h1>
<div class="space-y-6">
   {include "dashboard/Alert.tpl" text="¡La caché de Smarty se ha limpiado correctamente!" color="green" show=$tsSave}
   <!-- Información de versiones -->
   <section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
      <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-4">Información de versiones</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
         <div>
            <div class="text-gray-500 dark:text-gray-400">Versión del script</div>
            <div class="font-medium">v{$tsConfig.version}</div>
         </div>

         <div>
            <div class="text-gray-500 dark:text-gray-400">PHP</div>
            <div class="font-medium"> 
               {$tsVersion.php.version}
               <span class="text-xs text-gray-400">({$tsVersion.php.sapi})</span>
            </div>
        </div>

         <div>
            <div class="text-gray-500 dark:text-gray-400">Base de datos</div>
            <div class="font-medium">
               {$tsVersion.database.engine|upper} {$tsVersion.database.version}
            </div>
         </div>

         <div>
            <div class="text-gray-500 dark:text-gray-400">Servidor</div>
            <div class="font-medium">
               {$tsVersion.server.software}
            </div>
         </div>

         <div>
            <div class="text-gray-500 dark:text-gray-400">Sistema Operativo</div>
            <div class="font-medium">
               {$tsVersion.server.os}
            </div>
         </div>
      </div>
   </section>

   <section class="rounded-lg border bg-gray-50 dark:bg-surface/50 p-5 shadow-sm">
      <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
         Mantenimiento del Fork
      </h2>
      <div class="flex flex-wrap gap-3">
         <a href="{$tsConfig.url}/admin/?act=limpiar-cache" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-25 transition">
            Limpiar Caché de Smarty
         </a>
         <a href="https://github.com/joelmiguelvalente/PHPost/issues" target="_blank" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition">
            Reportar Bug en GitHub
         </a>
      </div>
   </section>

   <section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
      <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-4">Información de extensiones</h2>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
         <div>
            <div class="text-gray-500 dark:text-gray-400">MBString</div>
               <div class="font-medium">
                  {if $tsVersion.extensions.mbstring}
                     <span class="text-green-600">Habilitado</span>
                  {else}
                     <span class="text-red-600">Requerido</span>
                  {/if}
               </div>
            </div>

            <div>
               <div class="text-gray-500 dark:text-gray-400">Upload Max Size</div>
               <div class="font-medium text-gray-800 dark:text-gray-200">
                  {$tsVersion.php.upload_max_filesize}
               </div>
            </div>

            <div>
               <div class="text-gray-500 dark:text-gray-400">Zlib (Compresión)</div>
               <div class="font-medium">
                  {if $tsVersion.extensions.zlib}
                     <span class="text-green-600">Activo</span>
                  {else}
                     <span class="text-amber-600">Inactivo</span>
                  {/if}
               </div>
            </div>
         <div>
            <div class="text-gray-500 dark:text-gray-400">GD</div>
            <div class="font-medium">
               {if $tsVersion.extensions.gd.enabled}
                  <span class="text-green-600">{$tsVersion.extensions.gd.version}</span>
               {else}
                  <span class="text-red-600">No instalada</span>
               {/if}
            </div>
         </div>
         <div>
            <div class="text-gray-500 dark:text-gray-400">cURL</div>
            <div class="font-medium">
               {if $tsVersion.extensions.curl}
                  <span class="text-green-600">Habilitado</span>
               {else}
                  <span class="text-red-600">No habilitada</span>
               {/if}
           </div>
         </div>
         <div>
            <div class="text-gray-500 dark:text-gray-400">OpenSSL</div>
            <div class="font-medium">
               {if $tsVersion.extensions.openssl}
                  <span class="text-green-600">Habilitado</span>
               {else}
                  <span class="text-red-600">No habilitada</span>
               {/if}
           </div>
         </div>
      </div>

   </section>

  <!-- Créditos -->
  <section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
      Créditos
    </h2>

    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
      La arquitectura original fue desarrollada por <span class="text-primary font-medium">JNeutron</span>. Las versiones subsiguientes fueron mantenidas por <span class="text-primary font-medium">Isidro</span> y la <span class="text-primary font-medium">comunidad de PHPost</span>.</p>
      <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">Actualmente, este fork es mantenido y evolucionado por <a href="https://github.com/joelmiguelvalente/PHPost" target="_blank" rel="external" class="text-primary font-medium" title="Perfil en Github">Miguel92</a>, con el objetivo de modernizar el motor y preservar su legado funcional.
    </p>
  </section>

  <!-- Derechos de autor -->
  <section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
      Derechos de autor
    </h2>

    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
      El diseño y esquema de <strong>PHPost</strong> están basados en la plataforma <span class="text-primary font-medium">Taringa!</span>.
      Dichos elementos fueron adaptados con fines exclusivamente
      <strong>educativos</strong> y sin intención de
      <strong>lucro</strong>.
    </p>

    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
      Todas las imágenes, diseños y logotipos pertenecen a sus respectivos creadores.
    </p>
  </section>
</div>
