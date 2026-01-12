'use strict';

/* =========================
 * Selectores
 * ========================= */
const $form        = $('#LoginForm');
const $btnLogin    = $('#btn-login');
const $passwordInp = $('input[type="password"]');
const $togglePass  = $('.seePassword');
const $messageBox  = $('.display-message');

/* =========================
 * Utilidades
 * ========================= */
const getValue = id => {
	const $el = $(`#${id}`);
	const value = $el.val().trim();

	if (!value) {
		$el.focus();
		return null;
	}
	return value;
};

const resetUI = () => {
	showLoader(false);
	setButtonLoading(false);
	$messageBox.html('').hide();
};

/* =========================
 * UI helpers
 * ========================= */
function showLoader(show = true) {
	const $loader = $('#loader');

	if (show && !$loader.length) {
		$form.append(
			`<div id="loader" class="fixed flex justify-center items-center bg-gray-50/50" style="inset:0;">
				<img src="${route.img}/large-loading.gif" width="32" height="32" alt="Iniciando sesión">
			</div>`
		);
	}

	if (!show) {
		$loader.remove();
	}
}

function setButtonLoading(loading = false) {
	$btnLogin
		.prop('disabled', loading)
		.html(loading ? 'Iniciando sesión...' : 'Ingresar');
}

/* =========================
 * Login
 * ========================= */
function iniciarSesion() {

	const username = getValue('username');
	const password = getValue('password');

	if (!username || !password) {
		return;
	}

	const params = {
		username,
		password,
		remember: $('#remember').is(':checked')
	};

	showLoader(true);
	setButtonLoading(true);

	$.post(`${route.url}/login-user.php`, $.param(params))
		.done(response => {
			const { status, message } = $.parseResponse(response);

			if (status === 1) {
				setTimeout(() => location.reload(), 1500);
				return;
			}

			$messageBox.html(message).show();
			resetUI();
		})
		.fail(() => {
			$messageBox.html('Error al procesar la petición').show();
			resetUI();
		});
}

/* =========================
 * Password toggle
 * ========================= */
function togglePasswordVisibility() {
	$togglePass.on('click', () => {
		const isText = $passwordInp.attr('type') === 'text';
		$passwordInp.attr('type', isText ? 'password' : 'text');
	});
}

/* =========================
 * Init
 * ========================= */
$(() => {

	$form.on('keyup focusout', 'input', resetUI);

	$form.on('submit', e => {
		e.preventDefault();
		iniciarSesion();
	});

	togglePasswordVisibility();
});
