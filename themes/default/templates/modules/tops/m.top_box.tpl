<div class="box">
	<div class="box-header">
		<span class="box_txt" title="{$header}">{$header}</span>
		<div class="box_icon">
			<span class="{$icon}"></span>
		</div>
	</div>
	<div class="box-content">
		{if !$data}
			<div class="alert-empty">Nada por aqui</div>
		{else}
			<ol>
				{if $type == 'posts'}
					{foreach from=$data item=p}
						<li class="categoriaPost categoria-item grid gap-2">
							<img width="16" height="16" src="{$tsRoutes.assets.images}/icons/categories/{$p.c_img}" alt="{$p.c_nombre}">
							<a class="truncate" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" title="{$p.post_title}">{$p.post_title}</a> 
							<span class="rounded text-center block">{$p.$count}</span>
						</li>
					{/foreach}
				{else}
					{foreach from=$data item=u}
						<li class="categoriaUsuario categoria-item flex justify-start items-center gap-2">
							<img width="24" height="24" class="rounded-full" src="{$tsRoutes.storage.avatar}/user_{$u.user_id}/thumb_avatar.webp" alt="{$u.user_name}">
							<a href="{$tsConfig.url}/@{$u.user_name}" title="Usuario {$u.user_name}">{$u.user_name}</a> 
							<span class="rounded text-center block" style="width: 2rem;">{$u.total}</span>
						</li>
					{/foreach}
				{/if}
			</ol>
		{/if}
	</div>
</div>
