{*
 * @name m.buscador_resultados.tpl
 * @description Renderiza los resultados del buscador según el engine activo: web, tags, usuarios, fotos y muro.
 * @author Miguel92
 * @copyright 2026
 * @note Desarrollado con asistencia de Claude (Anthropic)
*}
<div id="resultados">
	<div id="showResult">

		{* POSTS (web) *}
		{if $tsEngine == 'web'}
			{if $tsResults.data}
				<div class="results-list results-posts">
					{foreach from=$tsResults.data item=r}
						<div id="div_{$r.post_id}" class="result-item result-post">
							<a class="result-title" href="{$tsConfig.url}/posts/{$r.c_seo}/{$r.post_id}/{$r.post_title|seo}.html">
								<img class="cat-icon" src="{$tsRoutes['assets:images']}/icons/categories/{$r.c_img}" alt="{$r.c_nombre}"/>
								{$r.post_title}
							</a>
							<div class="result-meta">
								<span class="meta-item">
									<img src="{$tsRoutes['assets:images']}/icons/clock.png" alt=""/> {$r.post_date|hace:true}
								</span>
								<span class="meta-sep">·</span>
								<span class="meta-item">
									<img src="{$tsRoutes['assets:images']}/icons/autor.png" alt=""/>
									<a href="{$tsConfig.url}/@{$r.user_name}">{$r.user_name}</a>
								</span>
								<span class="meta-sep">·</span>
								<span class="meta-item meta-cat">{$r.c_nombre}</span>
								<span class="meta-sep">·</span>
								<span class="meta-item">
									<img src="{$tsRoutes['assets:images']}/icons/relacionados.png" alt=""/>
									<a href="{$tsConfig.url}/buscador/?query={$r.post_title|seo}&engine={$tsEngine}&category={$tsCategory}&autor={$tsAutor}">Relacionados</a>
								</span>
							</div>
						</div>
					{foreachelse}
						<p class="empty">Lo siento, no se encontraron resultados...</p>
					{/foreach}
				</div>
			{else}
				<p class="empty">¿Qué tipo de búsqueda quieres realizar?</p>
			{/if}

		{* TAGS *}
		{elseif $tsEngine == 'tags'}
			{if $tsResults.data}
				<div class="results-list results-posts">
					{foreach from=$tsResults.data item=r}
						<div id="div_{$r.post_id}" class="result-item result-post">
							<a class="result-title" href="{$tsConfig.url}/posts/{$r.c_seo}/{$r.post_id}/{$r.post_title|seo}.html">
								<img class="cat-icon" src="{$tsRoutes['assets:images']}/icons/categories/{$r.c_img}" alt="{$r.c_nombre}"/>
								{$r.post_title}
							</a>
							<div class="result-meta">
								<span class="meta-item">
									<img src="{$tsRoutes['assets:images']}/icons/clock.png" alt="Tiempo"/> {$r.post_date|hace:true}
								</span>
								<span class="meta-sep">·</span>
								<span class="meta-item">
									<img src="{$tsRoutes['assets:images']}/icons/autor.png" alt=""/>
									<a href="{$tsConfig.url}/@{$r.user_name}">{$r.user_name}</a>
								</span>
								<span class="meta-sep">·</span>
								<span class="meta-item meta-cat">{$r.c_nombre}</span>
							</div>
						</div>
					{foreachelse}
						<p class="empty">No se encontraron posts con esa etiqueta...</p>
					{/foreach}
				</div>
			{else}
				<p class="empty">Escribe una etiqueta para buscar posts relacionados.</p>
			{/if}

		{* USUARIOS *}
		{elseif $tsEngine == 'usuarios'}
			{if $tsResults.data}
				<div class="results-grid results-usuarios">
					{foreach from=$tsResults.data item=u}
						<a class="result-user-card flex flex-col items-center text-center py-3 px-2 rounded" href="{$tsConfig.url}/@{$u.user_name}">
							<div class="user-avatar-wrap rounded">
								{include "blocks/Avatar.tpl" alt=$u.user_name id=$u.user_id class="user-avatar" size=100}
							</div>
							<div class="user-info">
								<strong class="user-name">{$u.user_name}</strong>
								<span class="user-realname">{if $u.p_nombre}{$u.p_nombre}{else}&nbsp;{/if}</span>
								<div class="user-stats">
									<span title="Puntos">⭐ {$u.user_puntos}</span>
									<span title="Posts">📰 {$u.user_posts}</span>
									<span title="Seguidores">👥 {$u.user_seguidores}</span>
								</div>
							</div>
						</a>
					{foreachelse}
						<p class="empty">No se encontraron usuarios con ese nombre...</p>
					{/foreach}
				</div>
			{else}
				<p class="empty">Escribe un nombre de usuario para buscar.</p>
			{/if}

		{* FOTOS *}
		{elseif $tsEngine == 'fotos'}
			{if $tsResults.data}
				<div class="results-grid results-fotos">
					{foreach from=$tsResults.data item=f}
						<a class="result-foto-card" href="{$tsConfig.url}/fotos/{$f.foto_id}/">
							<div class="foto-thumb-wrap">
								<img class="foto-thumb"
									src="{$tsRoutes['assets:fotos']}/{$f.f_url}"
									alt="{$f.f_title}"
									onerror="this.parentNode.classList.add('no-img')"/>
							</div>
							<div class="foto-info">
								<span class="foto-title">{$f.f_title}</span>
								{if $f.f_description}
									<span class="foto-desc">{$f.f_description|truncate:80:'...'}</span>
								{/if}
								<div class="foto-meta">
									<span>👁 {$f.f_visitas}</span>
									<span>·</span>
									<span><a href="{$tsConfig.url}/@{$f.user_name}">{$f.user_name}</a></span>
								</div>
							</div>
						</a>
					{foreachelse}
						<p class="empty">No se encontraron fotos con ese término...</p>
					{/foreach}
				</div>
			{else}
				<p class="empty">Escribe algo para buscar fotos.</p>
			{/if}

		{* MURO *}
		{elseif $tsEngine == 'muro'}
			{if $tsResults.data}
				<div class="results-list results-muro">
					{foreach from=$tsResults.data item=m}
						<div class="result-item result-muro-item">
							<div class="muro-header">
								<a class="muro-author" href="{$tsConfig.url}/@{$m.user_name}">
									<strong>{$m.user_name}</strong>
								</a>
								{if $m.pub_en_user && $m.pub_en_user != $m.user_name}
									<span class="muro-en"> publicó en el muro de <a href="{$tsConfig.url}/@{$m.pub_en_user}">{$m.pub_en_user}</a></span>
								{/if}
								<span class="muro-date">{$m.p_date|hace:true}</span>
							</div>
							<p class="muro-body">{$m.p_body|truncate:200:'...'|nl2br}</p>
							<div class="muro-meta">
								<span>❤ {$m.p_likes}</span>
								<span>·</span>
								<span>💬 {$m.p_comments}</span>
								<span>·</span>
								<a href="{$tsConfig.url}/@{$m.user_name}/{$m.pub_id}">Ver publicación</a>
							</div>
						</div>
					{foreachelse}
						<p class="empty">No se encontraron publicaciones en el muro...</p>
					{/foreach}
				</div>
			{else}
				<p class="empty">Escribe algo para buscar en el muro.</p>
			{/if}

		{/if}

	</div>{* /showResult *}

	{* Paginación (común) *}
	{if $tsResults.data}
		<div class="paginadorCom">
			{if $tsResults.pages.prev != 0}
				<div class="pag-prev">
					<a href="{$tsConfig.url}/buscador/?page={$tsResults.pages.prev}{if $tsQuery}&query={$tsQuery}{/if}&engine={$tsEngine}{if $tsCategory}&category={$tsCategory}{/if}{if $tsAutor}&autor={$tsAutor}{/if}">&laquo; Anterior</a>
				</div>
			{/if}
			{if $tsResults.pages.next != 0}
				<div class="pag-next">
					<a href="{$tsConfig.url}/buscador/?page={$tsResults.pages.next}{if $tsQuery}&query={$tsQuery}{/if}&engine={$tsEngine}{if $tsCategory}&category={$tsCategory}{/if}{if $tsAutor}&autor={$tsAutor}{/if}">Siguiente &raquo;</a>
				</div>
			{/if}
		</div>
	{/if}

</div>
