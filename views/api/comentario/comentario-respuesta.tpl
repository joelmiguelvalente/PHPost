<div class="reply-write" id="reply-form-{$c.cid}" style="display:none; margin-top:0.5rem;">
	{include "blocks/Avatar.tpl" id=$tsUser->uid size=32 alt="Ver perfil" lazy=true class="comment-avatar comment-avatar-sm" placeholder=false}
	<div class="reply-write-body">
		<textarea placeholder="Responder a {$c.user_name}…" rows="1" ></textarea>
		<div class="reply-write-actions">
			<button class="btn btn-sm btn-danger" onclick="comentario.responder('reply-form-{$c.cid}')">Cancelar</button>
			<button class="btn btn-sm btn-primary" onclick="comentario.responder('reply-form-{$c.cid}', true, {$c.cid})">Responder</button>
		</div>
	</div>
</div>