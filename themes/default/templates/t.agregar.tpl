{include "main_header.tpl"}
{if $tsUser->is_admod || $tsUser->permiso('global.posts.publicar')}
   <form action="{$tsConfig.url}/{if $tsAction == 'editar'}editar?id={$tsPid}{else}agregar{/if}" method="post" name="newpost" id="newpost" autocomplete="off">
      {if $tsDraft.post_id}
         <input type="hidden" name="borrador_id" value="{$tsDraft.post_id}">
      {/if}
      <div class="col-left">
         {include "m.agregar_form.tpl"}
      </div>
      <div class="col-right">
         {include "m.agregar_sidebar.tpl"}
      </div>
      <div class="end-form">
         <input type="button" id="borrador-save" class="mBtn btnCancel" value="Guardar en borradores">
         <input type="button" name="preview" class="mBtn btnOk" value="Vista Previa »">
         <input type="button" name="publish" class="mBtn btnGreen" value="Publicación directa">
         <div id="borrador-guardado" class="borrador-status"></div>
      </div>
   </form>
{else}
   <div class="alert-empty">Lo sentimos, pero no puedes publicar un nuevo post.</div>
{/if}

{include "main_footer.tpl"}