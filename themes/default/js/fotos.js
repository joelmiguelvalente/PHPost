'use strict';

function showError(fieldName, msg) {
    const $group = $(`.form-group[data-field="${fieldName}"]`);
    $group.addClass('error').find('.form-helper').html(msg).prop('hidden', false);
    $('html, body').animate({ scrollTop: $group.offset().top - 20 }, 400);
}

function hideError(fieldName) {
    $(`.form-group[data-field="${fieldName}"]`).removeClass('error').find('.form-helper').html('').prop('hidden', true);
}

function countUpperCasePercent(str) {
    const len = str.length;
    if (!len) return 0;
    const noUpper   = str.replace(/[A-Z]/g, '').length;
    const noLetters = str.replace(/[a-zA-Z]/g, '').length;
    if (len === noLetters) return 0;
    return (len - noUpper) / (len - noLetters) * 100;
}

function getField(name) {
    return $(`#foto_form [name="${name}"]`);
}

const MAX_DESC             = 500;
const MAX_TITULO_UPPERCASE = 90;
const MIN_TITULO_LEN       = 5;

function validarUrl(url) {
    const regex = /^(ht|f)tps?:\/\/\w+([\.\-\w]+)?\.([a-z]{2,3}|info|mobi|aero|asia|name)(:\d{2,5})?(\/.*)?$/i;
    const ext   = url.slice(-3).toLowerCase();
    if (!regex.test(url)) {
        showError('f_url', 'No es una dirección válida.');
        return false;
    }
    if (!['gif', 'png', 'jpg', 'jpeg', 'webp', 'avif'].includes(ext)) {
        showError('f_url', 'Sólo se permiten imágenes .gif, .png, .jpg, .jpeg, .webp y .avif');
        return false;
    }
    return true;
}

