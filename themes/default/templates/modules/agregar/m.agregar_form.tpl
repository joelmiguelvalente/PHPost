{if $tsUser->is_admod || $tsUser->can('gopp')}
	<div class="form-add-post">
		<form action="{$tsConfig.url}/agregar.php{if $tsAction == 'editar'}?action=editar&pid={$tsPid}{/if}" method="post" name="newpost" id="newpost" autocomplete="off">
			{if $tsDraft.bid}
				<input type="hidden" name="borrador_id" value="{$tsDraft.bid}">
			{/if}
			<ul class="clearbeta post-form">

				<li data-field="titulo">
					<label for="titulo">Título</label>
					<span class="errormsg" hidden></span>
					<input type="text" id="titulo" name="titulo" class="text-inp required" tabindex="1" value="{$tsDraft.b_title}" style="width:760px" data-role="field"
					>
					<div id="repost"></div>
				</li>

				<li data-field="cuerpo">
					<a name="post"></a>
					<label for="markItUp">Contenido del Post</label>
					<span class="errormsg" hidden></span>
					<textarea id="markItUp" name="cuerpo" class="required" tabindex="2" data-role="field" rows="30">{$tsDraft.b_body}</textarea>
				</li>

				<li data-field="tags">
					<label for="tags">Tags</label>
					<span class="errormsg" hidden></span>
					<input
						type="text"
						id="tags"
						name="tags"
						class="text-inp required"
						tabindex="4"
						maxlength="128"
						value="{$tsDraft.b_tags}"
						placeholder="gol, ingleses, Copa Oro, futbol, Argentina"
						data-role="field"
					>
				</li>

				<li class="special-left clearbeta" data-field="categoria">
					<label for="categoria">Categoría</label>
					<span class="errormsg" hidden></span>
					<select
						id="categoria"
						name="categoria"
						class="agregar required"
						tabindex="5"
						size="9"
						style="width:300px; float:left"
						data-role="field"
					>
						<option value="">Elegir una categoría</option>
						{foreach from=$tsCategories item=c}
							<option
								value="{$c.cid}"
								{if $tsDraft.b_category == $c.cid}selected{/if}
								style="background-image:url({$tsRoutes.tema.images}/icons/cat/{$c.c_img})"
							>
								{$c.c_nombre}
							</option>
						{/foreach}
					</select>
				</li>

				<li class="special-right clearbeta" data-field="opciones">
					<label>Opciones</label>

					<div class="option clearbeta">
						<input type="checkbox" id="privado" name="privado" tabindex="6" {if $tsDraft.b_private}checked{/if}>
						<label for="privado">Sólo usuarios registrados</label>
						<p>Tu post será visible solo para usuarios registrados.</p>
					</div>

					<div class="option clearbeta">
						<input type="checkbox" id="sin_comentarios" name="sin_comentarios" tabindex="7" {if $tsDraft.b_block_comments}checked{/if}>
						<label for="sin_comentarios">Cerrar comentarios</label>
						<p>Recomendado para posts polémicos.</p>
					</div>

					{if $tsUser->is_admod}
					<div class="option clearbeta">
						<input type="checkbox" id="patrocinado" name="patrocinado" tabindex="8" {if $tsDraft.b_sponsored}checked{/if}>
						<label for="patrocinado">Patrocinado</label>
						<p>Resalta este post.</p>
					</div>
					{/if}

					{if $tsUser->is_admod || $tsUser->can('most')}
					<div class="option clearbeta">
						<input type="checkbox" id="sticky" name="sticky" tabindex="9" {if $tsDraft.b_sticky}checked{/if}>
						<label for="sticky">Sticky</label>
						<p>Fijar el post en la home.</p>
					</div>
					{/if}
				</li>

				{if ($tsUser->is_admod || $tsUser->can('moedpo')) && $tsDraft.b_title && $tsDraft.b_user != $tsUser->uid}
				<li data-field="razon">
					<label for="razon">Razón</label>
					<span class="errormsg" hidden></span>
					<input
						type="text"
						id="razon"
						name="razon"
						maxlength="150"
						class="text-inp"
						data-role="field"
					>
					<p>Indica por qué modificaste este post.</p>
				</li>
				{/if}

			</ul>

			<div class="end-form clearbeta">
				<input
					type="button"
					id="borrador-save"
					class="mBtn btnOk floatL"
					value="Guardar en borradores"
				>
				<input
					type="button"
					name="preview"
					class="mBtn btnGreen"
					value="Continuar »"
				>
				<div id="borrador-guardado" class="borrador-status"></div>
			</div>

		</form>
	</div>
{else}
	<div class="emptyData clearfix">Lo sentimos, pero no puedes publicar un nuevo post.</div>
{/if}