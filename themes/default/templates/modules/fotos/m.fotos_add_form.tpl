<div>
{if ($tsAction == 'agregar' &&
($tsUser->permiso('global.fotos.publicar') || $tsUser->is_admod)) ||
($tsAction == 'editar' && ($tsUser->permiso('moderacion.fotos.editar') ||
$tsUser->is_admod))}
    <div class="box">
        <div class="box-header">
            <span class="box_txt">{if $tsAction == 'agregar'}Agregar nueva{else}Editar{/if} foto</span>
        </div>
        <div class="box-content" style="padding:0">
            <form name="add_foto" method="POST" enctype="multipart/form-data" id="foto_form" class="form-add-post relative" autocomplete="off">

                <div class="loader absolute">
                    <div class="flex justify-center items-center gap-3 flex-col w-full h-full">
                        <img src="{$tsRoutes['tema:images']}/loading_bar.gif" />
                        <h2>Cargando foto, espere por favor....</h2>
                    </div>
                </div>

                <div class="form-group" data-field="title">
                    <label class="form-label" for="titulo">Título</label>
                    <input type="text" id="titulo" name="title" class="form-control required" value="{$tsFoto.f_title}" data-role="field" required>
                    <small class="form-helper" hidden></small>
                </div>

                {if $tsAction != 'editar'}
                    {if $tsConfig.c_allow_upload == 1}
                        <div class="form-group" data-field="file">
                            <label class="form-label" for="archivo">Archivo</label>
                            <input type="file" name="file" id="archivo" />
                            <small class="form-helper" hidden></small>
                        </div>
                    {else}
                        <div class="form-group" data-field="url">
                            <label class="form-label" for="url">URL</label>
                            <input type="text" id="url" name="url" class="form-control required" value="{$tsFoto.f_url}" data-role="field">
                            <small class="form-helper" hidden></small>
                        </div>
                    {/if}
                {/if}

                <div class="form-group" data-field="description">
                    <label class="form-label" for="descripcion">Descripci&oacute;n (<small>Max <span id="count">500</span> car.</small>)</label>
                    <textarea id="descripcion" name="description" class="form-control" data-role="field" rows="5">{$tsFoto.f_description}</textarea>
                    <small class="form-helper" hidden></small>
                </div>

                <div class="form-group" data-field="opciones">
                    <h4>Opciones</h4>
                    <label class="option">
                        <input type="checkbox" id="sin_comentarios" name="closed"{if $tsFoto.f_closed} checked{/if}>
                        <span>Cerrar Comentarios</span>
                        <small class="block">Si no quieres recibir comentarios en tu foto.</small>
                    </label>

                    <label class="option">
                        <input type="checkbox" name="visitas"{if $tsFoto.f_visitas} checked{/if}>
                        <span>&Uacute;ltimos visitantes</span>
                        <small class="block">Se mostrar&aacute;n los &uacute;ltimos visitantes.</small>
                    </label>
                </div>

                {if $tsUser->is_admod > 0 && $tsAction == 'editar' && $tsFoto.f_user  != $tsUser->uid}
                    <div class="form-group" data-field="razon">
                        <label class="form-label" for="razon">Razón</label>
                        <input type="text" id="razon" name="razon" maxlength="150" class="form-control" data-role="field">
                        <small class="form-helper">Si has modificado el contenido de esta foto, ingresa la raz&oacute;n.</small>
                    </div>
                {/if}

                <div class="end-form p-2">
                    <!-- onclick="fotos.agregar()" -->
                    <input type="button" name="{if $tsAction == 'agregar'}new{else}edit{/if}" class="btn btn-secondary" value="{if $tsAction == 'agregar'}Agregar foto{else}Guardar cambios{/if}">
                </div>
            </form>
        </div>
    </div>
{else}
    <div class="alert-empty clearfix">Lo sentimos, pero no puedes {if $tsAction == 'agregar'}agregar{else}editar{/if} una nueva foto.</div>
{/if}
</div>
