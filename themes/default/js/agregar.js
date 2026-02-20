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

const getFieldContainer = el => $(el).closest('div.form-group');

const setError = (el, message) => {
	const $div = getFieldContainer(el);
	const hasError = Boolean(message);
	$div.toggleClass('error', hasError);
	$div.find('.form-helper').html(message || '').toggle(hasError);
}

function validateRequired() {
	let valid = true;
	$('.required').each(function (e, v) {
		if (!$(v).val().trim()) {
			setError(this, 'Este campo es obligatorio');
			valid = false;
			return false;
		}
	});
	return valid;
}

function validateTitle() {
	const input = $('input[name="title"]').get(0);
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
	const $textarea = $('textarea[name="body"]'); // elemento real
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
		title: $('input[name="title"]').val(),
		body: $('textarea[name="body"]').bbcode(),
		tags: $('input[name="tags"]').val(),
		category: $('select[name="category"]').val(),
		private: $('input[name="private"]').is(':checked') ? 1 : 0,
		block_comments: $('input[name="block_comments"]').is(':checked') ? 1 : 0,
		sponsored: $('input[name="sponsored"]').is(':checked') ? 1 : 0,
		visitantes: $('input[name="visitantes"]').is(':checked') ? 1 : 0,
		smileys: $('input[name="smileys"]').is(':checked') ? 1 : 0,
		sticky: $('input[name="sticky"]').is(':checked') ? 1 : 0
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
	$.post(route.url + url, data, handleBorradorResponse).fail(() => dialog.reintentar('save_borrador()'));
}

function handleBorradorResponse(response) {
	const { status, message } = $.parseResponse(response);

	if (status === 0) {
		borradorUltGuardado = message;
		resetBorradorTimeout(5000);
	} else {
		if (!$('input[name="borrador_id"]').val()) {
			$('input[name="borrador_id"]').val(message);
		}
		borradorUltGuardado = `Guardado a las ${new Date().toLocaleTimeString()} hs.`;
	}

	$('#borrador-guardado').text(borradorUltGuardado);
}

let confirmLeave = true;
let tagsGenerated = false;

window.onbeforeunload = function () {
	if (confirmLeave && ($('input[name="title"]').val() || $('textarea[name="body"]').bbcode())) {
		return 'Este post no fue publicado y se perderá.';
	}
};

const postSave = () => {
	confirmLeave = false;
	$('form[name="newpost"]').submit();
}

$(() => {

	$('.required').on('keyup change', function () {
		if ($(this).val().trim()) {
			setError(this);
		}
	});

	$('input[name="title"]').on('keyup', validateTitle);

	$('input[name="title"]').on('blur', () => {
		const param = { query: this.value };
		$.post(`${route.url}/posts-genbus.php?do=search`, param, response => $('#repost').html(response));
	});

	$('input[name="tags"]').on('click', function () {
		const param = { query: $('input[name="title"]').val() };
		$.post(`${route.url}/posts-genbus.php?do=generador`, param, response => {
			$('input[name="tags"]').val(response);
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

		const param = { cuerpo: $('textarea[name="body"]').bbcode() };

		$.post(`${route.url}/posts-preview.php?ts=true`, param, response => {
			dialog.easy($('input[name="title"]').val(), response, 'Publicar post', () => postSave())
		});
	});

	//Editor de posts
  	$('textarea[name=body]').css({ height: 400 }).addClass('required').wysibb();
   
});