{if $tsPost.post_user == $tsUser->uid && !$tsUser->is_admod && 
!$tsUser->permiso('moderacion.posts.fijar') && 
!$tsUser->permiso('moderacion.posts.abrir_cerrar') && 
!$tsUser->permiso('moderacion.posts.eliminar') && 
!$tsUser->permiso('moderacion.posts.editar')}
	<div class="mod-actions flex justify-start items-center gap-2 p-3">
		<strong>Acciones del Post:</strong>
		<span data-action="delete" data-post-id="{$tsPost.post_id}" class="action-btn pointer delete"><img alt="Borrar" src="{$tsRoutes['tema:images']}/borrar.png"/> Borrar</span>
		<a href="{$tsConfig.url}/editar?id={$tsPost.post_id}"><img alt="Borrar" src="{$tsRoutes['tema:images']}/editar.png"/> Editar</a>
	</div>
{elseif ($tsUser->is_admod && $tsPost.post_status == 'publicado') ||
$tsUser->permiso('moderacion.posts.fijar') || 
$tsUser->permiso('moderacion.posts.abrir_cerrar') || 
$tsUser->permiso('moderacion.posts.ocultar') || 
$tsUser->permiso('moderacion.posts.eliminar') || 
$tsUser->permiso('moderacion.posts.editar')}
	<div class="mod-actions flex justify-start items-center gap-2 p-3" data-is-author="{if $tsAutor.user_id != $tsUser->uid}false{else}true{/if}">
		<strong>Moderar Post:</strong>
		{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.fijar')}
        	<span data-action="sticky" data-post-id="{$tsPost.post_id}" data-current-state="{if $tsPost.post_sticky == 1}1{else}0{/if}" class="action-btn pointer sticky">{if $tsPost.post_sticky == 1}Quitar{else}Poner{/if} Sticky</span>
		{/if}
		{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.abrir_cerrar')}
        	<span data-action="openclosed" data-post-id="{$tsPost.post_id}" data-current-state="{if $tsPost.post_block_comments == 1}1{else}0{/if}" class="action-btn pointer openclosed">{if $tsPost.post_block_comments == 1}Abrir{else}Cerrar{/if} Post</span>
		{/if}
		{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.ocultar')}
			<span data-toggle-target="desapprove" class="action-btn pointer des_approve">Ocultar Post</span>
		{/if}
		{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.editar') || $tsAutor.user_id == $tsUser->uid}
			<a href="{$tsConfig.url}/editar?id={$tsPost.post_id}" class="edit">Editar</a>
		{/if}
		{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.eliminar') || $tsAutor.user_id == $tsUser->uid}
			<span data-action="delete" data-post-id="{$tsPost.post_id}" class="action-btn pointer delete">Borrar</span>
		{/if}
   </div>
   <div id="desapprove" style="display: none;">
   	<div class="flex gap-2 p-3">
   		<input type="text" id="d_razon" name="d_razon" maxlength="150" size="60" class="form-control" placeholder="Raz&oacute;n de la revisi&oacute;n" required>
   		<input type="button" data-action="ocultar" data-post-id="{$tsPost.post_id}" class="action-btn mBtn btnDelete" name="desapprove" value="Continuar"/>
   	</div>
   </div>
{/if}
