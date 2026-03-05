{*
  	Avatar component
  	Params:
   id          (int)    -> user id
   size        (int)    -> avatar size (default 160)
   alt         (string) -> alt text
   lazy        (bool)   -> lazy loading (default true)
   placeholder (bool)   -> placeholder
   class       (string)-> extra css classes
*}

{assign var=size value=$size|default:160}
{assign var=alt value=$alt|default:'Avatar del usuario'}
{assign var=lazyLoad value=$lazy|default:true}
{assign var=placeholder value=$placeholder|default:true}

<picture class="profile-avatar bg-gray-100 {$class|default:''|escape:'html'}">
  	<source 
   type="image/avif" 
   {if $placeholder}srcset="{$tsRoutes.assets.images}/phpost/main-512.avif" 
   data-{/if}srcset="{$tsRoutes.storage.avatar}/user_{$id}/{if $size < 100}thumb_{/if}avatar.avif">

  	<source 
   type="image/webp" 
   {if $placeholder}srcset="{$tsRoutes.assets.images}/phpost/main-512.webp" 
   data-{/if}srcset="{$tsRoutes.storage.avatar}/user_{$id}/{if $size < 100}thumb_{/if}avatar.webp">

  	<img 
   {if $placeholder}src="{$tsRoutes.assets.images}/phpost/main-512.png" 
   data-{/if}src="{$tsRoutes.storage.avatar}/user_{$id}/{if $size < 100}thumb_{/if}avatar.png" 
   alt="{$alt|escape:'html'}" 
   width="{$size}" height="{$size}" loading="{if $lazyLoad}lazy{else}eager{/if}" decoding="async" class="{$class|default:''|escape:'html'}">
</picture>
