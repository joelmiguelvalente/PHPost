{include "main_header.tpl"}
<script src="{$tsRoutes['assets:js']}/lite-youtube.js"></script>
<a name="cielo"></a>
{if $tsPost.post_status != 'publicado' || $tsAutor.user_activo != 1}
	<div class="alert-empty">Este post se encuentra {if $tsPost.post_status == 'eliminado'}eliminado{elseif $tsPost.post_status == 'oculto'} inactivo por acomulaci&oacute;n de denuncias{elseif $tsPost.post_status == 'revision'} en revisi&oacute;n{elseif $tsPost.post_status == 3} en revisi&oacute;n{elseif $tsAutor.user_activo == 0} oculto porque pertenece a una cuenta desactivada{/if}, t&uacute; puedes verlo porque {if $tsUser->is_admod == 1}eres Administrador{elseif $tsUser->is_admod == 2}eres Moderador{else}tienes permiso{/if}.</div><br>
{/if}
<div class="post-wrapper">
	<div class="post-wrapper-content">
		<div class="post-autor">
			{include "autor/autor_datos.tpl"}
			{include "autor/autor_herramientas.tpl"}
			{include "autor/autor_medallas.tpl"}
			{include "autor/autor_visitas.tpl"}
			{include "m.posts_related.tpl"}
		</div>
		<div class="post-contenedor relative rounded">
			{include "m.posts_content.tpl"}
			{if $tsUser->is_member}
				<a name="comentarios"></a>
				{include "m.posts_comments.tpl"}
				<a name="comentarios-abajo"></a>
			{/if}
			<br />
			{if !$tsUser->is_member}
				<div class="alert-empty mx-5 mb-3">Para poder comentar necesitas estar <a title="Crea una cuenta gratis" href="{$tsConfig.url}/registro/?redirect={$tsRoutes.redirectTo}">Registrado.</a> O.. ya tienes usuario? <a title="Inicia sesion" href="{$tsConfig.url}/login/?redirect={$tsRoutes.redirectTo}">Logueate!</a></div>
			{elseif $tsPost.block > 0}
				<div class="alert-empty">&iquest;Te has portado mal? {$tsPost.user_name} te ha bloqueado y no podr&aacute;s comentar sus post.</div>
			{/if}
			<div class="block py-2 text-center"><a class="irCielo" href="#cielo"><strong>Ir al cielo</strong></a></div>
		</div>
	</div>


</div>		
{include "main_footer.tpl"}
