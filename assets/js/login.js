'use strict';

const $form        = $('#LoginForm');
const $btnLogin    = $('#btn-login');
const $passwordInp = $('input[type="password"]');
const $togglePass  = $('.seePassword');

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
};

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
	$btnLogin.prop('disabled', loading).html(loading ? 'Iniciando sesión...' : 'Ingresar');
}

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

	$.post(`${route.url}/login-user.php`, $.param(params), response => {
		console.log(response)
		const { status, message } = $.parseResponse(response);
		if (status === 1) {
			setTimeout(() => location.reload(), 2000);
			return;
		}
		dialog.alert('Atención', message);
		resetUI();
	})
	.fail(() => {
		dialog.alert('Error', 'Error al procesar la petición');
		resetUI();
	});
}

function togglePasswordVisibility() {
	$togglePass.on('click', () => {
		const isText = $passwordInp.attr('type') === 'text';
		$passwordInp.attr('type', isText ? 'password' : 'text');
	});
}

const plantilla = `<div id="AFormInputs">
	<div class="flex flex-col gap-2">
		<label for="r_email" class="text-[#121517] dark:text-gray-200 text-sm font-semibold leading-normal">Correo electr&oacute;nico:</label>
		<input type="text" class="form-input w-full rounded-lg text-[#121517] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dce1e5] dark:border-[#374151] bg-white dark:bg-[#121920] focus:border-primary h-12 placeholder:text-[#657686] px-4 text-base font-normal transition-all" name="r_email" placeholder="example@gmail.com" id="r_email" maxlength="35"/>
	</div>
</div>`;

function remindResend(confirmed = false, type) {
	const pageMap = {
		password: { page: 'pass', title: 'Recuperar Contraseña', action: remindResend },
		validation: { page: 'validation', title: 'Reenviar validación', action: remindResend }
	};
	const config = pageMap[type];
	if (!config) return console.warn(`Tipo desconocido: ${type}`);
	if (!confirmed) {
		dialog.init({
	      title: config.title,
	      body: plantilla,
	      buttons: {
	         confirm: {
	            text: 'Continuar',
	            action: () => config.action(true, type)
	         },
	         cancel: {
	            text: 'Cancelar',
	            action: 'close'
	         }
	      }
	   });
	} else {
		const email = encodeURIComponent($('#r_email').val());
		$.post(`${route.url}/recover-${config.page}.php`, `r_email=${email}`, response => {
			const { status, message } = $.parseResponse(response);
			const alertTitle = status === 0 ? 'Oops!' : 'Hecho';
			dialog.alert(alertTitle, message);
		});
	}
}

$(document).ready(function() {
	$form.on('keyup focusout', 'input', resetUI);
	$form.on('submit', e => {
		e.preventDefault();
		iniciarSesion();
	});
	togglePasswordVisibility();
	$('#forgotPassword').on('click', () => remindResend(false, 'password'));
	$('#emailVerify').on('click', () => remindResend(false, 'validation'));
});