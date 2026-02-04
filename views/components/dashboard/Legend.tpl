{*
  	Legend component
  	Params:
   text      (string) -> texto a mostrar
*}

{assign var=text value=$text|default:'Legend: ingrese un texto...'}

<legend class="px-2 text-lg font-semibold text-gray-800 dark:text-gray-100">
  {$text}
</legend>