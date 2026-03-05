<div id="procesando"><div id="post"></div></div>
<div class="comment-write">
	<div class="comment-avatar" aria-label="{$tsUser->nick}">
		{include "blocks/Avatar.tpl" id=$tsUser->uid size=50 alt="Ver perfil" lazy=true class="avatar"}
	</div>
	<div class="comment-write-body">
		<textarea id="body_comm" placeholder="Escribe un comentario…" rows="1"></textarea>
		<div class="comment-write-actions" id="main-comment-actions">
			<input type="hidden" id="auser_post" value="{$tsPost.post_user}" />
			<button class="btn btn-sm btnBlue" onclick="comentario.nuevo(true)" id="btnsComment">Comentar</button>
		</div>
	</div>
</div>