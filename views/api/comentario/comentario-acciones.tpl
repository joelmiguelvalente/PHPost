<div class="comment-footer">
	{if $tsUser->is_member}
		{if $tsUser->uid != $c.c_user}
			{if $tsUser->permiso('global.posts.votar_positivo') || $tsUser->is_admod}
				<button class="comment-action action-like" data-cid="{$c.cid}" data-count="{$c.c_votos_pos}" onclick="comentario.votar(this,'like')">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
					<span class="action-count">{$c.c_votos_pos}</span>
				</button>
			{/if}
			{if $tsUser->permiso('global.posts.votar_negativo') || $tsUser->is_admod}
				<button class="comment-action action-dislike" data-cid="{$c.cid}" data-count="{$c.c_votos_neg}" onclick="comentario.votar(this,'dislike')">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"/></svg>
					<span class="action-count">{$c.c_votos_neg}</span>
				</button>
			{/if}
			<span class="comment-action-sep"></span>
		{/if}
		<button class="comment-action action-reply" onclick="comentario.responder('reply-form-{$c.cid}')">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
			Responder
		</button>
		<span class="comment-action-sep"></span>
		<button class="comment-action action-reply" onclick="comentario.citar({$c.cid}, '{$c.user_name}')">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/></svg>
			Mencionar
		</button>
		<div class="comment-more">
			<button class="comment-more-btn" onclick="toggleDropdown('dropdown{$c.cid}')">
				<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
			</button>
			<div class="dropdown-menu comment-actions" id="dropdown{$c.cid}">
				<button class="dropdown-item" onclick="editComment(1); closeDropdown('dropdown{$c.cid}')">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
					Editar
				</button>
				<button class="dropdown-item" onclick="copyComment(1); closeDropdown('dropdown{$c.cid}')">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
					Copiar texto
				</button>
				<div class="dropdown-divider"></div>
				<button class="dropdown-item item-danger" onclick="deleteComment(1); closeDropdown('dropdown{$c.cid}')">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
					Eliminar
				</button>
			</div>
		</div>
	{/if}
</div>