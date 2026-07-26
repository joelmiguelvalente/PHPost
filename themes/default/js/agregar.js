'use strict';
const setData = () => {
	return {
		title:          $('input[name="title"]').val(),
		portada:        $('input[name="portada"]').val(),
		body:           $('textarea[name="body"]').bbcode(),
		tags:           $('input[name="tags"]').val(),
		category:       $('select[name="category"]').val(),
		private:        $('input[name="private"]').is(':checked'),
		block_comments: $('input[name="block_comments"]').is(':checked'),
		sponsored:      $('input[name="sponsored"]').is(':checked'),
		visitantes:     $('input[name="visitantes"]').is(':checked'),
		smileys:        $('input[name="smileys"]').is(':checked'),
		sticky:         $('input[name="sticky"]').is(':checked')
	};
};

function saveToCookie() {
	const expires = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toUTCString();
	document.cookie = `post_draft=${encodeURIComponent(JSON.stringify(setData()))}; expires=${expires}; path=/`;
}

function loadFromCookie() {
	const match = document.cookie.match(/(?:^|;\s*)post_draft=([^;]*)/);
	if (!match) return;

	let data;
	try { data = JSON.parse(decodeURIComponent(match[1])); } catch { return; }

	// Solo restaurar si hay algo útil guardado
	if (!data.title && !data.body) return;

	const restore = confirm('Se encontró un post sin publicar guardado en el navegador. ¿Querés restaurarlo?');
	if (!restore) { clearCookie(); return; }

	$('input[name="title"]').val(data.title || '');
	$('input[name="portada"]').val(data.portada || '');
	if (data.body) {
		$('textarea[name="body"]').val(data.body);
	}
	$('input[name="tags"]').val(data.tags || '');
	if (data.category) $('select[name="category"]').val(data.category);

	const checkboxes = ['private', 'block_comments', 'sponsored', 'visitantes', 'smileys', 'sticky'];
	checkboxes.forEach(name => $(`input[name="${name}"]`).prop('checked', Boolean(data[name])));

	// Reinicializar el editor wysibb con el contenido restaurado (si aplica)
	if (typeof $.fn.bbcode === 'function') {
		// Depende de la implementación del editor; ajustar si es necesario
		$('textarea[name="body"]').trigger('change');
	}
}

function clearCookie() {
	document.cookie = 'post_draft=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
}

// ─── AUTO-SAVE PERIÓDICO ──────────────────────────────────────────────────────

let cookieLastHash = '';

function getCookieHash() {
	return $('input[name="title"]').val() + '|' + $('textarea[name="body"]').val();
}

setInterval(() => {
	const hash = getCookieHash();
	if (hash !== cookieLastHash && hash !== '|') {
		cookieLastHash = hash;
		saveToCookie();
	}
}, 10000);

// ─── FUNCIONES ORIGINALES ─────────────────────────────────────────────────────

function countUpperCase(str) {
	let upper = 0;
	let letters = 0;
	for (const char of str) {
		if (char >= 'A' && char <= 'Z') { upper++; letters++; }
		else if (char >= 'a' && char <= 'z') { letters++; }
	}
	if (letters === 0) return 0;
	return (upper / letters) * 100;
}

const getFieldContainer = el => $(el).closest('div.form-group');

const setError = (el, message) => {
	const $div = getFieldContainer(el);
	const hasError = Boolean(message);
	$div.toggleClass('error', hasError);
	$div.find('.form-helper').html(message || '').toggle(hasError);
};

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
	const $textarea = $('textarea[name="body"]');
	const content   = $textarea.bbcode();
	if (content.length > 65000) {
		setError($textarea, 'El post es demasiado largo. No debe exceder los 65000 caracteres.');
		return false;
	}
	setError($textarea);
	return true;
}

function buildBorradorParams() {
	return $.param(setData());
}

let borradorTimeout  = null;
let borradorUltGuardado = '';
let borradorEnabled  = true;

function enableBorradorSave() {
	$('input[name=draft]').prop('disabled', false).removeClass('disabled');
	borradorEnabled = true;
}

function disableBorradorSave() {
	$('input[name=draft]').prop('disabled', true).addClass('disabled');
	borradorEnabled = false;
}

function resetBorradorTimeout(ms) {
	clearTimeout(borradorTimeout);
	borradorTimeout = setTimeout(enableBorradorSave, ms);
}

function saveBorrador() {
	if (!borradorEnabled) return;
	const borradorId = $('input[name="borrador_id"]').val();
	const url = borradorId ? '/borradores-guardar' : '/borradores-agregar';
	const data = Object.fromEntries(new URLSearchParams(buildBorradorParams()));
	data.status = 'borrador';
	if (borradorId) data.borrador_id = borradorId;

	$('#borrador-guardado').text('Guardando...');
	disableBorradorSave();
	resetBorradorTimeout(60000);

	api(url.slice(1), data, handleBorradorResponse, { error: () => dialog.reintentar('saveBorrador()') })
		.always(() => clearCookie());
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

let confirmLeave  = true;
let tagsGenerated = false;

window.onbeforeunload = function () {
	if (confirmLeave && ($('input[name="title"]').val() || $('textarea[name="body"]').bbcode())) {
		saveToCookie();
		return 'Este post no fue publicado y se perderá.';
	}
};

const postSave = () => {
	clearCookie();
	confirmLeave = false;
	$('form[name="newpost"]').submit();
};

$(() => {

	loadFromCookie();

	$('.required').on('keyup change', function () {
		if ($(this).val().trim()) setError(this);
	});

	$('input[name="title"]').on('keyup', validateTitle);

	$('input[name="title"]').on('blur', function () {
		const param = { query: this.value };
		api('posts-genbus?do=search', param, response => $('#repost').html(response));
	});

	$('input[name="tags"]').on('click', function () {
		const param = { query: $('input[name="title"]').val() };
		api('posts-genbus?do=generador', param, response => {
			$('input[name="tags"]').val(response);
			tagsGenerated = true;
		});
	});

	$('input[name="preview"]').on('click', function () {
		if (!validateRequired() || !validateTitle() || !validateBodyLength() || !validateTags()) {
			return false;
		}
		dialog.alert('Vista previa', `Cargando vista previa...<br><br><img src="${route.img}/loading_bar.gif">`);
		const param = { cuerpo: $('textarea[name="body"]').bbcode() };
		api('posts-preview?ts=true', param, response => {
			dialog.easy($('input[name="title"]').val(), response, 'Publicar post', () => postSave());
		});
	});

	$('input[name="publish"]').on('click', () => postSave());
	$('input[name="draft"]').on('click', () => saveBorrador());

	// Editor de posts
	$('textarea[name=body]').css({ height: 400 }).addClass('required').wysibb();

});
