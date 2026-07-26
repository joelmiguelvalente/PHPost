{if $tsMensajes.data}
	<div id="mpList">
		{foreach from=$tsMensajes.data item=mp}
			<div id="mp_{$mp.mp_id}" class="mpList-item mb-3 flex justify-start items-center gap-3{if $mp.mp_read_to == 0} unread{/if}">
				<a href="{$tsConfig.url}/mensajes/leer/{$mp.mp_id}" title="{$mp.mp_subject}" rel="internal" class="grid items-center gap-3 flex-grow-1">
					{include "blocks/Avatar.tpl" alt=$mp.user_name id=$mp.mp_from size=70}
					<div class="mpList-item_content">
						<div class="autor"><strong>{$mp.user_name}</strong></div>
						<div class="subject">{$mp.mp_subject}</div>
						<div class="preview">{if $mp.mp_type == 1}<i class="return"></i> {/if}{$mp.mp_preview}...</div>
					</div>
				</a>
				<div class="actions flex justify-center items-center flex-col gap-3">
					<span role="button" class="read marcar" title="Marcar como le&iacute;do" onclick="mensaje.marcar('{$mp.mp_id}','{$mp.mp_type}', 0, 1, this)"{if $mp.mp_read_to == 1} style="display:none"{/if}><img src="{$tsRoutes['assets:images']}/mensajes/read.svg" width="16" height="16" alt="Marcar como le&iacute;do" /></span>

					<span role="button" class="unread marcar" title="Marcar como no le&iacute;do" onclick="mensaje.marcar('{$mp.mp_id}','{$mp.mp_type}', 1, 1, this)"{if $mp.mp_read_to == 0} style="display:none"{/if}><img src="{$tsRoutes['assets:images']}/mensajes/unread.svg" width="16" height="16" alt="Marcar como no le&iacute;do" /></span>

					<span role="button" title="Eliminar" onclick="mensaje.eliminar('{$mp.mp_id}:{$mp.mp_type}',1)"><img src="{$tsRoutes['assets:images']}/mensajes/delete.svg" width="16" height="16" alt="Eliminar" /></span>
				</div>
			</div>
		{/foreach}
	</div>
{else}
	<div class="alert-empty">No hay mensajes</div>
{/if}
<div class="mpFooter">
	<div class="actions">{if $tsAction == ''}<strong>Ver: </strong> {if $tsQT == ''}<a href="{$tsConfig.url}/mensajes/?qt=unread">No le&iacute;dos</a>{else}<a href="{$tsConfig.url}/mensajes/">Todos los mensajes</a>{/if}{/if}</div>
	<div class="paginador">
		{if $tsMensajes.pages.prev != 0}<div style="text-align:left" class="floatL"><a href="{$tsConfig.url}/mensajes/{if $tsAction}{$tsAction}/{/if}?page={$tsMensajes.pages.prev}{if $tsQT != ''}&qt=unread{/if}">&laquo; Anterior</a></div>{/if}
		{if $tsMensajes.pages.next != 0}<div style="text-align:right" class="floatR"><a href="{$tsConfig.url}/mensajes/{if $tsAction}{$tsAction}/{/if}?page={$tsMensajes.pages.next}{if $tsQT != ''}&qt=unread{/if}">Siguiente &raquo;</a></div>{/if}
	</div>
</div>
