{assign var=reply value=$reply|default:false}
<li class="comment-item{if $tsPost.autor == $c.c_user} author{elseif $c.c_user == $tsUser->uid} owner{/if}" data-comment-id="{$c.cid}">
	<a class="comment-avatar" aria-label="{$c.user_name} " href="{$tsConfig.url}/@{$c.user_name}">
		{include "blocks/Avatar.tpl" id=$c.user_id size=50 alt="Ver perfil" lazy=true placeholder=false}
	</a>
	<div class="comment-body">
		<div class="comment-bubble">
			<div class="comment-meta">
				<a href="{$tsConfig.url}/perfil/{$c.user_name}" class="comment-author">{$c.user_name}</a>
				<span class="comment-time">{$c.c_date|hace}</span>
			</div>
			<p class="comment-text" data-bbcode="{$c.c_html}">{$c.c_body|raw}</p>
		</div>
		{include "comentario/comentario-acciones.tpl"}
		{include "comentario/comentario-respuesta.tpl"}
		{if !$reply && $c.replies|count >= 1}
		   <button class="replies-toggle" onclick="comentario.verRespuestas('replies-{$c.cid}', this)" aria-expanded="false" data-total="{$c.replies|count}">
		      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg> {$c.replies|count} respuesta{if $c.replies|count > 1}s{/if}
		   </button>
		{/if}

		<ul class="replies-container" id="replies-{$c.cid}"{if $c.replies} style="display:none;"{/if} role="list">
			{foreach from=$c.replies item=c}
				{include "comentario/comentario-item.tpl" reply=true}
			{/foreach}
		</ul>

	</div>
</li>
