<div class="content-tabs cuenta">
   <fieldset>
      <div class="field">
         <label for="email">E-Mail:</label>
         <input type="text"value="{$tsUser->info.user_email}" maxlength="35" name="email" id="email" class="text cuenta-save-1">
      </div>
      <div class="field">
         <label for="pais">Pa&iacute;s:</label>
         <select onchange="cuenta.chgpais()" class="cuenta-save-1" name="pais" id="pais">
            <option value="">Pa&iacute;s</option>
            {foreach from=$tsPaises key=code item=pais}
               <option value="{$code}"{if $code == $tsPerfil.user_pais} selected{/if}>{$pais}</option>
            {/foreach}
         </select>
      </div>
      <div class="field">
         <label for="estado">Estado/Provincia:</label>
         <select name="estado" id="estado" class="cuenta-save-1">
            {foreach from=$tsEstados key=code item=estado}
               <option value="{$code+1}"{if $code+1 == $tsPerfil.user_estado} selected{/if}>{$estado}</option>
            {/foreach}
         </select>
      </div>
      <div class="field">
         <label>Sexo</label>
         <ul class="fields">
            <li>
               <label><input type="radio" value="none" name="sexo" class="radio cuenta-save-1"{if $tsPerfil.user_sexo == 'none'} checked{/if}/>Ninguno</label>
            </li>
            <li>
               <label><input type="radio" value="male" name="sexo" class="radio cuenta-save-1"{if $tsPerfil.user_sexo == 'male'} checked{/if}/>Masculino</label>
            </li>
            <li>
               <label><input type="radio" value="female" name="sexo" class="radio cuenta-save-1"{if $tsPerfil.user_sexo == 'female'} checked{/if}/>Femenino</label>
            </li>
         </ul>
      </div>
      <div class="field">
   		<label>Nacimiento:</label>
   		<input type="date" name="nacimiento" class="cuenta-save-1" min="{$birthMin}" max="{$birthMax}" value="{if $tsPerfil.user_ano}{$tsPerfil.user_ano|string_format:'%04d'}-{$tsPerfil.user_mes|string_format:'%02d'}-{$tsPerfil.user_dia|string_format:'%02d'}{else}{$smarty.now|date_format:'Y-m-d'}{/if}">
   	</div>
      {if $tsConfig.c_allow_firma}
         <div class="field">
            <label for="firma">Firma:<br /> <small style="font-weight:normal">(Acepta BBCode) Max. 300 car.</small></label>
            <textarea name="firma" id="firma" class="cuenta-save-1">{$tsPerfil.user_firma}</textarea>
         </div>
      {/if}
   </fieldset>
   <div class="buttons">
      <input type="button" value="Guardar" onclick="cuenta.guardar_datos()" class="mBtn btnOk">
   </div>
   <div class="clearfix"></div>
</div>