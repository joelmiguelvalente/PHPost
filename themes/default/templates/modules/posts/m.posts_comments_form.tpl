<div id="procesando"><div id="post"></div></div>
<div class="box-comment p-3">
	<div class="box-avatar relative">
		{include "blocks/Avatar.tpl" id=$tsUser->uid size=50 alt="Ver perfil" lazy=true class="avatar"}
		<div id="gif_cargando" class="absolute">
			<img src="{$tsRoutes.tema.images}/tload.gif" />
		</div>
	</div>
	<div class="comment">
		<div class="error"></div>
		<textarea id="body_comm" class="form-control" placeholder="Escribir un comentario...">Escribir un comentario...</textarea>
		<div class="buttons">
			<input type="hidden" id="auser_post" value="{$tsPost.post_user}" />
			<input type="button" onclick="comentario.nuevo(true)" class="mBtn btnOk" value="Enviar Comentario" tabindex="3" id="btnsComment"/>
		</div>
	</div>
</div>


