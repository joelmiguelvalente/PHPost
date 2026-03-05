{include "main_header.tpl"}
{if $tsUser->is_admod || $tsUser->permiso('global.posts.publicar')}
   <form action="{$tsConfig.url}/{if $tsAction == 'editar'}editar?id={$tsPid}{else}agregar{/if}" method="post" name="newpost" id="newpost" autocomplete="off">
      {include "agregar/agregar_formulario.tpl"}
      {include "agregar/agregar_sidebar.tpl"}
      <div class="end-form">
         <input type="button" name="draft" class="btn btn-secondary" value="Guardar en borradores">
         <input type="button" name="preview" class="btn btn-primary" value="Vista Previa »">
         <input type="button" name="publish" class="btn btn-success" value="Publicación directa">
         <div id="borrador-guardado" class="borrador-status"></div>
      </div>
   </form>
{else}
   <div class="alert-empty">Lo sentimos, pero no puedes publicar un nuevo post.</div>
{/if}

{include "main_footer.tpl"}