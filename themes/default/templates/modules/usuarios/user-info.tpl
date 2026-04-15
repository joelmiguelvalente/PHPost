<div class="user box">
	<h4 class="flex justify-between items-center">
		<a class="flex justify-start items-start gap-2" href="{$tsConfig.url}/@{$u.user_name}" style="color:#{$u.rango.color}">
			<img src="{$tsRoutes.assets.images}/icons/ranks/{$u.rango.image}" alt="{$u.rango.title}">{$u.user_name}
		</a>
		<div class="flex justify-end items-center gap-2">
			{$u.status.t} <strong class="status {$u.status.css} inline-bloc">&nbsp;</strong>
		</div>
	</h4>
	<div class="box-content grid gap-2">
		<div class="avatarBox">
			<a href="{$tsConfig.url}/@{$u.user_name}" class="block w-full h-full" title="{$u.user_name}" rel="internal">
				{include "blocks/Avatar.tpl" alt=$u.user_name id=$u.user_id size=75}
			</a>
		</div>
		<div class="infoBox">
			<div class="infoItem">Sexo: <strong>{if $u.user_sexo == 'female'}Mujer{elseif $u.user_sexo == 'male'}Hombre{else}No género{/if}</strong> - Pa&iacute;s: <strong>{$tsPaises[$u.user_pais]|default:'Desconocido'}</strong></div>
			<div class="infoItem">
				Posts: <strong>{$u.user_posts|number_abbr}</strong> - 
				Puntos: <strong>{$u.user_puntos|number_abbr}</strong> - 
				Comentarios: <strong>{$u.user_comentarios|number_abbr}</strong>
			</div>
			{if $u.user_id != $tsUser->uid}
				<div class="infoItem">
					<a href="{if !$tsUser->is_member}{$tsConfig.url}/registro/{else}javascript:mensaje.nuevo('{$u.user_name}');{/if}">Enviar Mensaje</a>
				</div>
			{/if}
		</div>
		<div class="message truncate">{if $u.p_mensaje}{$u.p_mensaje}{else}Sin mensaje personal{/if}</div>
	</div>
</div>
