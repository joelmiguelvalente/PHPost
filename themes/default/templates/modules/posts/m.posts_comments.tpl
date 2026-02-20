<div id="post-comentarios">
	<div class="comentarios-title flex justify-between items-center">
		<h4 class="m-0"><span id="ncomments">{$tsPost.post_comments}</span> Comentarios</h4>
		<img src="{$tsRoutes.tema.images}/cargando.gif" id="commentsLoads"/>
	</div>
	{if $tsPost.post_comments > $tsConfig.c_max_com}
		<div class="comentarios-title">
			<div class="paginadorCom"></div>
		</div>
	{/if}
	<div id="comentarios">
		<script>
			window.addEventListener('load', () => comentario.cargar({$tsPages.post_id}, 1, {$tsPages.autor}));
		</script>
		<div id="no-comments">Cargando comentarios espera un momento...</div>
	</div>
	{if $tsPost.post_comments > $tsConfig.c_max_com}
		<div class="comentarios-title">
			<div class="paginadorCom"></div>
		</div>
	{/if}

	{if $tsPost.post_block_comments == 1 && (!$tsUser->is_admod && $tsUser->permiso('moderacion.posts.comentarios_cerrado') == false)}
		<div id="no-comments">El post se encuentra cerrado y no se permiten comentarios.</div>
	{elseif $tsUser->is_admod == 0 && $tsUser->permiso('global.posts.comentar') == false}
		<div id="no-comments">No tienes permisos para comentar.</div>
	{elseif $tsUser->is_member && ($tsPost.post_block_comments != 1 || $tsPost.post_user == $tsUser->uid || $tsUser->is_admod || $tsUser->permiso('global.posts.comentar')) && $tsPost.block == 0}
		<div class="miComentario">
			{include "m.posts_comments_form.tpl"}
		</div>
	{/if}
</div>