{if $tsDraft.post_id}
   <input type="hidden" name="borrador_id" value="{$tsDraft.post_id}">
{/if}
<div class="col-left">	
	<div class="form-group" data-field="title">
		<label class="form-label" for="titulo">Título</label>
		<input type="text" id="titulo" name="title" class="form-control required" value="{$tsDraft.post_title}" data-role="field" required>
		<small class="form-helper" hidden></small>
		<div id="repost"></div>
	</div>

	<a name="post"></a>
	<div class="form-group" data-field="body">
		<label class="form-label" for="cuerpo">Contenido del Post</label>
		<textarea id="cuerpo" name="body" class="form-control required" data-role="field" rows="30" required>{$tsDraft.post_body}</textarea>
	</div>

	<div class="form-group" data-field="tags">
		<label class="form-label" for="tags">Tags</label>
		<input type="text" id="tags" name="tags" class="form-control required" maxlength="128" value="{$tsDraft.post_tags}" placeholder="gol, ingleses, Copa Oro, futbol, Argentina" data-role="field" required>
		<small class="form-helper" hidden></small>
	</div>

	{if ($tsUser->is_admod || $tsUser->permiso('moderacion.posts.editar')) && $tsDraft.post_title && $tsDraft.post_user != $tsUser->uid}
		<div class="form-group" data-field="razon">
			<label class="form-label" for="razon">Razón</label>
			<input type="text" id="razon" name="razon" maxlength="150" class="form-control" data-role="field">
			<small class="form-helper">Indica por qué modificaste este post.</small>
		</div>
	{/if}
</div>