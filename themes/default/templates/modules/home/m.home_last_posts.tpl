<div class="box">
	<div class="box-header">
		<span class="box_txt">&Uacute;ltimos posts en {$tsConfig.titulo}</span>
		<div class="box_icon">
			<a href="{$tsConfig.url}/rss/ultimos-post" title="&Uacute;ltimos posts en {$tsConfig.titulo}"><span class="systemicons sRss" alt="RSS" style="position:relative;z-index:87"></span></a>
		</div>
	</div>
	<div class="box-content" style="padding:0">
		{foreach $tsPostsStickys item=p}
			{include "m.home_post_item.tpl" staff=true data=$p}
		{/foreach}
		{foreach $tsPosts item=p}
			{include "m.home_post_item.tpl" category=false data=$p}
		{foreachelse}
			<div class="p-2">
				<div class="alert-empty">No hay posts aqu&iacute;</div>
			</div>
		{/foreach}
	</div>
	<div class="box-footer">
		{if $tsPages.prev > 0 && !$tsPages.max}
			<a href="?page={$tsPages.prev}" class="btn">&laquo; Anterior</a>
		{/if}
		{if $tsPages.next <= $tsPages.pages && $tsPages.current > 1}
			<a href="?page={$tsPages.next}" class="btn">Siguiente &raquo;</a>
		{elseif $tsPages.max}
			<a href="?page=2">Siguiente &raquo;</a>
		{/if}
	</div>
</div>
{*				<div class="clearbeta lastPosts">
					{if $tsPostsStickys}
					
					<div class="body">
						<ul>
							{foreach from=$tsPostsStickys item=p}
							<li {if $p.post_status == 3}style="background-color:#f1f1f1;"{elseif $p.post_status == 1}style="background-color:coral;"{elseif $p.post_status == 2}style="background-color:rosyBrown;"{elseif $p.user_activo == 0}style="background-color:burlyWood;"{elseif $p.user_baneado == 1}style="background-color:orange;"{/if} class="categoriaPost sticky{if $p.post_sponsored == 1} patrocinado{/if}">
							<a {if $p.post_status == 3}class="qtip" title="El post est&aacute; en revisi&oacute;n"{elseif $p.post_status == 1}class="qtip" title="El post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias"{elseif $p.post_status == 2}class="qtip" title="El post est&aacute; eliminado"{elseif $p.user_activo == 0}class="qtip" title="La cuenta del usuario est&aacute; desactivada"{/if}  href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" style="background:url({$p.c_img}) no-repeat 5px center" title="{$p.post_title}" target="_self" class="title">{$p.post_title|truncate:55}</a>
							</li>
							{/foreach}
						</ul>
					</div>
					{/if}
					<div class="header">
						<div class="box_txt ultimos_posts">&Uacute;ltimos posts en {$tsConfig.titulo}</div>
						<div class="box_rss">
							<a href="/rss/ultimos-post">
								<span class="systemicons sRss" style="position:relative;z-index:87"></span>
							</a>
						</div>
						<div class="clearBoth"></div>
					</div>
					<div class="body">
						<ul>
							{if $tsPosts}
							{foreach from=$tsPosts item=p}
							<li class="categoriaPost" style="background-image:url({$p.c_img}); {if $p.post_status == 3} background-color:#f1f1f1; {elseif $p.post_status == 1}background-color:coral;{elseif $p.post_status == 2} background-color:rosyBrown;{elseif $p.user_activo == 0} background-color:burlyWood;{elseif $p.user_baneado == 1} background-color:orange;{/if}" >
								<a {if $p.post_status == 3}class="qtip" title="El post est&aacute; en revisi&oacute;n"{elseif $p.post_status == 1}class="qtip" title="El post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias"{elseif $p.post_status == 2}class="qtip" title="El post est&aacute; eliminado"{elseif $p.user_activo == 0}class="qtip" title="La cuenta del usuario est&aacute; desactivada"{elseif $p.user_baneado == 1}class="qtip" title="La cuenta del usuario est&aacute; suspendida"{/if} class="title {if $p.post_private}categoria privado{/if}" alt="{$p.post_title}" title="{$p.post_title}" target="_self" href="{$tsConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:50}</a>
								<span>{$p.post_date|hace:true} &raquo; <a href="{$tsConfig.url}/@{$p.user_name}" class="hovercard" uid="{$p.post_user}"><strong>@{$p.user_name}</strong></a> &middot; Puntos <strong>{$p.post_puntos}</strong> &middot; Comentarios <strong>{$p.post_comments}</strong></span>
								<span class="floatR"><a href="{$tsConfig.url}/posts/{$p.c_seo}/">{$p.c_nombre}</a></span>
							</li>
							{/foreach}
							{else}
							<li class="alert-empty">No hay posts aqu&iacute;</li>
							{/if}
						</ul>
						<br clear="left"/>
					</div>
				
					<div class="footer size13">
						{if $tsPages.prev > 0 && !$tsPages.max}
							<a href="pagina{$tsPages.prev}" class="floatL">&laquo; Anterior</a>
						{/if}
						{if $tsPages.next <= $tsPages.pages}
							<a href="pagina{$tsPages.next}" class="floatR">Siguiente &raquo;</a>
						{elseif $tsPages.max}
							<a href="pagina2">Siguiente &raquo;</a>
						{/if}
					</div>
				 </div>*}