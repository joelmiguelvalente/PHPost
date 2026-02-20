{*
  	Avatar component
  	Params:
   id        (int)    -> user id
   size      (int)    -> avatar size (default 160)
   alt       (string) -> alt text
   lazy      (bool)   -> lazy loading (default true)
   class     (string)-> extra css classes
*}

{assign var=size value=$size|default:160}
{assign var=alt value=$alt|default:'Avatar del usuario'}
{assign var=lazyLoad value=$lazy|default:true}

<picture class="profile-avatar bg-gray-100 {$class|default:''|escape:'html'}">
  	<source 
   type="image/avif" 
   srcset="{$tsRoutes.assets.images}/phpost/main-512.avif" 
   data-srcset="{$tsRoutes.storage.avatar}/user_{$id}/{if $size < 100}thumb_{/if}avatar.avif">

  	<source 
   type="image/webp" 
   srcset="{$tsRoutes.assets.images}/phpost/main-512.webp" 
   data-srcset="{$tsRoutes.storage.avatar}/user_{$id}/{if $size < 100}thumb_{/if}avatar.webp">

  	<img 
   src="{$tsRoutes.assets.images}/phpost/main-512.png" 
   data-src="{$tsRoutes.storage.avatar}/user_{$id}/{if $size < 100}thumb_{/if}avatar.png" 
   alt="{$alt|escape:'html'}" 
   width="{$size}" height="{$size}" loading="{if $lazyLoad}lazy{else}eager{/if}" decoding="async" class="{$class|default:''|escape:'html'}">
</picture>
