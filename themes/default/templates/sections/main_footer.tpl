
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
         <a href="{$tsConfig.url}"><strong>{$tsConfig.titulo}</strong></a> &copy; {$smarty.now|date_format:"Y"} - Powered by <a href="https://github.com/joelmiguelvalente/PHPost/" target="_blank"><strong>PHPost</strong></a>
      </div>
   </div>
  
   {if $tsUser->is_admod && $tsConfig.c_see_mod && $tsModerar.total}
      <div id="stickymsg" onmouseover="$('#brandday').css('opacity',0.5);" onmouseout="$('#brandday').css('opacity',1);" onclick="location.href = '{$tsConfig.url}/moderacion/'" style="cursor:default;">Hay {$tsModerar.total} contenido{if $tsModerar.total != 1}s{/if} esperando revisi&oacute;n</div>
   {/if}

</body>
</html>