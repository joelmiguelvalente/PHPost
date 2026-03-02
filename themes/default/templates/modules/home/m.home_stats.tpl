<div class="box">
	<div class="box-header">
		<span class="box_txt" title="Actualizado: {$tsStats.stats_time|hace}">Estad&iacute;sticas</span>
	</div>
	<div class="box-content">
		<div class="table">
			<a class="table-item" href="{$tsConfig.url}/usuarios/?online=true">
				<img width="16" height="16" src="{$tsRoutes.assets.images}/icons/power_on.png" alt="R&eacute;cord conectados: {$tsStats.stats_max_online}">
				<span title="R&eacute;cord conectados: {$tsStats.stats_max_online} {$tsStats.stats_max_time|fecha}"><strong>{$tsStats.stats_max_online|number_abbr}</strong> online</span>
			</a>
			<a class="table-item" href="{$tsConfig.url}/usuarios/">
				<img width="16" height="16" src="{$tsRoutes.assets.images}/icons/user.png" alt="Total de miembros">
				<span title="Total de miembros"><strong>{$tsStats.stats_miembros|number_abbr}</strong> miembros</span>
			</a>
			<div class="table-item">
				<img width="16" height="16" src="{$tsRoutes.assets.images}/icons/posts.png" alt="Total de posts">
				<span title="Total de posts"><strong>{$tsStats.stats_posts|number_abbr}</strong> posts</span>
			</div>
			<div class="table-item">
				<img width="16" height="16" src="{$tsRoutes.assets.images}/icons/comment.png" alt="Total de comentarios">
				<span title="Total de comentarios"><strong>{$tsStats.stats_comments|number_abbr}</strong> comentarios</span>
			</div>
		</div>
	</div>
</div>