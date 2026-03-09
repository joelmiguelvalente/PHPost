{assign var=staff value=$staff|default:false}
{assign var=category value=$category|default:false}

<div class="listPost-item{if $data.post_sponsored == 1} patrocinado{/if}{if $data.post_private == 1} privado{/if}{if $data.post_status != 0} post_status{$data.post_status}{/if}{if $data.user_activo == 0} user_activo{/if}{if $data.user_baneado == 1} user_baneado{/if}">
	<div class="item-header">
		<img class="item-icon" {if $staff}src="{$tsRoutes.assets.images}/icons/note.png" alt="Posts Fijado"{else}src="{$data.c_img}" alt="{$data.c_nombre}"{/if}>
		<a class="item-title truncate" href="{$tsConfig.url}/posts/{$data.c_seo}/{$data.post_id}/{$data.post_title|seo}.html" title="{if $data.post_status == 3}El post est&aacute; en revisi&oacute;n{elseif $data.post_status == 1}El post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias{elseif $data.post_status == 2}El post est&aacute; eliminado{elseif $data.user_activo == 0}La cuenta del usuario est&aacute; desactivada{else}{$data.post_title}{/if}"  target="_self">{$data.post_title}</a>
	</div>
	{if !$staff}
		<div class="item-data">
			<div>
				<time datetime="{$data.post_date|fecha:'iso'}">{$data.post_date|hace:true}</time>
				<a href="{$tsConfig.url}/@{$data.user_name}" title="Perfil del usuario">@{$data.user_name}</a>
				<span>Puntos <strong>{$data.post_puntos|number_abbr}</strong></span>
				<span>Comentarios <strong>{$data.post_comments|number_abbr}</strong></span>
			</div>
			{if $category}
				<a class="category" href="{$tsConfig.url}/posts/{$data.c_seo}/">{$data.c_nombre}</a>
			{/if}
		</div>
	{/if}
</div>