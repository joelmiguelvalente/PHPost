{if !$tsInfo.user_activo || $tsInfo.user_baneado}
	<div class="alert-empty danger">Cuenta {if !$tsInfo.user_activo}desactivada{else}baneada{/if}</div>
{/if}
<div class="header-profile">
	<!-- DATOS DEL USUARIO -->
	<div class="profile-data grid items-center gap-3 p-3">
		<!-- AVATAR -->
		<div class="data-avatar">
			<a href="{$tsConfig.url}/@{$tsInfo.nick}" title="{$tsInfo.nick}" rel="internal">
				{include "blocks/Avatar.tpl" alt=$tsInfo.nick id=$tsInfo.uid size=130}
			</a>
		</div>
		<!-- INFORMACION -->
		<div class="data-info relative">
			<h1 class="nick m-0 p-1">{$tsInfo.nick}</h1>
			<small class="realname block px-1">{if $tsInfo.p_nombre}{$tsInfo.p_nombre}{/if}{if $tsInfo.user_pais && $tsInfo.user_pais != 'Desconocido'} - {$tsInfo.user_pais}{/if}</small>
			<span class="block px-1">Se unió hace {$tsInfo.user_registro|hace}</span>
			<span class="frase-personal block p-1">{$tsInfo.p_mensaje}</span>
			{if $tsUser->uid != $tsInfo.uid && $tsUser->is_member}
				<div class="ex_opts flex justify-start items-start gap-2">
					<span class="action-btn btn btn-sm btn-{if $tsInfo.block.bid}success{else}danger{/if}" role="button" data-action="bloquear" data-id="{$tsInfo.uid}" data-block="{if $tsInfo.block.bid}false{else}true{/if}">{if $tsInfo.block.bid}Desbloquear{else}Bloquear{/if}</span>
					<span class="action-btn btn btn-sm btn-warning" role="button" data-action="denuncia" data-id="{$tsInfo.uid}" data-nick="{$tsInfo.nick}">Denunciar</span>

					{if ($tsUser->is_admod || $tsUser->permiso('moderacion.usuarios.suspender')) && !$tsInfo.user_baneado}
						<span class="action-btn btn btn-sm btn-danger" role="button" data-action="ban" data-id="{$tsInfo.uid}">Suspender</span>
					{/if}
				</div>
				<span id="followUser" role="button" data-action="{if $tsInfo.follow}un{/if}follow_user" data-id="{$tsInfo.uid}" data-follow="{if $tsInfo.follow}1{else}0{/if}" title="{if $tsInfo.follow}Dejar de seguir{else}Seguir usuario{/if}" class="absolute icons {if $tsInfo.follow}un{/if}follow"></span>
			{/if}
		</div>
		<!-- ESTADISTICAS -->
		<div class="data-stats grid gap-2">
			<div class="stats-item rounded relative p-2 text-right">
				<strong style="color:#{$tsInfo.stats.r_color}">{$tsInfo.stats.r_name}</strong>
				<span class="block">Rango</span>
				<span style="position:absolute;top:6px;left:0"><span title="{$tsInfo.status.t}" class="status {$tsInfo.status.css} absolute"></span></span>
			</div>
			<div class="stats-item rounded p-2 text-right">
				<strong>{$tsInfo.stats.user_puntos}</strong>
				<span class="block">Puntos</span>
			</div>
			<div class="stats-item rounded p-2 text-right">
				<strong>{$tsInfo.stats.user_posts}</strong>
				<span class="block">Posts</span>
			</div>
			<div class="stats-item rounded p-2 text-right">
				<strong>{$tsInfo.stats.user_comentarios}</strong>
				<span class="block">Comentarios</span>
			</div>
			<div class="stats-item rounded p-2 text-right">
				<strong>{$tsInfo.stats.user_seguidores}</strong>
				<span class="block">Seguidores</span>
			</div>
			<div class="stats-item rounded p-2 text-right">
				<strong>{$tsInfo.stats.user_fotos}</strong>
				<span class="block">Fotos</span>
			</div>
		</div>
	</div>
	<!-- NAVEGACION -->
	<div class="profile-navegation">
		<div id="tabs_menu" class="flex justify-start items-start gap-1 relative">
			{if $tsType == 'news' || $tsType == 'story'}
				<span class="tab-item py-1 px-3 active" role="button" data-tab="news">{if $tsType == 'story'}Publicaci&oacute;n{else}Noticias{/if}</span>
				<!-- <a href="#" onclick="perfil.load_tab('news', this); return false"></a> -->
			{/if}
			<span class="tab-item py-1 px-3{if $tsType == 'wall'} active{/if}" role="button" data-tab="wall">Muro</span>
			<span class="tab-item py-1 px-3" role="button" data-tab="actividad">Actividad</span>
			<span class="tab-item py-1 px-3" role="button" data-tab="info">Informaci&oacute;n</span>
			<span class="tab-item py-1 px-3" role="button" data-tab="posts">Posts</span>
			<span class="tab-item py-1 px-3" role="button" data-tab="seguidores">Seguidores</span>
			<span class="tab-item py-1 px-3" role="button" data-tab="siguiendo">Siguiendo</span>
			<span class="tab-item py-1 px-3" role="button" data-tab="medallas">Medallas</span>
			{if $tsUser->uid != $tsInfo.uid && $tsUser->is_member}
				<a href="#" onclick="mensaje.nuevo('{$tsInfo.nick}','','',''); return false"><span style="float:none; height:14px;width:16px;" class="systemicons mensaje"></span></a>
			{/if}
			{if $tsUser->is_admod == 1}
				<a class="absolute" style="top: 0.5rem;right: 1rem;" href="{$tsConfig.url}/admin/users?act=show&uid={$tsInfo.uid}">
					<img title="Editar a {$tsInfo.nick}" src="{$tsRoutes.assets.images}/icons/editar.png"/>
				</a>
			{/if}
		</div>
	</div>
</div>