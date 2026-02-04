<select name="{$name}" id="{$id}" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
	{foreach from=$options item=opt}
		<option value="{$opt.value}"{if $opt.value==$selected} selected{/if}>
			{$opt.label}
		</option>
	{/foreach}
</select>