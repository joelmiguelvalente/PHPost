{*
	Checkbox / Radio
*}

<label class="flex items-center gap-2 text-sm">
	<input type="{$type}" name="{$name}" value="{$value}"{if $checked == $value} checked{/if} class="text-primary focus:ring-primary"/> {$label}
</label>