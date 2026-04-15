<h1 class="text-xl font-semibold mb-4">Administrar Usuarios</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{if !$tsAct}
		{if !$tsMembers.data}
			{* ESTO ES INCOHERENTE, SIEMPRE HABRA UN USUARIO *}
			{include "dashboard/Alert.tpl" text="No hay usuarios registrados." color="orange" show=true}
		{else}
			<div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 rounded-xl">
			   	<div class="flex items-center gap-1">
			   		<span class="text-xs font-semibold text-gray-500 uppercase tracking-widest mr-2">Ordenar por</span>
			   		<a href="{$tsConfig.url}/admin/users?order=email&modo={$smarty.get.modo|default:'desc'}" class="px-3 py-1.5 text-sm rounded-lg transition-all duration-150 {if $smarty.get.order == 'email' || !$smarty.get.order}bg-indigo-600 text-white font-semibold shadow{else}text-gray-700 hover:text-white hover:bg-gray-700{/if}">Email</a>
			   		<a href="{$tsConfig.url}/admin/users?order=activity&modo={$smarty.get.modo|default:'desc'}" class="px-3 py-1.5 text-sm rounded-lg transition-all duration-150 {if $smarty.get.order == 'activity'}bg-indigo-600 text-white font-semibold shadow{else}text-gray-700 hover:text-white hover:bg-gray-700{/if}">Última actividad</a>
			   		<a href="{$tsConfig.url}/admin/users?order=ip&modo={$smarty.get.modo|default:'desc'}" class="px-3 py-1.5 text-sm rounded-lg transition-all duration-150 {if $smarty.get.order == 'ip'}bg-indigo-600 text-white font-semibold shadow{else}text-gray-700 hover:text-white hover:bg-gray-700{/if}">IP</a>
			   		<a href="{$tsConfig.url}/admin/users?order=status&modo={$smarty.get.modo|default:'desc'}" class="px-3 py-1.5 text-sm rounded-lg transition-all duration-150 {if $smarty.get.order == 'status'}bg-indigo-600 text-white font-semibold shadow{else}text-gray-700 hover:text-white hover:bg-gray-700{/if}">Estado</a>
			   		<a href="{$tsConfig.url}/admin/users" class="px-3 py-1.5 text-sm rounded-lg transition-all duration-150">Borrar</a>
			   	</div>
			   	<div class="flex items-center gap-1 border border-gray-700 rounded-lg overflow-hidden">
			      	<a href="{$tsConfig.url}/admin/users?order={$smarty.get.order|default:'email'}&modo=asc" class="flex items-center gap-1.5 px-3 py-1.5 text-sm transition-all duration-150 {if $smarty.get.modo == 'asc'}bg-gray-700 text-white font-semibold{else}text-gray-700 hover:text-white hover:bg-gray-700/50{/if}">A-Z</a>
			      	<a href="{$tsConfig.url}/admin/users?order={$smarty.get.order|default:'email'}&modo=desc" class="flex items-center gap-1.5 px-3 py-1.5 text-sm transition-all duration-150 {if $smarty.get.modo == 'desc' || !$smarty.get.modo}bg-gray-700 text-white font-semibold{else}text-gray-700 hover:text-white hover:bg-gray-700/50{/if}">Z-A</a>
			   </div>
			</div>
			<div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">
					{include "dashboard/table/Thead.tpl" fields=[
						"Rango",
						"Usuario",
						"Email",
						"&Uacute;ltima actividad ",
						"Registro",
						"IP",
						"Estado",
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsMembers.data item=m}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
								<td class="px-3 py-2 text-gray-600 dark:text-gray-400"><img src="{$tsRoutes.assets.images}/icons/ranks/{$m.r_image}" alt="{$m.r_name}" /></td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/perfil/{$m.user_name}" style="color:#{$m.r_color};">{$m.user_name}</a></td>
								<td class="px-3 py-2">{$m.user_email}</td>
								<td class="px-3 py-2">{if $m.user_lastactive == 0} Nunca{else}{$m.user_lastactive|hace:true}{/if}</td>
								<td class="px-3 py-2">{$m.user_registro|date_format:"d/m/Y"}</td>
								<td class="px-3 py-2"><a href="{$tsConfig.url}/moderacion/buscador/1/1/{$m.user_last_ip}" class="geoip" target="_blank">{$m.user_last_ip}</a></td>
								<td class="px-3 py-2" id="status_user_{$m.user_id}">{if $m.user_baneado == 1}<font color="red">Suspendido</font>{elseif $m.user_activo == 0}<font color="purple">Inactivo</font>{else}<font color="green">Activo</font>{/if}</td>
								<td class="px-3 py-2 admin_actions">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="users?act=show&uid={$m.user_id}" title="Editar Usuario" icon="edit"}
										{include "dashboard/table/Action.tpl" action="admin.users.setInActive({$m.user_id})" type="button" title="Activar/Desactivar Usuario" icon="sync"}

										{include "dashboard/table/Action.tpl" action="moderacion.users.action({$m.user_id}, 'aviso', false)" type="button" title="Enviar Alerta" icon="warning"}

										{include "dashboard/table/Action.tpl" action="mod.{if $m.user_baneado == 1}reboot({$m.user_id}, 'users', 'unban', false){else}users.action({$m.user_id}, 'ban', false){/if}" type="button" title="{if $m.user_baneado == 1}Reactivar{else}Suspender{/if} Usuario" icon="account_circle{if $m.user_baneado != 1}_off{/if}"}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					{include "dashboard/table/Tfoot.tpl" span=8 pages=$tsMembers.pages}
				</table>
			</div>
		{/if}
	{elseif $tsAct == 'show'}
		<div class="flex justify-between items-center mb-4 border-b-2 pb-4">
			<h1>Administrar: <strong class="block text-xl">{$tsUsername}</strong></h1>
			<div>
				<strong>Seleccionar:</strong>
				{include "dashboard/Select.tpl" id="type" name="type" options=[
				   ['value'=> 1, 'label'=> 'Vista general'],
				   ['value'=> 5, 'label'=> 'Preferencias'],
				   ['value'=> 6, 'label'=> 'Borrar Contenido'],
				   ['value'=> 7, 'label'=> 'Rango'],
				   ['value'=> 8, 'label'=> 'Firma'],
				] onchange="location.href='{$tsConfig.url}/admin/users?act=show&uid={$tsUserID}&type=' + this.value;" }
			</div>
		</div>
		{if $tsSave}<div class="mensajes ok">Tus cambios han sido guardados.</div>{/if}
		{if $tsError}<div class="mensajes error">{$tsError}</div>{/if}
		<form method="POST" autocomplete="off" class="space-y-6">
			<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
				{if !$tsType || $tsType == 1}
					{include "dashboard/Legend.tpl" text="Vista general"}

					{include "dashboard/FormGroup.tpl" id="user" label="Nombre de Usuario" name="user_name" helper="El nick s&oacute;lo se cambiar&aacute; si escribe una nueva contrase&ntilde;a" value=$tsUserD.user_name}

					{include "dashboard/FormGroupText.tpl" id="rank" label="Rango" text=$tsUserD.r_name style="color:#{$tsUserD.r_color}"}

					{include "dashboard/FormGroupText.tpl" id="registro" label="Registrado" text=$tsUserD.user_registro|fecha:"full_datetime"}

					{include "dashboard/FormGroupText.tpl" id="activity" label="&Uacute;ltima vez activo" text=$tsUserD.user_lastactive|hace}

					{include "dashboard/FormGroup.tpl" id="points" label="Puntos" name="user_puntos" value=$tsUserD.user_puntos}

					{include "dashboard/FormGroup.tpl" id="pointsxdar" label="Puntos para dar" name="user_puntosxdar" value=$tsUserD.user_puntosxdar}

					{include "dashboard/FormGroup.tpl" id="changenicks" label="Cambios de nick disponibles" name="user_name_changes" value=$tsUserD.user_name_changes}

					{include "dashboard/FormGroup.tpl" type="email" id="email" label="E-mail" name="user_email" value=$tsUserD.user_email}

					{include "dashboard/FormGroup.tpl" type="password" id="password" label="Nueva contrase&ntilde;a" name="user_password" helper="Debe tener entre 5 y 35 caracteres."}

					{include "dashboard/FormGroup.tpl" type="password" id="password2" label="Confirmar contrase&ntilde;a" name="user_confirm" helper="Necesita confirmar su contrase&ntilde;a s&oacute;lo si la ha cambiado arriba."}

					{include "dashboard/Check.tpl" type="checkbox" label="Informar al usuario" helper="Marque esta casilla si quiere enviar un e-mail al usuario con los nuevos datos" name="sendata" class="mb-3"}
				{elseif $tsType == 5}
					{include "dashboard/Legend.tpl" text="Modificar privacidad del usuario"}
					<h2 class="active">&iquest;Qui&eacute;n puede...</h2>
					<div class="field">
						<dl>
							<dt><label>ver su muro?</label></dt>
							<dd>
								<select name="muro">
								{foreach $tsPrivacidad key=val item=label}
									<option value="{$val}"{if $tsPerfil.p_privacidad == $val} selected{/if}>{$label}</option>
								{/foreach}
								</select>
							</dd>
						</dl>                    				
					</div>
					{*$tsPerfil.p_configs.muro*}
					<div class="field">
						<dl>
							<dt><label>firmar su muro?</label></dt>
							<dd>
								<select name="muro_firm">
									{foreach from=$tsPrivacidad key=val item=label}
										{if $val != 'everyone'}
											<option value="{$val}"{if $tsPerfil.p_publicar_muro == $val} selected{/if}>{$label}</option>
										{/if}
									{/foreach}
								</select>
							</dd>
						</dl>
					</div>
					<div class="field">
						<dl>
							<dt><label>ver visitantes recientes?</label></dt>
							<dd>
								<select name="last_hits">
									{foreach from=$tsPrivacidad key=val item=label}
										{if $val != 'only_friends' && $val != 'friends'}
							      			<option value="{$val}"{if $tsPerfil.p_muro_visitas == $val} selected{/if}>{$label}</option>
							      		{/if}
									{/foreach}
								</select>
							</dd>
						</dl>
					</div>
					<div class="field">
						<dl>
							<dt><label>enviarles mensajes privados?</label><br /><span>Esta opci&oacute;n no se aplica a moderadores y administradores.</span></dt>
							<dd>
								<select name="rec_mps">
									{foreach from=$tsPrivacidad key=val item=label}
										{if $val != 'everyone'}
											<option value="{$val}"{if $tsPerfil.p_mensajes_privados == $val} selected{/if}>{$label}</option>
										{/if}
									{/foreach}
									<option value="nobody"{if $tsPerfil.p_mensajes_privados == 'nobody'} selected{/if}>Deshabilitar mensajer&iacute;a (opci&oacute;n administrativa)</option>
								</select>
							</dd>
						</dl>
					</div>
            	{elseif $tsType == 6}
            		{include "dashboard/Legend.tpl" text="Eliminaci&oacute;n de contenidos"}
					<input type="checkbox" id="bocuenta" name="bocuenta" onclick="$('#ext').slideToggle();"/><label style="font-weight:bold;" for="bocuenta">Cuenta Completa</label><label for="bocuenta"> &nbsp; Se eliminar&aacute; la cuenta y todo el contenido relacionado a {$tsUsername}.</label>
					<div id="ext">
						{* Configurado en inc/extras/datos.php *}
						{foreach $tsContenido item=del}
							<hr/>
							<input type="checkbox" id="bo{$del.for}" name="bo{$del.for}"/><label style="font-weight:bold;" for="bo{$del.for}">{$del.title}</label><label for="bo{$del.for}"> &nbsp; {$del.desc}</label>
						{/foreach}
					</div>
	               <br /><hr/>
	               Introduzca su contrase&ntilde;a para continuar: <input type="password" name="password"/>
           		{elseif $tsType == 7}
            		{include "dashboard/Legend.tpl" text="Modificar rango de usuario"}
					{include "dashboard/FormGroupText.tpl" id="rango_actual" label="Rango actual" text=$tsUserR.user.r_name style="color:#{$tsUserR.user.r_color}"}

					<div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
				    	<div>
				    	  	<label for="user" class="font-medium text-gray-700 dark:text-gray-300">Nuevo rango</label>
				    	</div>
				    	<div class="md:col-span-2 space-y-3">
							<select name="new_rango" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
								{foreach from=$tsUserR.rangos item=r}
									<option value="{$r.rango_id}" style="color:#{$r.r_color}"{if $r.rango_id == $tsUserR.user.rango_id} selected{/if}>{$r.r_name}</option>
								{/foreach}
							</select>
				    	</div>
				   </div>
				{elseif $tsType == 8}
					{include "dashboard/FormGroupTextarea.tpl" id="firma" label="Modificar firma de usuario" name="user_firma" value=$tsUserF.user_firma helper="Se aceptan bbcodes [b], [u], [i]"}
				{else}
					<div class="phpostAlfa">Pendiente</div>
				{/if}
				{include "dashboard/Button.tpl" submit_text="Guardar Cambios"}
			</fieldset>
		</form>
	{/if}
</div>
