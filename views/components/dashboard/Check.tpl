{*
   @component Check.tpl
   @params
     type    : "checkbox" | "radio"
     name    : string
     label   : string
     checked : bool|string|int
     value   : string (opcional, requerido para radio groups)
     helper  : string (opcional)
     class   : string (opcional)
     disabled: bool   (opcional)
*}

<label class="flex items-start gap-2 text-sm cursor-pointer{if $class} {$class}{/if}{if $disabled} opacity-50 pointer-events-none{/if}">
   <input type="{$type}" name="{$name}"{if $value} value="{$value}"{/if}
   	{if $value !== null && $value !== ''}
   		{if $checked == $value} checked{/if}
   	{else}
   		{if $checked} checked{/if} 
   	{/if}{if $disabled} disabled{/if} class="mt-0.5 shrink-0 text-primary focus:ring-primary"/>
   <span class="flex flex-col">
      {$label}
      {if $helper}<span class="text-xs text-gray-500 mt-0.5">{$helper}</span>{/if}
   </span>
</label>