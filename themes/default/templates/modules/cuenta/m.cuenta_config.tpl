<div class="content-tabs privacidad">
	<fieldset>
		<h2 class="active">&iquest;Qui&eacute;n puede...</h2>
		<div class="field">
			<label>ver tu muro?</label>
			<select name="privacidad" class="cuenta-save-7">
				{foreach $tsPrivacidad key=val item=label}
					<option value="{$val}"{if $tsPerfil.p_privacidad == $val} selected{/if}>{$label}</option>
				{/foreach}
			</select>
		</div>
		{*$tsPerfil.p_configs.muro*}
		<div class="field">
      	<label>firmar tu muro?</label>
      	<select name="publicar_muro" class="cuenta-save-7">
      		{foreach from=$tsPrivacidad key=val item=label}
	      		{if $val != 'everyone'}
	      			<option value="{$val}"{if $tsPerfil.p_publicar_muro == $val} selected{/if}>{$label}</option>
	      		{/if}
      		{/foreach}
      	</select>
      </div>
      <div class="field">
      	<label>ver &uacute;ltimas visitas?</label>
      	<select name="muro_visitas" class="cuenta-save-7">
      		{foreach from=$tsPrivacidad key=val item=label}
	      		{if $val != 'only_friends' && $val != 'friends'}
	      			<option value="{$val}"{if $tsPerfil.p_muro_visitas == $val} selected{/if}>{$label}</option>
	      		{/if}
      		{/foreach}
      	</select>
      </div>
      {if !$tsUser->is_admod}
      	<div class="field">
      		<label>enviarte MPs?</label>
      		<select name="mensajes_privados" class="cuenta-save-7">
      			{foreach from=$tsPrivacidad key=val item=label}
      				{if $val != 'everyone'}
      					<option value="{$val}"{if $tsPerfil.p_mensajes_privados == $val} selected{/if}>{$label}</option>
      				{/if}
      			{/foreach}
      		</select>
      	</div>
      {/if}
   </fieldset>
	{if !$tsUser->is_admod}
		<a onclick="$('#primi').slideUp(); $('#passi').slideDown(); $('#informa').slideDown(); $('#btninforma').slideDown();" id="primi">Desactivar Cuenta</a>
		<p style="display:none;" id="informa"> Si desactiva su cuenta, todo el contenido relacionado a usted dejar&aacute; de ser visible durante un tiempo. <br>Pasado ese tiempo, la administraci&oacute;n borrar&aacute; todo su contenido y no podr&aacute; recuperarlo.</p>
		<a onclick="desactivate()" style="display:none;" id="btninforma"><input type="button" value="Lo s&eacute;, pero quiero desactivarla" style="position:right;" class="mBtn btnDelete"></a>
	{/if}
	<div class="buttons">
      <input type="button" value="Guardar" onclick="cuenta.guardar_datos()" class="mBtn btnOk">
   </div>
   <div class="clearfix"></div>
</div>