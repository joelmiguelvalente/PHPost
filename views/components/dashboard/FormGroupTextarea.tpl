{*
  	Form Group
  	Params:
   	id
   	label
   	helper
   	required (bool)

   Input
   	name
   	group
   	value
*}

{assign var=group value=$group|default:false}

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-2 mb-3">
  	<label for="{$id}" class="font-medium text-gray-700 dark:text-gray-300">{$label}
   {if $required}<span class="text-red-500">*</span>{/if}
   {if $helper}<p class="mt-1 text-xs text-gray-500">{$helper}</p>{/if}
  	</label>
  	<div class="md:col-span-2">
      <textarea name="{$name}" id="{$name}" rows="5" cols="50" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" placeholder="{$value}">{$value}</textarea>
  	</div>
</div>