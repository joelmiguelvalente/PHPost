
            <!--end-cuerpo-->
            </section>
            <footer id="pie">
               <a href="{$tsConfig.url}/pages/ayuda/">Ayuda</a> -
               <a href="{$tsConfig.url}/pages/chat/">Chat</a> -
               <a href="{$tsConfig.url}/pages/contacto/">Contacto</a> -  
               <a href="{$tsConfig.url}/pages/protocolo/">Protocolo</a>
               <br/>
               <a href="{$tsConfig.url}/pages/terminos-y-condiciones/">T&eacute;rminos y condiciones</a> - 
               <a href="{$tsConfig.url}/pages/privacidad/">Privacidad de datos</a> -
               <a href="{$tsConfig.url}/pages/dmca/">Report Abuse - DMCA</a>
            </footer>
         </div>
         <!--END CONTAINER-->
      </main>
      {* 
      El siguiente contenedor sirve para validar el Copyright,
      El ID del div NO debe ser alterado de lo contrario nuestro validador 
      tomará al sitio como una web sin copyright 
      *}
      <div id="pp_copyright">
         <a href="{$tsConfig.url}"><strong>{$tsConfig.titulo}</strong></a> &copy; 2025- - Powered by <a href="{Config::app('app.contact.repository')}" target="_blank"><strong>PHPost</strong></a>
         <small style="display: block;font-family: monospace;">Script v{Config::app('app.version')} - Modo: {Config::app('app.development') ? 'development' : 'production'}</small>
      </div>
   </div>
  
   {if $tsUser->is_admod && $tsConfig.c_see_mod && $tsModerar.total}
      <a id="stickymsg" href="{$tsConfig.url}/moderacion/" title="Hay {$tsModerar.total} contenido{if $tsModerar.total != 1}s{/if} esperando" rel="internal">Hay {$tsModerar.total} contenido{if $tsModerar.total != 1}s{/if} esperando revisi&oacute;n</a>
   {/if}

</body>
</html>
