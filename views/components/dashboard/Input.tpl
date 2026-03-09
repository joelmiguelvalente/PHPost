{*
  	Input component
  	Params:
   	type     text|email|url|date
   	name
   	id
   	value
   	placeholder
   	disabled
*}

{assign var=type value=$type|default:'text'}

<input type="{$type}" name="{$name}" id="{$id}" value="{$value|escape}" placeholder="{$placeholder|default:''}" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface{if $type == 'color'}p-1 {else} px-3 py-2{/if} text-sm focus:outline-none focus:ring-2 focus:ring-primary"{if $disabled} disabled{/if} />