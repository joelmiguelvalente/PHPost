{*
    Form Group
    Params:
    id
    label
    helper

   Text
*}

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-2 mb-3">
    <label for="{$id}" class="font-medium text-gray-700 dark:text-gray-300">{$label}
    {if $helper}<p class="mt-1 text-xs text-gray-500">{$helper}</p>{/if}
    </label>
    <div class="md:col-span-2">
        <strong{if !empty($style)} style="{$style}"{/if}>{$text}</strong>
    </div>
</div>
