{*
  	Action component
  	Params:
   action    (string) -> Accion a realizar
   icon      (string) -> Icono a mostrar
   title     (string) -> Titulo del button
   onclick   (string) -> Accion a realia
*}

{assign var=action value=$action|default:''}
{assign var=icon value=$icon|default:''}
{assign var=title value=$title|default:''}
{assign var=type value=$type|default:'link'}

{if $type == 'link'}
	<a href="{$tsConfig.url}/admin/{$action}" class="text-gray-600 hover:text-primary" title="{$title}"><span class="material-symbols-outlined">{$icon}</span></a>
{else}
	<button type="button" onclick="{$action}" class="text-gray-600 hover:text-yellow-600" title="{$title}"><span class="material-symbols-outlined">{$icon}</span></button>
{/if}