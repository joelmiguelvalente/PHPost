{if $tsUser->is_admod || 
	$tsUser->permiso('moderacion.usuarios.desbanear') || 
	$tsUser->permiso('moderacion.usuarios.suspender')}
	<div class="box">
		<div class="box-header">
			<span class="box_txt">Herramientas</span>
		</div>
		<div class="box-content tools">
			<div class="item block text-center py-2">
				<a href="{$tsConfig.url}/moderacion/buscador/1/1/{$tsPost.post_ip}" target="_blank">{$tsPost.post_ip}</a>
			</div>
			{if $tsUser->is_admod}
				<div class="item block text-center py-2">
					<a href="{$tsConfig.url}/admin/users?act=show&uid={$tsAutor.user_id}" class="edituser">Editar Usuario</a>
				</div>
			{/if}
			{if $tsAutor.user_id != $tsUser->uid}
				<div class="item block text-center py-2">
					<span onclick="moderacion.users.action({$tsAutor.user_id}, 'aviso', false)" class="alert">Enviar Aviso</span>
				</div>
			{/if}
			{if $tsAutor.user_id != $tsUser->uid && $tsUser->is_admod || 
			$tsUser->permiso('moderacion.usuarios.desbanear') || 
			$tsUser->permiso('moderacion.usuarios.suspender')}
				{if $tsAutor.user_baneado}
					{if $tsUser->is_admod || $tsUser->permiso('moderacion.usuarios.desbanear')}
						<div class="item block text-center py-2">
							<span onclick="mod.reboot({$tsAutor.user_id}, 'users', 'unban', false); $(this).remove()" class="unban">Desuspender Usuario</span>
						</div>
					{/if}
				{else}
					{if $tsUser->is_admod || $tsUser->permiso('moderacion.usuarios.suspender')}
						<div class="item block text-center py-2">
							<span onclick="moderacion.users.action({$tsAutor.user_id}, 'ban', false); $(this).remove()" class="ban">Suspender Usuario</span>
						</div>
					{/if}
				{/if}
			{/if}
		</div>
	</div>
{/if}