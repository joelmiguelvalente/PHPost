<div class="post-metadata">
	<div class="p-4">
		<div style="display: none;" class="mensajes"></div>
		{if ($tsUser->is_admod || $tsUser->permiso('global.posts.puntuar')) && $tsUser->is_member && $tsPost.post_user != $tsUser->uid && $tsUser->info.user_puntosxdar >= 1}
			<div class="dar-puntos flex justify-start items-center gap-3">
				<span>Dar Puntos:</span>
				<div class="puntuar flex justify-start items-center">
					<input type="number" id="points" value="{$tsPunteador.rango}" min="1" max="{$tsPunteador.rango}"/> 	
					<input type="button" value="Votar">  
				</div> (de <span class="count">{$tsUser->info.user_puntosxdar}</span> Disponibles)
			</div>
		{/if}
		<ul class="post-estadisticas flex justify-end items-center gap-3">
			{*<li class="relative text-right">
				<i aria-hidden="true" class="absolute icons medallas"></i> 
				<strong class="block">{$tsPost.m_total}</strong>
				<span>Medalla{if $tsPost.m_total != 1}s{/if}</span>
			</li>*}
			<li class="relative text-right">
				<i aria-hidden="true" class="absolute icons agregar_favoritos"></i> 
				<strong class="block favoritos_post">{$tsPost.post_favoritos|number_abbr}</strong>
				<span>Favoritos</span>
			</li> 
			<li class="relative text-right">
				<i aria-hidden="true" class="absolute icons visitas_post"></i> 
				<strong class="block">{$tsPost.post_hits|number_abbr}</strong>
				<span>Visitas</span>
			</li>
			<li class="relative text-right">
				<i aria-hidden="true" id="puntosPost" class="absolute icons puntos_post"></i> 
				<strong class="block">{$tsPost.post_puntos|number_abbr}</strong>
				<span>Puntos</span>
			</li>
			<li class="relative text-right">
				<i aria-hidden="true" class="absolute icons monitor"></i> 
				<strong class="block" data-post-follow>{$tsPost.post_seguidores|number_abbr}</strong>
				<span>Seguidores</span>
			</li>
		</ul>
	</div>
</div>