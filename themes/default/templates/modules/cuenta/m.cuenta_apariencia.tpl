<div class="content-tabs perfil">
   <fieldset>
      <div class="field">
         <label for="theme">Seleccione un theme!:</label>
         <select onchange="apariencia.theme()" name="theme" id="theme">
            {foreach $tsThemes key=t item=tema}
               <option value="{$tema.t_path}"{if $tsThemeCurrent == $tema.t_path} selected{/if}>{$tema.t_name}</option>
            {/foreach}
         </select>
      </div>
   </fieldset>
</div>