const fotos = {

    agregar() {
        let ok = true;

        // --- Título ---
        const $titulo = getField('f_title');
        if ($titulo.length) {
            const val = $titulo.val().trim();
            if (!val) {
                showError('f_title', 'El título es obligatorio.');
                ok = false;
            } else if (val.length >= MIN_TITULO_LEN && countUpperCasePercent(val) > MAX_TITULO_UPPERCASE) {
                showError('f_title', 'El título no debe estar en mayúsculas.');
                ok = false;
            } else {
                hideError('f_title');
            }
        }

        // --- URL (sólo cuando el campo existe, sin upload) ---
        const $url = getField('f_url');
        if ($url.length) {
            const val = $url.val().trim();
            if (!val) {
                showError('f_url', 'La URL es obligatoria.');
                ok = false;
            } else if (!validarUrl(val)) {
                ok = false;
            } else {
                hideError('f_url');
            }
        }

        // --- Descripción ---
        const $desc = getField('f_description');
        if ($desc.length && $desc.val().length > MAX_DESC) {
            showError('f_description', `La descripción no debe exceder los ${MAX_DESC} caracteres.`);
            ok = false;
        } else {
            hideError('f_description');
        }

        if (!ok) return false;

        // --- Enviar ---
        $('.fade_out').fadeOut('slow', () => $('.loader').fadeIn());
        $('form[name=add_foto]').submit();
    },

    comentar() {
        const $btn      = $('#btnComment').prop('disabled', true);
        const $textarea = $('#mensaje');
        const text      = $textarea.val().trim();

        if (!text || text === $textarea.attr('title')) {
            $textarea.trigger('focus');
            $btn.prop('disabled', false);
            return;
        }
        if (text.length > 1000) {
            alert('Tu comentario no puede ser mayor a 1000 caracteres.');
            $textarea.trigger('focus');
            $btn.prop('disabled', false);
            return;
        }

        const auser = $('input[name=auser_post]').val() ?? '';
        $('#loading').fadeIn(250);

        $.ajax({
            type: 'POST',
            url:  `${route.url}/comentario-agregar?ts=true&do=fotos`,
            data: `comentario=${encodeURIComponent(text)}&fotoid=${queryParam('fotoid')}&auser=${auser}`,
            success(h) {
                const code = h.charAt(0);
                const body = h.substring(3);
                if (code === '0') {
                    $('.form .error').html(body).show('slow');
                    $btn.prop('disabled', false);
                } else if (code === '1') {
                    $('#no-comments').hide();
                    $('#mensajes').append(body);
                    $('.form').html('<div class="alert-empty">Tu comentario fue agregado correctamente :)</div>');
                    $('#ncomments').text(parseInt($('#ncomments').text(), 10) + 1);
                    $('.noComments').remove();
                    $btn.prop('disabled', false);
                }
                $('#loading').fadeOut(250);
            }
        });
    },

    votar(voto) {
        voto = (voto === 'pos') ? 'pos' : 'neg';
        const $el   = $(`#votos_total_${voto}`);
        let   total = parseInt($el.text(), 10);
        if (isNaN(total)) total = 0;

        $('#loading').fadeIn(250);

        $.ajax({
            type: 'POST',
            url:  `${route.url}/comentario-votar?do=fotos`,
            data: `voto=${voto}&fotoid=${queryParam('fotoid')}`,
            success(h) {
                if (h.charAt(0) === '0') {
                    mydialog.alert('Votar Foto', h.substring(3));
                } else if (h.charAt(0) === '1') {
                    $('#actions').html(h.substring(3)).fadeIn('fast');
                    $el.text(total + 1);
                }
                $('#loading').fadeOut(250);
            }
        });
    },

    borrar(id, type) {
        const txtType = (type === 'com') ? 'comentario' : 'foto';
        const txtAux  = (type === 'com') ? 'este '      : 'esta ';
        mydialog.mask_close = false;
        mydialog.show(true);
        mydialog.title(`Eliminar ${txtType}`);
        mydialog.body(`¿Seguro que quieres eliminar ${txtAux}${txtType}?`);
        mydialog.buttons(
            true, true, `Eliminar ${txtType}`, `fotos.del_${txtType}(${id})`,
            true, true, true, 'Cancelar', 'close', true, false
        );
        mydialog.center();
    },

    del_comentario(cid) {
        $('#loading').fadeIn(250);
        $.ajax({
            type: 'POST',
            url:  `${route.url}/comentario-borrar?do=fotos`,
            data: `cid=${cid}`,
            success(h) {
                if (h.charAt(0) === '0') {
                    mydialog.alert('Error:', h.substring(3));
                } else if (h.charAt(0) === '1') {
                    $('#ncomments').text(parseInt($('#ncomments').text(), 10) - 1);
                    $(`#div_cmnt_${cid}`).slideUp(1500, 'easeInOutElastic', function () {
                        $(this).remove();
                    });
                    mydialog.close();
                }
                $('#loading').fadeOut(250);
            }
        });
    },

    del_foto(fid) {
        $('#loading').fadeIn(250);
        $.ajax({
            type: 'POST',
            url:  `${route.url}/fotos/borrar`,
            data: `fid=${fid}`,
            success(h) {
                if (h.charAt(0) === '0') {
                    mydialog.alert('Error:', h.substring(3));
                } else if (h.charAt(0) === '1') {
                    mydialog.close();
                    location.href = `${route.url}/fotos/`;
                }
                $('#loading').fadeOut(250);
            }
        });
    }
};

/* ---------- DOM Ready ---------- */

$(function () {

    /* Botón submit */
    $('#foto_form .btn[name="new"], #foto_form .btn[name="edit"]').on('click', () => fotos.agregar());

    /* Contador de caracteres en descripción */
    const $desc    = $('#descripcion');
    const $counter = $('#count');
    if ($desc.length && $counter.length) {
        $counter.text(MAX_DESC);
        $desc.on('input', function () {
            const remaining = MAX_DESC - $(this).val().length;
            $counter.text(Math.max(remaining, 0));
            if (remaining < 0) {
                showError('f_description', `La descripción no debe exceder los ${MAX_DESC} caracteres.`);
            } else {
                hideError('f_description');
            }
        });
    }

    /* Limpiar error al escribir en cualquier campo */
    $('#foto_form [data-role="field"]').on('input', function () {
        const fieldName = $(this).closest('.form-group').data('field');
        if (fieldName && $(this).val().trim()) hideError(fieldName);
    });

    /* Validación en vivo del título (mayúsculas) */
    $('#titulo').on('input', function () {
        const val = $(this).val();
        if (val.length >= MIN_TITULO_LEN && countUpperCasePercent(val) > MAX_TITULO_UPPERCASE) {
            showError('f_title', 'El título no debe estar en mayúsculas.');
        } else {
            hideError('f_title');
        }
    });

    /* Ajuste visual de herramientas sobre la imagen */
    let toolsReady = false;
    $('#imagen').on('mouseenter', function () {
        if (toolsReady) return;
        const w    = parseInt($('#imagen .img').css('width'), 10) - 6;
        const left = (568 - w) / 2;
        $(this).find('.tools').css({ width: `${w}px`, left: `${left}px` });
        toolsReady = true;
    });

});
