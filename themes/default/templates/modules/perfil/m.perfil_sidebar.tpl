{if $tsInfo.p_socials != '' || $tsInfo.p_sitio != ''}
   <div class="perfil-redes flex justify-start items-center gap-3 mb-3">
      {if $tsInfo.p_sitio}
         <a class="sitio" target="_blank" href="{$tsInfo.p_sitio}" title="Mi sitio">
            <img height="30" width="30" alt="{$name}" src="{$tsRoutes.assets.images}/redes/site.svg"/>
         </a>
      {/if}
      {foreach $tsRedes key=name item=red}
         {if !empty($tsInfo.p_socials.$name)}
            <a class="sitio {$name}" target="_blank" href="https://{$name}.{if $name == 'twitch'}tv{else}com{/if}/{$tsInfo.p_socials.$name}" title="{$red}">
               <img height="30" width="30" alt="{$name}" src="{$tsRoutes.assets.images}/redes/{$name}.svg"/>
            </a>
         {/if}
      {/foreach}
   </div>
{/if}
         
<div class="box w-medallas clearfix">
   <div class="box-header clearfix">
      <span class="box_txt">Medallas</span>
      <span class="box_icon">{$tsGeneral.m_total}</span>
   </div>
   <div class="box-content">
      {if $tsGeneral.m_total}
         <div class="flex justify-start items-start gap-3" style="flex-wrap: wrap;">
            {foreach from=$tsGeneral.medallas item=m}
               <img src="{$tsRoutes.assets.images}/icons/medals/{$m.m_image}_32.png" width="32" height="32" title="{$m.m_title} - {$m.m_description}"/>
            {/foreach}
         </div>
         {if $tsGeneral.m_total >= 21}
            <span class="item py-1 px-3 block text-center" role="button" data-tab="medallas">Ver m&aacute;s &raquo;</span>
         {/if}
      {else}
         <div class="alert-empty">No tiene medallas</div>
      {/if}
   </div>
</div>
<div class="box w-seguidores clearfix">
   <div class="box-header clearfix">
      <span class="box_txt">Seguidores</span>
      <span class="box_icon">{$tsInfo.stats.user_seguidores}</span>
   </div>
   <div class="box-content">
      {if $tsGeneral.segs.data}
         <div class="flex justify-start items-start gap-3" style="flex-wrap: wrap;">
            {foreach from=$tsGeneral.segs.data item=s}
               <a href="{$tsConfig.url}/@{$s.user_name}" class="block overflow-hidden rounded-full" style="width:2rem;height:2rem;">
                  {include "blocks/Avatar.tpl" id=$s.user_id size=32 alt=$s.user_name lazy=true class="avatar"}
               </a> 
            {/foreach}
         </div>
         {if $tsGeneral.segs.total >= 21}
            <span class="item py-1 px-3 block text-center" role="button" data-tab="seguidores">Ver m&aacute;s &raquo;</span>
         {/if}
      {else}
         <div class="alert-empty">No tiene seguidores</div>
      {/if}
   </div>
</div>
<div class="box w-seguidores clearfix">
   <div class="box-header clearfix">
      <span class="box_txt">Siguiendo</span>
      <span class="box_icon">{$tsGeneral.sigd.total}</span>
   </div>
   <div class="box-content">
      {if $tsGeneral.sigd.data}
         <div class="flex justify-start items-start gap-3" style="flex-wrap: wrap;">
            {foreach from=$tsGeneral.sigd.data item=s}
               <a href="{$tsConfig.url}/@{$s.user_name}" class="block overflow-hidden rounded-full" style="width:2rem;height:2rem;">
                  {include "blocks/Avatar.tpl" id=$s.user_id size=32 alt=$s.user_name lazy=true class="avatar"}
               </a> 
            {/foreach}
         </div>
         {if $tsGeneral.sigd.total >= 21}
            <span class="item py-1 px-3 block text-center" role="button" data-tab="siguiendo">Ver m&aacute;s &raquo;</span>
         {/if}
      {else}
         <div class="alert-empty">No sigue usuarios</div>
      {/if}
   </div>
</div>
{if $tsInfo.can_hits}
   <div class="box w-seguidores clearfix">
      <div class="box-header clearfix">
         <span class="box_txt">&Uacute;ltimas visitas</span>
         <span class="box_icon">{$tsGeneral.visitas_total}</span>
      </div>
      <div class="box-content">
         {if $tsInfo.visitas}
            <div class="flex justify-start items-start gap-3" style="flex-wrap: wrap;">
               {foreach from=$tsInfo.visitas item=s}
                  <a href="{$tsConfig.url}/@{$s.user_name}" class="block overflow-hidden rounded-full" style="width:2rem;height:2rem;">
                     {include "blocks/Avatar.tpl" id=$s.user_id size=32 alt=$s.user_name lazy=true class="avatar"}
                  </a> 
               {/foreach}
            </div>
         {else}
            <div class="alert-empty">No tiene visitas</div>
         {/if}
     </div>
	</div>
{/if}
