<div id="post-izquierda">
    <div class="box">
        <div class="box-header">
            <span class="box_txt">&Uacute;ltimos comentarios</span>
        </div>
        <div class="box-content" style="padding:0">
            <ul>
                {foreach from=$tsLastComments item=c}
                    <li><strong>{if $tsUser->is_admod && $tsConfig.c_see_mod == 1 && $tsFoto.f_status != 0 || $tsFoto.user_activo == 0}<span style="color: {if $c.user_activo == 0} brown {elseif $c.f_status == 1} purple {elseif $c.f_status == 2} red{/if};" class="qtip" title="{if $c.user_activo == 0}El autor del comentario tiene la cuenta desactivada {elseif $c.f_status == 1} La foto se encuentra oculta {elseif $c.f_status == 2} La foto se encuentra eliminada{/if}">{/if}{$tsUser->getUsername($c.c_user)}{if $c.user_activo == 0 || $c.f_status != 0 && $tsUser->is_admod}</span>{/if}</strong> &raquo; <a href="{$tsConfig.url}/fotos/{$c.user_name}/{$c.foto_id}/{$c.f_title|seo}.html#div_cmnt_{$c.cid}">{$c.f_title}</a></li>
                {foreachelse}
                    <li><div class="alert-empty">No hay comentarios</div></li>
                {/foreach}
           </ul>
        </div>
    </div>
    <div class="box">
        <div class="box-header">
            <span class="box_txt">Estad&iacute;sticas</span>
        </div>
        <div class="box-content" style="padding:0">
            <ul>
                <li class="flex justify-between items-center py-2 px-3">
                    <span>Miembros</span>
                    <strong>{$tsStats.stats_miembros}</strong>
                </li>
                <li class="flex justify-between items-center py-2 px-3">
                    <span>Fotos</span>
                    <strong>{$tsStats.stats_fotos}</strong>
                </li>
                <li class="flex justify-between items-center py-2 px-3">
                    <span>Comentarios</span>
                    <strong>{$tsStats.stats_foto_comments}</strong>
                </li>
             </ul>
        </div>
    </div>
</div>
