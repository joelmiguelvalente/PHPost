{if $tsMensajes}
	<div id="mpList">
		{foreach from=$tsMensajes item=av}
			<div id="mp_{$av.av_id}" class="mpList-item mb-3 flex justify-start items-center gap-3{if $av.av_read == 0} unread{/if}">
				<a href="{$tsConfig.url}/mensajes/avisos/?aid={$av.av_id}" title="{$av.av_subject}" rel="internal" class="grid items-center gap-3 flex-grow-1">
					<img src="{$tsRoutes.assets.images}/mensajes/aviso_{$av.av_type}.svg" style="width:70px;height:70px;" />
					<div class="mpList-item_content">
						<div class="autor"><strong>{$tsConfig.titulo}</strong></div>
						<div class="subject">{$av.av_subject}</div>
						<div class="preview">{$av.av_body|truncate:70}... | {$av.av_date|hace}</div>
					</div>
				</a>
				<div class="actions flex justify-center items-center flex-col gap-3">
					<a href="{$tsConfig.url}/mensajes/avisos/?did={$av.av_id}"><img src="{$tsRoutes.assets.images}/mensajes/delete.svg" width="16" height="16" alt="Eliminar" /></a>
				</div>
			</div>
		{/foreach}
	</div>
{elseif $tsMensaje.av_id > 0}
	<div class="grid gap-3 grid-leer">
		<div class="mpRContent">
			<div class="mpHeader">
				<h2 class="m-0 p-2">{$tsMensaje.av_subject}</h2>
			</div>
			<div class="mpUser py-2">
				<span class="info">
					<a href="{$tsConfig.url}">{$tsConfig.titulo}</a> 
					<span class="block">{$tsMensaje.av_date|hace}</span>
				</span>
			</div>
			<div class="mpHistory" id="historial">
				<div class="bubble-container px-3 py-2 flex justify-start items-start gap-3">
					<div class="autor-image block overflow-hidden">
						<img src="{$tsRoutes.assets.images}/mensajes/aviso_{$tsMensaje.av_type}.svg" style="width:50px;height:50px;" />
					</div>
					<div class="mensaje py-2 px-3 rounded-lg">
						<div class="mensaje-autor">
							<a href="{$tsConfig.url}/@{$mp.user_name}" class="autor-name">{$mp.user_name}</a>
						</div>
						<div class="mensaje-cuerpo my-1">{$tsMensaje.av_body|nl2br}</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mpOptions">
			<div class="info">
				<h2 class="m-0 p-2">Acciones</h2>
			</div>
			<div class="cat-list">
				<div class="cat-list_item"><a href="{$tsConfig.url}/mensajes/avisos/?did={$tsMensaje.av_id}">Eliminar</a></div>
				<div class="cat-list_item"><a href="{$tsConfig.url}/mensajes/avisos/">&laquo; Volver a avisos</a></div>
			</div>
		</div>
	</div>
{else}
	<div class="alert-empty">{if $tsMensaje}{$tsMensaje}{else}No hay avisos o alertas{/if}</div>
{/if}