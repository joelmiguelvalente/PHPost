<div id="post-izquierda">
	<div class="box">
		<div class="box-header">
			<span class="box_txt">Filtrar Actividad</span>
		</div>
		<div class="box-content">
			<ul class="check-filter">
				<li class="emptyData">Elige que notificaciones recibir y cuales no.</li>
				<li class="divider"></li>

				<li><strong>Mis Posts</strong></li>
				<li><label><input data-type="1" type="checkbox"{if $tsData.filtro.f1} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_star"></span> Favoritos</label></li>
				<li><label><input data-type="2" type="checkbox"{if $tsData.filtro.f2} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_comment_post"></span> Comentarios</label></li>
				<li><label><input data-type="3" type="checkbox"{if $tsData.filtro.f3} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_points"></span> Puntos Recibidos</label></li>
							
				<li><strong>Mis Comentarios</strong></li>
				<li><label><input data-type="8" type="checkbox"{if $tsData.filtro.f8} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_voto"></span> Votos</label></li>
				<li><label><input data-type="9" type="checkbox"{if $tsData.filtro.f9} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_comment_resp"></span> Respuestas</label></li>

				<li><strong>Usuarios que sigo</strong></li>
				<li><label><input data-type="4" type="checkbox"{if $tsData.filtro.f4} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_follow"></span> Nuevos</label></li>
				<li><label><input data-type="5" type="checkbox"{if $tsData.filtro.f5} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_post"></span> Posts</label></li>
				<li><label><input data-type="6" type="checkbox"{if $tsData.filtro.f6} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_share"></span> Recomendaciones</label></li>
				<li><label><input data-type="10" type="checkbox"{if $tsData.filtro.f10} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_photo"></span> Fotos</label></li>

				<li><strong>Posts que sigo</strong></li>
				<li><label><input data-type="7" type="checkbox"{if $tsData.filtro.f7} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_blue_ball"></span> Comentarios</label></li>

				<li><strong>Mis Fotos</strong></li>
				<li><label><input data-type="11" type="checkbox"{if $tsData.filtro.f11} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_comment_post"></span> Comentarios</label></li>

				<li><strong>Perfil</strong></li>
				<li><label><input data-type="12" type="checkbox"{if $tsData.filtro.f12} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_status"></span> Publicaciones</label></li>
				<li><label><input data-type="13" type="checkbox"{if $tsData.filtro.f13} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_w_comment"></span> Comentarios</label></li>
				<li><label><input data-type="14" type="checkbox"{if $tsData.filtro.f14} checked{/if} onclick="notifica.filter()"/><span class="monac_icons ma_w_like"></span> Likes</label></li>
			</ul>
		</div>
	</div>
	{if $tsConfig.c_allow_live == 1}
		<div class="box">
			<div class="box-header">
				<span class="box_txt">Notificaciones Live</span>
			</div>
			<div class="box-content">
				<label class="block py-1">
					<input type="checkbox"{if $tsStatus.live_nots == 'ON'} checked{/if} onclick="live.ch_status('nots');"/> Mostrar notificaciones
				</label>
				<label class="block py-1">
					<input type="checkbox"{if $tsStatus.live_mps == 'ON'} checked{/if} onclick="live.ch_status('mps');"/> Mostrar mensajes nuevos
				</label>
				<label class="block py-1">
					<input type="checkbox"{if $tsStatus.live_sound == 'ON'} checked{/if} onclick="live.ch_status('sound');"/> Reproducir sonidos
				</label>
		</div>
	{/if}
</div>