{*
	Form Actions
	Params:
		submit_text
		submit_name
		cancel_url (opcional)
*}

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
	<div class="md:col-span-1"></div>
	<div class="md:col-span-2 flex items-center gap-3">
		<button type="submit"{if $submit_name} name="{$submit_name}"{/if} class="inline-flex items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary">{$submit_text|default:'Guardar cambios'}</button>
		{if $cancel_url}
			<a href="{$cancel_url}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    	{/if}
   </div>
</div>