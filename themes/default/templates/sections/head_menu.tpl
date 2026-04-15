<nav class="navbar flex justify-between items-center">
   <!--LEFT MENU-->
   <div class="navbar-left flex justify-start items-center">
      {if $tsConfig.c_allow_portal && $tsUser->is_member}
         <div class="nav-item{if $tsPage == '' || $tsPage == 'portal'} active{/if}" id="tabbedhome">
            <a title="Ir a Inicio" href="{$tsConfig.url}/mi/"><span></span></a>
         </div>
      {/if}
      <div class="nav-item{if $tsPage == 'posts' || $tsPage == 'home'} active{/if}">
         <a title="Ir a Posts" href="{$tsConfig.url}/posts/">Posts</a>
      </div>
      {if $tsConfig.c_fotos_private == 0 && $tsUser->is_member}                        
         <div class="nav-item{if $tsPage == 'fotos'} active{/if}">
            <a title="Ir a Fotos" href="{$tsConfig.url}/fotos/">Fotos</a>
         </div>
      {/if}
      <div class="nav-item{if $tsPage == 'tops'} active{/if}">
         <a title="Ir a TOPs" href="{$tsConfig.url}/top/">TOPs</a>
      </div>
      {if $tsUser->is_admod == 1}
         <div class="nav-item{if $tsPage == 'admin'} active{/if}">
            <a title="Panel de Administrador" href="{$tsConfig.url}/admin/">Administraci&oacute;n</a>
         </div>
      {/if}
      {if !$tsUser->is_member}
         <div class="nav-item registrate">
            <a title="Registrate!" href="{$tsConfig.url}/registro/{if $tsPage != '' && $tsPage != 'home'}?redirect={$tsRoutes.redirectTo}{/if}">Registrate!</a>
         </div>
      {/if}
   </div>
   <!--RIGHT MENU-->
   <div class="navbar-right flex justify-end items-center{if !$tsUser->is_member} anonimo{/if}">
      {if $tsUser->is_member}
         <div class="navbar-user flex justify-end items-center">
            <div class="monitor relative">
               <a href="{$tsConfig.url}/monitor/" onclick="notifica.last(); return false" title="Monitor de usuario" name="Monitor"><span class="systemicons monitor"></span></a>
               <div class="dropdown-nots" id="mon_list">
                  <a class="dropdown-header" href="{$tsConfig.url}/monitor/" target="internal">Notificaciones</a>
                  <div class="dropdown-content" dropdown-open="Monitor">
                     <div class="dropdown-empty">Cargando...</div>
                  </div>
                  <a href="{$tsConfig.url}/monitor/" class="dropdown-footer">Ver m&aacute;s notificaciones</a>
               </div>
            </div>
            <div class="mensajes relative">
               <a href="{$tsConfig.url}/mensajes/" onclick="mensaje.last(); return false" title="Mensajes Personales" name="Mensajes"><span class="systemicons mps"></span></a>
               <div class="dropdown-nots" id="mp_list">
                  <a class="dropdown-header" href="{$tsConfig.url}/mensajes/" target="internal">Mensajes</a>
                  <div class="dropdown-content" dropdown-open="Mensajes">
                     <div class="dropdown-empty">Cargando...</div>
                  </div>
                  <a href="{$tsConfig.url}/mensajes/" class="dropdown-footer">Ver todos los mensajes</a>
               </div>
            </div>
            {if $tsAvisos}
               <div class="relative">
                  <a title="Avisos" href="{$tsConfig.url}/mensajes/avisos/"><img src="{$tsRoutes.assets.images}/icons/megaphone.png" /></a>
                  <div id="alerta_avs" class="alertas"><a title="{$tsAvisos} aviso{if $tsAvisos != 1}s{/if}"><span>{$tsAvisos}</span></a></div>
               </div>
            {/if}
            <div class="usernameMenu relative">
               <a href="{$tsConfig.url}/@{$tsUser->info.user_name}" onclick="usuario.last(); return false" title="Mi perfil" name="Usuario" class="username">{$tsUser->nick}</a>
               <div class="dropdown-nots" id="user_list">
                  <div class="dropdown-content" dropdown-open="Usuario">
                     <a title="Mi cuenta" href="{$tsConfig.url}/@{$tsUser->info.user_name}" class="dropdown-item">
                        <div class="icon"></div>
                        <div class="info">Mi perfil</div>
                     </a>
                     <a title="Mi cuenta" href="{$tsConfig.url}/cuenta/" class="dropdown-item">
                        <div class="icon"><span class="systemicons micuenta"></span></div>
                        <div class="info">Mi cuenta</div>
                     </a>
                     <a title="Mis Favoritos" href="{$tsConfig.url}/favoritos" class="dropdown-item">
                        <div class="icon"><span class="systemicons favoritos"></span></div>
                        <div class="info">Mis Favoritos</div>
                     </a>
                     <a title="Mis Borradores" href="{$tsConfig.url}/borradores" class="dropdown-item">
                        <div class="icon"><span class="systemicons borradores"></span></div>
                        <div class="info">Mis Borradores</div>
                     </a>
                     <hr>
                     <a title="Salir" href="{$tsConfig.url}/login-salir" class="dropdown-item">
                        <div class="icon"><span class="systemicons logout"></span></div>
                        <div class="info">Salir</div>
                     </a>
                  </div>
               </div>
            </div>
         </div>
      {else}
         <div class="nav-item identificarme">
            <a title="Identificarme" href="{$tsRoutes.url}/login/{if $tsPage != '' && $tsPage != 'home'}?redirect={$tsRoutes.redirectTo}{/if}" class="iniciar_sesion">Identificarme</a>
         </div>
      {/if}
   </div>
</nav>
