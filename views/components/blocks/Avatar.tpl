{*
    Avatar component
    Params:
      id          (int)    -> user id
      size        (int)    -> avatar size (default 160)
      alt         (string) -> alt text
      lazy        (bool)   -> lazy loading (default true)
      placeholder (bool)   -> placeholder
      class       (string) -> extra css classes
*}

{assign var=size value=$size|default:160}
{assign var=alt value=$alt|default:'Avatar del usuario'}
{assign var=lazyLoad value=$lazy|default:true}
{assign var=placeholder value=$placeholder|default:true}

{assign var=prefix value=''}
{if $size < 100}
  {assign var=prefix value='thumb_'}
{/if}

{assign var=fallbackAvif value="{$tsRoutes.assets.images}/phpost/main-512.avif"}
{assign var=fallbackWebp  value="{$tsRoutes.assets.images}/phpost/main-512.webp"}
{assign var=fallbackPng   value="{$tsRoutes.assets.images}/phpost/main-512.png"}

<picture class="profile-avatar bg-gray-100 {$class|default:''|escape:'html'}">
   <source type="image/avif" {if $placeholder}srcset="{$fallbackAvif}" data-{/if}srcset="{$tsRoutes.storage.avatar}/user_{$id}/{$prefix}avatar.avif">
   <source type="image/webp" {if $placeholder}srcset="{$fallbackWebp}" data-{/if}srcset="{$tsRoutes.storage.avatar}/user_{$id}/{$prefix}avatar.webp">
   <img {if $placeholder}src="{$fallbackPng}" data-{/if}src="{$tsRoutes.storage.avatar}/user_{$id}/{$prefix}avatar.png" alt="{$alt|escape:'html'}" width="{$size}" height="{$size}" loading="{if $lazyLoad}lazy{else}eager{/if}" decoding="async" class="{$class|default:''|escape:'html'}" data-fallback-avif="{$fallbackAvif}" data-fallback-webp="{$fallbackWebp}" data-fallback-png="{$fallbackPng}">
</picture>