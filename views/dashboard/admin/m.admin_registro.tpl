<h1 class="text-xl font-semibold mb-4">Administrar Registro</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">

	{if $tsSave}<div style="display: block;" class="mensajes ok">Configuraciones guardadas</div>{/if}
	<form action="" method="post" autocomplete="off">
		<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
			<legend>Configuraci&oacute;n del Registro</legend>
			<dl>
				<dt>
					<label for="ai_edad">Edad requerida:</label>
					<br /><span>A partir de que edad los usuarios pueden registrarse.</span></dt>
				<dd>
					<input type="text" id="ai_edad" name="c_allow_edad" style="width:10%" maxlength="2" value="{$tsConfig.c_allow_edad}" /> a&ntilde;os.</dd>
			</dl>
			<dl>
				<dt>
					<label for="ai_met_welcome">Mensaje de Bienvenida:</label>
					<br /><span id="c_message_welcome" {if $tsConfig.c_met_welcome==0 }style="display:none;" {/if}> <br /> [usuario] => Nombre del registrado <br /> [welcome] => Bienvenido/a depende del sexo <br /> [web] => Nombre de esta web <br /> <br />(Se aceptan BBCodes y Smileys)</span></dt>
				<dd>
					<select id="ai_met_welcome" name="c_met_welcome" style="width: 266px;" class="select" {if $tsConfig.c_met_welcome==0 } onchange="if($('#ai_met_welcome').val() != 0) $('textarea[name=c_message_welcome]').slideDown(); $('#c_message_welcome').slideDown();" {/if}>
						<option value="0" {if $tsConfig.c_met_welcome==0 }selected{/if}>No dar bienvenida</option>
						<option value="1" {if $tsConfig.c_met_welcome==1 }selected{/if}>Muro</option>
						<option value="2" {if $tsConfig.c_met_welcome==2 }selected{/if}>Mensaje privado</option>
						<option value="3" {if $tsConfig.c_met_welcome==3 }selected{/if}>Aviso</option>
					</select>
					<br />
					<textarea name="c_message_welcome" id="ai_met_welcome" style="width: 260px; height: 100px; {if $tsConfig.c_met_welcome == 0} display:none; {/if}">{$tsConfig.c_message_welcome}</textarea>
				</dd>
			</dl>
			<dl>
				<dt>
					<label for="ai_reg_active">Registro abierto:</label>
					<br /><span>Permitir el registro de nuevos usuarios</span></dt>
				<dd>
					<label>
						<input name="c_reg_active" type="radio" id="ai_reg_active" value="1" {if $tsConfig.c_reg_active==1 }checked="checked" {/if} class="radio" />S&iacute;</label>
					<label>
						<input name="c_reg_active" type="radio" id="ai_reg_active" value="0" {if $tsConfig.c_reg_active !=1 }checked="checked" {/if} class="radio" />No</label>
				</dd>
			</dl>
			<dl>
				<dt>
					<label for="ai_reg_activate">Activar usuarios:</label>
					<br /><span>Activar autom&aacute;ticamente la cuenta de usuario.</span></dt>
				<dd>
					<label>
						<input name="c_reg_activate" type="radio" id="ai_reg_activate" value="1" {if $tsConfig.c_reg_activate==1 }checked="checked" {/if} class="radio" />S&iacute;</label>
					<label>
						<input name="c_reg_activate" type="radio" id="ai_reg_activate" value="0" {if $tsConfig.c_reg_activate !=1 }checked="checked" {/if} class="radio" />No</label>
				</dd>
			</dl>
										
			<dl>
				<dt>
					<label for="pkey">reCaptcha p&uacute;blica</label>
					<br /><span>Clave p&uacute;blica de <a href="https://www.google.com/recaptcha/admin">reCatpcha</a>.</span>
				</dt>
				<dd>
					<input type="text" id="pkey" name="pkey" value="{$tsConfig.pkey}" />
				</dd>
			</dl>
			<dl>
				<dt>
					<label for="skey">reCaptcha secreta</label>
					<br /><span>Clave privada de <a href="https://www.google.com/recaptcha/admin">reCatpcha</a>.</span>
				</dt>
				<dd>
					<input type="text" id="skey" name="skey" value="{$tsConfig.skey}" />
				</dd>
			</dl>
										<p><input type="submit" name="save" value="Guardar Cambios" class="btn_g"/></p>
									</fieldset>
									</form>
								</div>