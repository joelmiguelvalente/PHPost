{if $tsPost.user_firma && $tsConfig.c_allow_firma}
	<hr class="divider" />
	<div class="post-firma">{$tsPost.user_firma|raw}</div>
{/if}
