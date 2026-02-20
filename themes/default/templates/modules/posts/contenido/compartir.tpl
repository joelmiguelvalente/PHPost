<ul class="post-compartir clearbeta">
				<li class="share-big">
					<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="{$tsConfig.titulo}" data-lang="es">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
				</li>
				<li class="share-big">
					<a name="fb_share" share_url="{$tsConfig.url}/posts/{$tsPost.categoria.c_seo}/{$tsPost.post_id}/{$tsPost.post_title|seo}.html" type="box_count" href="http://www.facebook.com/sharer.php">Compartir</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
				</li>
				<li class="share-big">
					<span class="share-t-count">{$tsPost.post_shared}</span>
					<a href="{if !$tsUser->is_member}{$tsConfig.url}/registro/{else}javascript:notifica.handleRecomendar({$tsPost.post_id}, 'post'){/if}" class="share-t"></a>
				</li>
				<li class="txt-movi">Compartir en:</li>
			</ul>