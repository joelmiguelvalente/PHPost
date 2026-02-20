{if $total > 15}
	{assign "color" "red"}
{elseif $total > 5}
	{assign "color" "purple"}
{else}
	{assign "color" "green"}
{/if}
<span class="inline-flex items-center px-3 py-0 rounded-full text-sm font-medium bg-{$color}-100 text-{$color}-800">{$total|default:0}</span>