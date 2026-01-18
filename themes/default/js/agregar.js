function countUpperCase(str) {
	let upper = 0;
	let letters = 0;
	for (const char of str) {
		if (char >= 'A' && char <= 'Z') {
			upper++;
			letters++;
		} else if (char >= 'a' && char <= 'z') {
			letters++;
		}
	}
	if (letters === 0) {
		return 0;
	}
	return (upper / letters) * 100;
}

function getFieldContainer(el) {
	return $(el).closest('li');
}

function setError(el, message) {
	const $li = getFieldContainer(el);
	const hasError = Boolean(message);
	$li.toggleClass('error', hasError);
	$li.find('.errormsg').html(message || '').toggle(hasError);
}

function validateRequired() {
	let valid = true;
	$('.required').each(function () {
		if (!$.trim(this.value)) {
			setError(this, 'Este campo es obligatorio');
			valid = false;
			return false;
		}
	});
	return valid;
}

function validateTitle() {
	const input = $('input[name="titulo"]').get(0);
	const value = input.value;
	if (value.length >= 5 && countUpperCase(value) > 90) {
		setError(input, 'El título no debe estar en mayúsculas');
		return false;
	}
	setError(input);
	return true;
}

function validateTags() {
	const input = $('input[name="tags"]').get(0);
	const tags = input.value.split(',').map(t => t.trim()).filter(Boolean);
	if (tags.length < 4) {
		setError(input, 'Tienes que ingresar por lo menos 4 tags separados por coma.');
		return false;
	}
	setError(input);
	return true;
}

function validateBodyLength() {
	const $textarea = $('textarea[name="cuerpo"]'); // elemento real
	const content   = $textarea.bbcode();            // string BBCode

	if (content.length > 65000) {
		setError($textarea, 'El post es demasiado largo. No debe exceder los 65000 caracteres.');
		return false;
	}
	setError($textarea);
	return true;
}

function buildBorradorParams() {
	return $.param({
		titulo: $('input[name="titulo"]').val(),
		cuerpo: $('textarea[name="cuerpo"]').bbcode(),
		tags: $('input[name="tags"]').val(),
		categoria: $('select[name="categoria"]').val(),
		privado: $('input[name="privado"]').is(':checked') ? 1 : undefined,
		sin_comentarios: $('input[name="sin_comentarios"]').is(':checked') ? 1 : undefined,
		patrocinado: $('input[name="patrocinado"]').is(':checked') ? 1 : undefined,
		sticky: $('input[name="sticky"]').is(':checked') ? 1 : undefined
	});
}

let borradorTimeout = null;
let borradorUltGuardado = '';
let borradorEnabled = true;

function enableBorradorSave() {
	$('#borrador-save').prop('disabled', false).removeClass('disabled');
	borradorEnabled = true;
}

function disableBorradorSave() {
	$('#borrador-save').prop('disabled', true).addClass('disabled');
	borradorEnabled = false;
}

function resetBorradorTimeout(ms) {
	clearTimeout(borradorTimeout);
	borradorTimeout = setTimeout(enableBorradorSave, ms);
}

function save_borrador() {
	if (!borradorEnabled) return;
	const borradorId = $('input[name="borrador_id"]').val();
	const url = borradorId ? '/borradores-guardar.php' : '/borradores-agregar.php';
	let data = buildBorradorParams();
	if (borradorId) {
		data += '&borrador_id=' + encodeURIComponent(borradorId);
	}
	$('#borrador-guardado').text('Guardando...');
	disableBorradorSave();
	resetBorradorTimeout(60000);
	$.post(route.url + url, data).done(handleBorradorResponse).fail(() => mydialog.error_500('save_borrador()'));
}

function handleBorradorResponse(response) {
	const status = response.charAt(0);
	const payload = response.substring(3);

	if (status === '0') {
		borradorUltGuardado = payload;
		resetBorradorTimeout(5000);
	} else {
		if (!$('input[name="borrador_id"]').val()) {
			$('input[name="borrador_id"]').val(payload);
		}
		borradorUltGuardado = `Guardado a las ${new Date().toLocaleTimeString()} hs.`;
	}

	$('#borrador-guardado').text(borradorUltGuardado);
}

let confirmLeave = true;
let tagsGenerated = false;

window.onbeforeunload = function () {
	if (confirmLeave && ($('input[name="titulo"]').val() || $('textarea[name="cuerpo"]').bbcode())) {
		return 'Este post no fue publicado y se perderá.';
	}
};

function postSave() {
	confirmLeave = false;
	$('form[name="newpost"]').submit();
}

$(function () {

	$('.required').on('keyup change', function () {
		if ($.trim(this.value)) {
			setError(this);
		}
	});

	$('input[name="titulo"]').on('keyup', validateTitle);

	$('input[name="titulo"]').on('blur', function () {
		$.post(route.url + '/posts-genbus.php?do=search', { q: this.value })
			.done(h => $('#repost').html(h));
	});

	$('input[name="tags"]').on('click', function () {
		if (tagsGenerated) return;

		$.post(route.url + '/posts-genbus.php?do=generador', {
			q: $('input[name="titulo"]').val()
		}).done(h => {
			$(this).val(h);
			tagsGenerated = true;
		});
	});

	$('input[name="preview"]').on('click', function () {

		if (
			!validateRequired() ||
			!validateTitle() ||
			!validateBodyLength() ||
			!validateTags()
		) {
			return false;
		}
		dialog.alert('Vista previa', `Cargando vista previa...<br><br><img src="${route.img}/loading_bar.gif">`);

		$.post(route.url + '/posts-preview.php?ts=true', {
			cuerpo: $('textarea[name="cuerpo"]').bbcode()
		}).done(response => {
			console.log(response)
			dialog.init({
		      title: $('input[name="titulo"]').val(),
		      body: response,
		      buttons: {
		         confirm: {
		            text: 'Publicar post',
		            action: () => postSave()
		         },
		         cancel: {
		            text: 'Cerrar previsualización',
		            action: 'close'
		         }
		      }
		   });
		});
	});

	$('a.consejos-view-more-button').on('click', function () {
		$(this).hide();
		$('div.consejos-view-more').show();
	});
	//Editor de posts
  	$('textarea[name=cuerpo]').removeAttr('onblur onfocus class style').css('height', '400').addClass('required').wysibb();
   
})