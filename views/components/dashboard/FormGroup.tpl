{*
  	Form Group
  	Params:
   	id
   	label
   	helper
      type
   	required (bool)

   Input
   	name
   	group
      type
   	value
*}

{assign var=group value=$group|default:false}

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-2 mb-3">
  	<label{if $type != 'radio'} for="{$id}"{/if} class="font-medium text-gray-700 dark:text-gray-300">{$label}
   {if $required}<span class="text-red-500">*</span>{/if}
   {if $helper}<p class="mt-1 text-xs text-gray-500">{$helper}</p>{/if}
  	</label>
  	<div class="{if $type == 'radio'}flex gap-6{else}md:col-span-2{/if}">
  		{if $group}
    		{include file="dashboard/InputGroup.tpl" name=$id id=$id value=$value|default:'' maxlength=$maxlength|default:2 prefix=$prefix|default:'' suffix=$suffix|default:'' width=$width|default:'w-20'}
    	{elseif $type == 'radio'}
    		{foreach $values item=val key=i}
    			{include file="dashboard/Check.tpl" type="radio" name=$name value=$val checked=$checked label=$labels.$i}
    		{/foreach}
    	{else}
    		{include file="dashboard/Input.tpl" type=$type name=$id id=$id value=$value|default:'' disabled=$disabled|default:false}
  		{/if}
  	</div>
</div>