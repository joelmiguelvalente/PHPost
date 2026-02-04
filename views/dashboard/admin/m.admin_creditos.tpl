<h1 class="text-xl font-semibold mb-4">Soporte y Cr&eacute;ditos</h1>
<div class="space-y-6">
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

   <section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
      <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-4">Información de extensiones</h2>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
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
      La primera versión fue desarrollada por
      <a href="mailto:jneutron@phpost.net" class="text-primary hover:underline font-medium">
        JNeutron
      </a>.
      Las versiones posteriores se mantienen por
      <a href="mailto:isidro@phpost.net" class="text-primary hover:underline font-medium">
        Isidro
      </a>
      con la participación de los usuarios de la
      <a href="http://www.phpost.net" class="text-primary hover:underline font-medium">
        comunidad
      </a>.
    </p>
  </section>

  <!-- Derechos de autor -->
  <section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
      Derechos de autor
    </h2>

    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
      El diseño y esquema de <strong>PHPost</strong> están basados en la plataforma
      <a href="http://www.taringa.net" target="_blank" rel="noopener noreferrer"
         class="text-primary hover:underline font-medium">
        Taringa!
      </a>.
      Dichos elementos fueron adaptados con fines exclusivamente
      <strong>educativos</strong> y sin intención de
      <strong>lucro</strong>.
    </p>

    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
      Todas las imágenes, diseños y logotipos pertenecen a sus respectivos creadores.
    </p>
  </section>
</div>