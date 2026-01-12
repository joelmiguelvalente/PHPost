<div id="menu">
   <!--LEFT MENU-->
	<ul class="menuTabs">
      {if $tsConfig.c_allow_portal && $tsUser->is_member == true}
         <li class="tabbed{if $tsPage != 'home' && $tsPage != 'posts' && $tsPage != 'tops' && $tsPage != 'admin' && $tsPage != 'fotos'} here{/if}" id="tabbedhome">
            <a title="Ir a Inicio" href="{$tsConfig.url}/mi/"><span>&nbsp;</span></a>
         </li>
      {/if}
      <li class="tabbed{if $tsPage == 'posts' || $tsPage == 'home'} here{/if}">
         <a title="Ir a Posts" href="{$tsConfig.url}/posts/">Posts</a>
      </li>				
		{if $tsConfig.c_fotos_private == '1' && !$tsUser->is_member}{else}								
         <li class="tabbed{if $tsPage == 'fotos'} here{/if}">
            <a title="Ir a Fotos" href="{$tsConfig.url}/fotos/">Fotos</a>
         </li>								
		{/if}
      <li class="tabbed{if $tsPage == 'tops'} here{/if}">
         <a title="Ir a TOPs" href="{$tsConfig.url}/top/">TOPs</a>
      </li>
      {if $tsUser->is_member}
         {if $tsUser->is_admod == 1}
            <li class="tabbed{if $tsPage == 'admin'} here{/if}">
               <a title="Panel de Administrador" href="{$tsConfig.url}/admin/">Administraci&oacute;n</a>
            </li>
         {/if}
      {else}
         <li class="tabbed registrate">
            <a title="Registrate!" href="{$tsConfig.url}/registro/">Registrate!</a>
         </li>
      {/if}
      <li class="clearBoth"></li>
   </ul>
   <!--RIGHT MENU-->
   <div class="opciones_usuario {if !$tsUser->is_member}anonimo{/if}">
      {if $tsUser->is_member}
         <div class="userInfoLogin">
            <ul>
               <li class="monitor" style="position: relative">
                  <a href="{$tsConfig.url}/monitor/" onclick="notifica.last(); return false" title="Monitor de usuario" name="Monitor"><span class="systemicons monitor"></span></a>
                  <div class="notificaciones-list" id="mon_list">
                     <div style="padding: 10px 10px 0 10px;font-size:13px">
                        <strong style="cursor:pointer" onclick="location.href='{$tsConfig.url}/monitor/'">Notificaciones</strong>
                     </div>
                     <ul></ul>
                     <a href="{$tsConfig.url}/monitor/" class="ver-mas">Ver m&aacute;s notificaciones</a>
                  </div>
               </li>
               <li class="mensajes" style="position:relative">
                  <a href="{$tsConfig.url}/mensajes/" onclick="mensaje.last(); return false" title="Mensajes Personales" name="Mensajes"><span class="systemicons mps"></span></a>
                  <div class="notificaciones-list" id="mp_list" style="width:270px">
                     <div style="padding: 10px 10px 0 10px;font-size:13px">
                        <strong style="cursor:pointer" onclick="location.href='{$tsConfig.url}/mensajes/'">Mensajes</strong>
                     </div>
                     <ul id="boxMail"></ul>
                     <a href="{$tsConfig.url}/mensajes/" class="ver-mas">Ver todos los mensajes</a>
                  </div>
               </li>
               {if $tsAvisos}
                  <li style="position:relative;">
                     <a title="Avisos" href="{$tsConfig.url}/mensajes/avisos/"><img src="{$tsRoutes.tema.images}/icons/megaphone.png" /></a>
                     <div id="alerta_avs" class="alertas" style="top: -6px;"><a title="{$tsAvisos} aviso{if $tsAvisos != 1}s{/if}"><span>{$tsAvisos}</span></a></div>
                  </li>
               {/if}
               <li>
                  <a title="Mis Favoritos" href="{$tsConfig.url}/favoritos.php"><span class="systemicons favoritos"></span></a>
               </li>
               <li>
                  <a title="Mis Borradores" href="{$tsConfig.url}/borradores.php"><span class="systemicons borradores"></span></a>
               </li>
               <li>
                  <a title="Mi cuenta" href="{$tsConfig.url}/cuenta/">
                      <span class="systemicons micuenta"></span>
                  </a>
               </li>
               <li class="usernameMenu">
                  <a title="Mi Perfil" href="{$tsConfig.url}/perfil/{$tsUser->info.user_name}" class="username">{$tsUser->nick}</a>
               </li>
               <li class="logout">
                  <a href="{$tsConfig.url}/login-salir.php" style="vertical-align: middle" title="Salir"><span class="systemicons logout"></span></a>
               </li>
            </ul>
            <div style="clear:both"></div>
         </div>
      {else}
         <div class="identificarme">
			   <a title="Identificarme" href="{$tsRoutes.url}/login/" class="iniciar_sesion">Identificarme</a>
         </div>
      {/if}
	</div>
   <div class="clearBoth"></div>
</div>