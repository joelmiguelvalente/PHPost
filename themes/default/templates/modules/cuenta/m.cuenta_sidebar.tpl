<div class="sidebar-tabs">
   <h3>Mi Avatar</h3>
   <div class="avatar-big-cont">
      <div style="display:none;" class="avatar-loading">
         <img src="{$tsRoutes.tema.images}/large-loading.gif" alt="Cargando avatar">
      </div>
      <img alt="Avatar usuario" src="{$tsUser->avatar}?t={$smarty.now}" class="avatar-big" id="avatar-img"/>
   </div>
   <div class="change-avatar" id="blockUpload">
      <div data-file="file" class="file">Local</div>
      <div data-file="url" class="file">Url</div>
   </div>
</div>

<template id="file-local">
   <div id="drop-region">
      <input type="file" name="local" id="file-avatar" class="browse-file"/>
      <div class="drop-message">
         <p>Arrastra y suelta la imagen o haz clic para subir</p>
      </div>
   </div>
   <button class="avatar-upload btn btn-primary">Subir imagen</button>
</template>

<template id="url-local">
   <input type="url" name="url" autocomplete="off" id="url-avatar" placeholder="Url de la imagen" class="browse-url form-control"/>
   <button class="avatar-upload btn btn-primary">Subir imagen</button>
</template>