<div class="mpRContent">
	<div class="mpHeader">
		<h2 class="m-0 p-2">{$tsMensajes.msg.mp_subject}</h2>
	</div>
	<div class="mpUser py-2">
		<span class="info">Entre <a href="{$tsConfig.url}/@{$tsUser->nick}">T&uacute;</a> y <a href="{$tsConfig.url}/@{$tsMensajes.ext.user}">{$tsMensajes.ext.user}</a></span>
	</div>
	<div class="mpHistory" id="historial">
		{if $tsMensajes.res}
			{foreach from=$tsMensajes.res item=mp}
				<div class="bubble-container px-3 py-2 flex justify-start items-start gap-3{if $mp.user_id === $tsUser->uid} is-author{/if}">
					<a href="{$tsConfig.url}/@{$mp.user_name}" class="autor-image block overflow-hidden rounded-full">
						{include "blocks/Avatar.tpl" alt=$mp.user_name id=$mp.user_id size=48 class="ratio ratio-1x1"}
					</a>
					<div class="mensaje py-2 px-3 rounded-lg">
						<div class="mensaje-autor">
							<a href="{$tsConfig.url}/@{$mp.user_name}" class="autor-name">{$mp.user_name}</a>
						</div>
						<div class="mensaje-cuerpo my-1">{$mp.mr_body|nl2br}</div>
						<div class="mensaje-fecha">
							{if $tsUser->is_admod && $mp.mr_ip != ''}
								<a href="{$tsConfig.url}/moderacion/buscador/1/1/{$mp.mr_ip}">{$mp.mr_ip}</a> | 
							{/if}
							{$mp.mr_date|hace:true}
						</div>
					</div>
				</div>
			{/foreach}
		{else}
			<div class="alert-empty">No se pudieron cargar los mensajes.</div>
		{/if}
	</div>
	{if $tsUser->is_admod || ($tsMensajes.msg.mp_del_to == 0 && $tsMensajes.msg.mp_del_from == 0 && $tsMensajes.ext.can_read == 1)}
		<div class="mpForm p-3">
			<textarea id="respuesta" rows="3" placeholder="Escribe una respuesta..." class="form-control"></textarea>
			<input type="hidden" id="mp_id" value="{$tsMensajes.msg.mp_id}" />
			<span role="button" class="btn btn-primary btn-sm block resp" onclick="mensaje.responder()">Responder</span>
		</div>
	{else}
		<li class="alert-empty">Un participante abandon&oacute; la conversaci&oacute;n o no tienes permiso para responder</li>
	{/if}
</div>
<div class="mpOptions">
	<div class="info">
		<h2 class="m-0 p-2">Acciones</h2>
	</div>
	<div class="cat-list">
		<div class="cat-list_item"><span role="button" onclick="mensaje.marcar('{$tsMensajes.msg.mp_id}','{$tsMensajes.msg.mp_type}', 1, 2, this)">Marcar como no le&iacute;do</span></div>

		<div class="cat-list_item"><span role="button" onclick="mensaje.eliminar('{$tsMensajes.msg.mp_id}:{$tsMensajes.msg.mp_type}',2)">Eliminar</a></span></div>
		<div class="cat-list_item"><span role="button" onclick="denuncia.nueva('mensaje',{$tsMensajes.msg.mp_id})">Marcar como correo no deseado...</a></span></div>

		<div class="cat-list_item"><span role="" onclick="denuncia.nueva('usuario',{if $tsMensajes.msg.mp_from != $tsUser->uid}{$tsMensajes.msg.mp_from}{else}{$tsMensajes.msg.mp_to}{/if}, '', '{if $tsMensajes.msg.mp_from != $tsUser->uid}{$tsMensajes.msg.user_name}{else}{$tsUser->getUsername($tsMensajes.msg.mp_to)}{/if}'); return false">Denunciar a este usuario...</span></div>
		<div class="cat-list_item"><span role="button" href="javascript:bloquear({$tsMensajes.ext.uid}, {if $tsMensajes.ext.block}false{else}true{/if}, 'mensajes')" id="bloquear_cambiar">{if $tsMensajes.ext.block}Desbloquear{else}Bloquear{/if} a <u>{$tsMensajes.ext.user}</u>...</span></div>

		<div class="cat-list_item"><a href="{$tsConfig.url}/mensajes/" title="Volver a mensajes">&laquo; Volver a mensajes</a></div>
	</div>
</div>