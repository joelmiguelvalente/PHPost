{*
  	Alert component
  	Params:
   color     (string) -> green | red | yellow | secondary
   text      (string) -> texto a mostrar
   show      (bool)   -> true | false
*}

{assign var=color value=$color|default:'secondary'}
{assign var=text value=$text|default:'Debe colocar un texto en el componente'}
{assign var=show value=$show}

{if $show}
	<div class="flex gap-3 rounded-md border border-{$color}-200 bg-{$color}-50 text-{$color}-800 p-4 text-sm align-center dark:border-{$color}-900 dark:bg-{$color}-950 dark:text-{$color}-300 mb-4">{$text}</div>
{/if}