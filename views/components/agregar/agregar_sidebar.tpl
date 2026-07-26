<div class="col-right">	

	<div class="form-group" data-field="portada">
		<label class="form-label" for="portada">Portada del post</label>
		<input type="text" id="portada" name="portada" class="form-control required" value="{$tsDraft.post_portada}" data-role="field" placeholder="Url para la portada" required>
		<small class="form-helper" hidden></small>
	</div>

	<div class="form-group" data-field="category">
		<label class="form-label" for="categoria">Categoría</label>
		<select id="categoria" name="category" class="form-select required" tabindex="5" size="9" data-role="field">
			<option value="" selected>Elegir una categoría</option>
			{foreach from=$tsCategories item=c}
				<option value="{$c.cid}"{if $tsDraft.post_category == $c.cid} selected{/if} style="background-image:url({$tsRoutes.assets.images}/icons/cat/{$c.c_img})">{$c.c_nombre}</option>
			{/foreach}
		</select>
		<small class="form-helper" hidden></small>
	</div>

	<div class="form-group" data-field="opciones">
		<h4>Opciones</h4>

		<label class="option">
			<input type="checkbox" id="privado" name="private"{if $tsDraft.post_private} checked{/if}>
			<span>Sólo usuarios registrados</span>
			<small>Tu post será visible solo para usuarios registrados.</small>
		</label>

		<label class="option">
			<input type="checkbox" name="block_comments"{if $tsDraft.post_block_comments} checked{/if}>
			<span>Cerrar comentarios</span>
			<small>Recomendado para posts polémicos.</small>
		</label>

		<label class="option">
			<input type="checkbox" name="visitantes"{if $tsDraft.post_visitantes} checked{/if}>
			<span>Mostrar visitantes recientes</span>
			<small>Tu post mostrar&aacute; los &uacute;ltimos visitantes que ha tenido.</small>
		</label>

		<label class="option">
			<input type="checkbox" name="smileys"{if $tsDraft.post_smileys} checked{/if}>
			<span>Sin Smileys</span>
			<small>Si tu post no necesita smileys, desact&iacute;valos.</small>
		</label>

		{if $tsUser->is_admod}
			<label class="option">
				<input type="checkbox" name="sponsored"{if $tsDraft.post_sponsored} checked{/if}>
				<span>Patrocinado</span>
				<small>Resalta este post.</small>
			</label>
		{/if}

		{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.fijar')}
			<label class="option">
				<input type="checkbox" name="sticky"{if $tsDraft.post_sticky} checked{/if}>
				<span>Sticky</span>
				<small>Fijar el post en la home.</small>
			</label>
		{/if}
	</div>
</div>
