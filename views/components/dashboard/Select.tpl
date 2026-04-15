<select name="{$name}" id="{$id}" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary"{if !empty($onchange)} onchange="{$onchange}"{/if}>
	{foreach from=$options item=opt}
		<option value="{$opt.value}"{if !empty($selected)}{if $opt.value == $selected} selected{/if}{/if}>
			{$opt.label}
		</option>
	{/foreach}
</select>
