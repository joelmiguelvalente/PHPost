<tfoot class="bg-gray-50 dark:bg-surface-alt">
	{if $pages}
		<tr>
			<td colspan="{$span|default:2}" class="px-3 py-3 text-center">
				<div class="mb-3">{$pages}</div>
			</td>
		</tr>
	{/if}
	{if $link}
		<tr>
			<td colspan="{$span|default:2}" class="px-3 py-3 text-right">
				{if $type == 'button'}
					<button type="button" onclick="{$link}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90" title="{$title}"><span class="material-symbols-outlined text-sm">{$icon|default:'add'}</span> {$text|default:'Texto del boton'}</button>
				{else}
					<a href="{$tsConfig.url}/admin/{$link|default:''}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm">{$icon|default:'add'}</span> {$text|default:'Texto del boton'}</a>
				{/if}
			</td>
		</tr>
	{/if}
</tfoot>