{*
  	Avatar component
  	Params:
   src       (string) -> source image
   size      (int)    -> image size
   width     (int)    -> image width
   height    (int)    -> image height
   alt       (string) -> alt text
   lazy      (bool)   -> lazy loading (default true)
   class     (string) -> extra css classes
   style     (string) -> extra css classes
*}

{assign var=size value=$size|default:160}
{assign var=alt value=$alt|default:'Avatar del usuario'}
{assign var=lazyLoad value=$lazy|default:true}

<picture style="max-width:200px;">
  	<img 
   src="{$tsRoutes.assets.images}/phpost/main-512.png" 
   data-src="{$src}" 
   alt="{$alt|escape:'html'}" 
   {if $width || $size} width="{if $width}{$width}{else}{$size}{/if}"{/if} 
   {if $height || $size} height="{if $height}{$height}{else}{$size}{/if}"{/if} 
   loading="{if $lazyLoad}lazy{else}eager{/if}" 
   decoding="async" 
   class="{$class|default:''|escape:'html'}"{if $style} style="{$style}"{/if}>
</picture>
