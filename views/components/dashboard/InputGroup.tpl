{*
	Input Group component
	Params:
		id
		name
		label
		value
		type        (text|number)
		prefix      (string)
		suffix      (string)
		maxlength
		width       (tailwind width, ej: w-24)
*}

{assign var=type value=$type|default:'text'}
{assign var=width value=$width|default:'w-24'}


<div class="flex items-center">
	{if $prefix}
      <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-3 py-2 text-sm text-gray-600">{$prefix}</span>
   {/if}
	<input type="{$type}" name="{$name}" id="{$id}" value="{$value|escape}" maxlength="{$maxlength|default:''}" class="{$width} rounded-none{if !$prefix} rounded-l-md{/if}{if !$suffix} rounded-r-md{/if} border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"/>
	{if $suffix}
	   <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-3 py-2 text-sm text-gray-600">{$suffix}</span>
	{/if}
</div>