<div class="content-tabs perfil">
   <fieldset>
      <div class="field">
         <label for="nombre">Nombre completo</label>
         <input type="text" placeholder="Jhon Doe" value="{$tsPerfil.p_nombre}" maxlength="60" name="nombre" id="nombre" class="form-control">
      </div>
      <div class="field">
         <label for="sitio">Mensaje Personal</label>
         <textarea placeholder="Mensaje personal para el perfil" maxlength="60" name="mensaje" id="mensaje" class="form-control">{$tsPerfil.p_mensaje}</textarea>
      </div>
      <div class="field">
         <label for="sitio">Sitio Web</label>
         <input type="text" value="{$tsPerfil.p_sitio}" placeholder="{$tsConfig.url}" maxlength="60" name="sitio" id="sitio" class="form-control">
      </div>
      <div class="field">
         <label for="red">Redes sociales</label>
         <div class="grid grid-cols-2 gap-3">
            {foreach $tsPerfil.redes key=name item=red}
               <div class="flex justify-start items-center gap-2">
                  <div class="icon">
                  	<img src="{$tsRoutes['assets:images']}/redes/{$name}.svg" width="24" height="24" />
                  </div>
                  <input type="text" class="form-control" value="{$tsPerfil.p_socials.$name}" placeholder="{$red}" name="red[{$name}]">
               </div>
            {/foreach}
         </div>
      </div>
     
		<div class="buttons">
		   <input type="button" value="Guardar" onclick="cuenta.guardar_datos()" class="mBtn btnOk">
		</div>
   </fieldset>
</div>
