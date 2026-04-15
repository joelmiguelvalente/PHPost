<div class="box">
	<div class="box-content p-0">
		<img src="{$tsPost.post_portada}" class="post_portada" alt="{$tsPost.post_title}">
		<div class="box-content-avatar" style="z-index: 9;">
			<a href="{$tsConfig.url}/@{$tsAutor.user_name}">
				{include "blocks/Avatar.tpl" id=$tsAutor.user_id size=160 alt="Ver perfil de {$tsAutor.user_name}" lazy=true class="avatar"}
			</a>
		</div>
		<a href="{$tsConfig.url}/@{$tsAutor.user_name}" class="given-name" style="text-decoration:none;color:#{$tsAutor.rango.r_color}">{$tsAutor.user_name}</a>
		<span class="title">{$tsAutor.rango.r_name}</span>
		<div class="box-autor-badges flex justify-center items-center gap-2 py-2">
			<img src="{$tsRoutes.tema.images}/space.gif" class="status {$tsAutor.status.css}" title="{$tsAutor.status.t}"/>
			<img src="{$tsRoutes.assets.images}/icons/ranks/{$tsAutor.rango.r_image}" title="{$tsAutor.rango.r_name}" />
			<img src="{$tsRoutes.assets.images}/icons/{if $tsAutor.user_sexo == 0}female{else}male{/if}.png" title="{if $tsAutor.user_sexo == 0}Mujer{else}Hombre{/if}" />
			<img src="{$tsRoutes.assets.images}/flags/{$tsAutor.pais.icon}.png" style="padding:2px" title="{$tsAutor.pais.name}" />
		</div>

		<div class="buttons flex justify-center items-center gap-2 py-3">
			{if $tsAutor.user_id != $tsUser->uid}
				{if !$tsUser->is_member}
					<a title="Enviar mensaje privado" href="{$tsConfig.url}/registro/?redirect={$tsRoutes.redirectTo}">Mensaje privado</a>
					<a title="Seguir Usuario" href="{$tsConfig.url}/registro/?redirect={$tsRoutes.redirectTo}"><span class="icons follow"></span></a>
				{else}
					{* No se puede usar `href="javascript:...` *}
					<span style="cursor: pointer;" onclick="mensaje.nuevo('{$tsAutor.user_name|escape:javascript}');">
						<img title="Enviar mensaje privado" src="{$tsRoutes.tema.images}/icon-mensajes-recibidos.gif"/>
					</span>

					<span id="followUser" role="button" 
					data-action="{if $tsAutor.follow}un{/if}follow_user" 
					data-id="{$tsAutor.user_id}" 
					data-follow="{if $tsAutor.follow}1{else}0{/if}" 
					title="{if $tsAutor.follow}Dejar de seguir{else}Seguir usuario{/if}" 
					class="icons {if $tsAutor.follow}un{/if}follow"></span>
				{/if}
					
			{/if}
		</div>
		<hr>
		<div class="metadata-usuario">
			<div class="item flex justify-between items-center p-2">
				<span class="nData user_follow_count" data-count-follow>{$tsAutor.user_seguidores|number_abbr}</span>
				<span class="txtData">Seguidores</span>
			</div>
			<div class="item flex justify-between items-center p-2">
				<span class="nData" style="color: #0196ff">{$tsAutor.user_puntos|number_abbr}</span>
				<span class="txtData">Puntos</span>
			</div>
			<div class="item flex justify-between items-center p-2">
				<span class="nData">{$tsAutor.user_posts|number_abbr}</span>
				<span class="txtData">Posts</span>
			</div>
			<div class="item flex justify-between items-center p-2">
				<span style="color: #456c00" class="nData">{$tsAutor.user_comentarios|number_abbr}</span>
				<span class="txtData">Comentarios</span>
			</div>
		</div>
	</div>
	<!-- final del .box-content -->
</div>

<div class="box">
	<div class="box-content p-3">
		{if !$tsUser->is_member}
			<a class="btn block py-3" href="{$tsConfig.url}/registro/?redirect={$tsRoutes.redirectTo}">
				<span class="icons follow_post follow"></span> Seguir Post
			</a>

		{elseif $tsPost.post_user != $tsUser->uid}
			<div class="btn block py-3 mb-2" id="followPost">
				<span role="button" data-action="{if $tsPost.follow}un{/if}follow_post" data-id="{$tsPost.post_id}" data-follow="{if $tsPost.follow}1{else}0{/if}" title="{if $tsPost.follow}Dejar de seguir{else}Seguir post{/if}" class="icons {if $tsPost.follow}un{/if}follow"></span> {if $tsPost.follow}Dejar de seguir{else}Seguir post{/if}
			</div>

			<div class="btn block py-3 mb-2" id="postFavorito" role="button" title="Agregar a Favoritos"{if $tsUser->is_member} data-action="addFavorite"{else} data-reload="{$tsConfig.url}/registro/?redirect={$tsRoutes.redirectTo}"{/if}>
				<span class="icons agregar_favoritos"></span> Agregar a Favoritos
			</div>

			<span role="button" class="btn block py-3 mb-2 action-btn" data-action="denuncia" data-post-id="{$tsPost.post_id}" data-title="{$tsPost.post_title}" data-username="{$tsAutor.user_name}"><span class="icons denunciar_post"></span> Denunciar</span>
		{/if}

	</div>
</div>
