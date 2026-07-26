{assign var=staff value=$staff|default:false}
{assign var=category value=$category|default:false}

<div class="listPost-item slim{if $data.post_sponsored == 1} patrocinado{/if}{if $data.post_status >= 1 || $data.post_status != 'publicado'} post_{$data.post_status}{/if}{if $data.user_activo == 0} user_activo{/if}{if $data.user_baneado == 1} user_baneado{/if}">
	<a class="item-avatar" href="{$tsConfig.url}/@{$data.user_name}" title="Perfil del usuario">
		{include "blocks/Avatar.tpl" id=$data.user_id size=64 alt="Perfil del usuario" lazy=true}
	</a>
	<div class="item-data">
		<h4><a class="item-title" href="{$tsConfig.url}/posts/{$data.c_seo}/{$data.post_id}/{$data.post_title|seo}.html" title="{if $data.post_status == 'revision'}El post est&aacute; en revisi&oacute;n{elseif $data.post_status == 'oculto'}El post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias{elseif $data.post_status == 'eliminado'}El post est&aacute; eliminado{elseif $data.user_activo == 0}La cuenta del usuario est&aacute; desactivada{else}{$data.post_title}{/if}"  target="_self">{$data.post_title}</a></h4>
		<span class="flex justify-start items-center gap-2"><img class="item-icon" {if $staff}src="{$tsRoutes['assets:images']}/icons/note.png" alt="Posts Fijado"{else}src="{$data.c_img}" alt="{$data.c_nombre}"{/if}>{if $staff}Posts Fijado{else}{$data.c_nombre}{/if}</span>
	</div>
	{if !$staff}
		<div class="flex justify-center items-end gap-2" style="flex-direction:column;">
			<strong title="Total de comentarios" aria-label="Total de comentarios">{$data.post_comments|number_abbr}</strong>
			<time datetime="{$data.post_date|fecha:'iso'}">{$data.post_date|short:'corto'}</time>
		</div>
	{/if}
</div>
