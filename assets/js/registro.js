'use strict';

// Función para obtener el parámetro redirect de la URL
function getRedirectParam() {
   const urlParams = new URLSearchParams(window.location.search);
   return urlParams.get('redirect');
}

// --- CONSTANTES Y CONFIGURACIÓN ---
const $form	= $('#RegistroForm');

/** Códigos de estado del Backend (el primer carácter de la respuesta) */
const STATUS = {
	ERROR: 0,
	SUCCESS: 1,
	WARNING: 2,
	INFO: 3,
	CRITICAL: 4
};

// Clases CSS utilizadas para el manejo de mensajes de validación
const VALIDATION_CLASSES_TEXT = 'text-error text-success text-secondary text-primary';
const VALIDATION_CLASSES_BORDER = 'border-error border-success border-secondary border-primary';

// Estado de aprobación de cada campo
const approved = {
	nick: false,
	password: false,
	email: false,
	terminos: false
};

// Patrones de expresiones regulares para validar campos
const REGEX = {
	nick: /^[a-zA-Z0-9\_\-]{4,20}$/,
	password: /^.{4,32}$/,
	email: /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/
};

// Niveles de seguridad de la contraseña para UX
const PASSWORD_LEVEL = {
	colors: { 0: 'gray', 1: 'green', 2: '#E5B91E', 3: '#E5521E', 4: '#CD1B1B' },
	texts: { 0: 'Muy fácil', 1: 'Fácil', 2: 'Medio', 3: 'Difícil', 4: 'Extremadamente difícil' }
};

// Referencias a elementos
const $iWantPassword = $(".seePassword");
const $inputPassword = $('input[type="password"]');

function onLoader(show = true) {
	const $loader = $('#loader');
	if (show && !$loader.length) {
		$form.append(
			`<div id="loader" class="fixed flex justify-center items-center bg-gray-50/50" style="inset:0;">
				<img src="${route.img}/large-loading.gif" width="32" height="32" alt="Iniciando sesión">
			</div>`
		);
		return;
	}
	$loader.remove();
}

/**
 * Muestra mensajes de validación al usuario.
 * @param {string} selector - Selector del campo de entrada.
 * @param {string} msg - El mensaje de texto a mostrar.
 * @param {number} type - Código de estado para determinar la clase CSS (0: error, 1: ok, etc.).
 * @returns {boolean} True si la validación fue exitosa (type=1), false en caso contrario.
 */
function displayMessage(selector, message, type) {
	const statusClassesBorder = VALIDATION_CLASSES_BORDER.split(' ');
	const appendClassBorder = statusClassesBorder[type];

	// Selecciona el elemento '.help' dentro del contenedor principal
	const $input = $(`#${selector}`);
	$input.removeClass(VALIDATION_CLASSES_BORDER).addClass(appendClassBorder);
	
	const statusClassesText = VALIDATION_CLASSES_TEXT.split(' ');
	const appendClassText = statusClassesText[type];
	// Selecciona el elemento '.help' dentro del contenedor principal
	const $help = $(`[data-label=${selector}]`);
	$help.removeClass(VALIDATION_CLASSES_TEXT).addClass(appendClassText).html(message);
	
	return (type === STATUS.SUCCESS);
}

/**
 * Valida la respuesta del servidor (código y mensaje) y la regex local.
 * @param {string} selector - Nombre del campo ('nick', 'email', 'password').
 * @param {string} response - Respuesta del servidor (ej: '1: Nick disponible').
 * @returns {boolean} Resultado final de la validación.
 */
function validateField(selector, response) {
	let valueOfText = $(`#${selector}`).val();
	const { status, message } = $.parseResponse(response);

	// Si el nombre es 'password2', usamos la regex de 'password'
	const fieldRegex = REGEX[selector === 'password2' ? 'password' : selector];
	const verifyRegex = fieldRegex ? fieldRegex.test(valueOfText) : true;
 
	// La validación es exitosa solo si pasa la regex y el servidor responde SUCCESS
	if (verifyRegex) {
		return displayMessage(selector, message, status);
	}
	
	// Si falla la regex local, se asume error
	return displayMessage(selector, "Formato incorrecto o fuera de rango", STATUS.ERROR);
}

const endpoint = (element, input, param) => {
	displayMessage(element, `Comprobando ${input}...`, STATUS.WARNING);
	const endpoint = `${route.url}/registro-check-${element}?ajax=true`;
	$.post(endpoint, param, response => approved[input] = validateField(input, response));
}

/**
 * Evalúa la fortaleza de la contraseña y actualiza el feedback visual.
 * @param {string} password - La contraseña a evaluar.
 * @param {string} nameEl - Nombre del campo (para compatibilidad).
 */
function checkStrength(password) {
	let strength = 0; // 0 (Muy fácil) a 4 (Extremadamente difícil)
 
	if (password.length >= 8) strength += 1;
	if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
	if (password.match(/\d/)) strength += 1;
	if (password.match(/[^a-zA-Z\d]/)) strength += 1;

	// Actualización visual
	const $strengthEl = $('.password-strength-fill');
	const $textEl = $('#statusPassword');
	
	$strengthEl.css({ 
		'background-color': PASSWORD_LEVEL.colors[strength],
		'width': `${(strength / 4) * 100}%` 
	});
	$textEl.html(PASSWORD_LEVEL.texts[strength]);
}

/**
 * Valida el campo Nick contra el servidor.
 * @param {string} input - 'nick'.
 * @param {string} value - Valor del campo.
 * @param {string} element - Selector de ID (ej: '#nick').
 */
function validateNick(input, value, element) {
	// Validaciones de longitud mínima/máxima antes de contactar al servidor
  if (value.length < 4) {
		approved[input] = displayMessage(element, `Debe ser mayor a 4 caracteres`, STATUS.INFO);
		return;
	} else if (value.length > 20) {
		approved[input] = displayMessage(element, `Debe ser menor a 20 caracteres`, STATUS.INFO);
		return;
	}
	endpoint(element, input, { nick: value });
}

/**
 * Valida el campo Email contra el servidor.
 * @param {string} input - 'email'.
 * @param {string} value - Valor del campo.
 * @param {string} element - Selector de ID (ej: '#email').
 */
const validateEmail = (input, value, element) => endpoint(element, input, { email: value });

/**
 * Valida la contraseña (fortaleza local y contra el nick).
 * @param {string} inputNameElement - 'password'.
 * @param {string} inputIDElement - Selector de ID (ej: '#password').
 */
function validatePassword(input, element) {
	const valueOfPassword = $("#password").val();
	const valueOfNick = $("#nick").val();

	checkStrength(valueOfPassword);

	let message = '';
	let type = STATUS.SUCCESS;
	if (valueOfPassword === valueOfNick) {
		message = 'No puede ser igual al Nick';
		type = STATUS.ERROR;
	} else if (valueOfPassword.length < 4) {
		message = 'Debe tener al menos 4 caracteres';
		type = STATUS.ERROR;
	}
	// Si hay un error local, lo mostramos y terminamos la validación
	if (type === STATUS.ERROR) {
		approved[input] = displayMessage(element, message, type);
	} else {
		// Si no hay error local, mostramos el mensaje de éxito o info (como 'Comprobando...')
		approved[input] = displayMessage(element, 'Contraseña OK. ', STATUS.SUCCESS);
	}
}

/**
 * Función principal que dirige la validación del campo.
 * @param {HTMLElement} element - El elemento DOM que disparó el evento.
 */
function checkField(element) {
   const $field = $(element);
   const field = {
      name: $field.attr('name'),
      id: $field.attr('id'),
      value: $field.val()
   };
   const handlers = {
      nick: ({ name, value, id }) => validateNick(name, value, id),
      email: ({ name, value, id }) => validateEmail(name, value, id),
      password: ({ name, id }) => validatePassword(name, id),
      terminos: ({ name, id, $el }) => {
         const isChecked = $field.prop('checked');
         const status = isChecked ? STATUS.SUCCESS : STATUS.ERROR;
         const message = isChecked ? 'Términos aceptados' : 'Debes aceptar los términos';
         approved[name] = displayMessage(id, message, status);
      }
   };
   handlers[field.name]?.(field);
}

/**
 * Verifica si todos los campos requeridos están aprobados.
 * @param {object} obj - El objeto 'approved'.
 * @returns {boolean} True si todos los valores son true.
 */
function areAllApproved(obj) {
	for (const prop in obj) {
	if (Object.prototype.hasOwnProperty.call(obj, prop) && !obj[prop]) {
			return false;
		}
	}
	return true;
}

/**
 * Muestra/Oculta el estado de carga en el botón de submit.
 * @param {boolean} [action=false] - True para cargar, false para estado normal.
 */
function buttonLoader(action = false) {
	const TXT_ACTION = action ? 'Creando nueva cuenta...' : 'Registrarse';
	$('#registrarme').attr({ disabled: action }).html(TXT_ACTION);
}

const onDialog = (message, show = true) => {
	if(show) {
		dialog.alert('Info...', message);
		return;
	}
	dialog.close();
}

/**
 * Procesa el envío del formulario y la creación de la cuenta.
 */
function createAccount() {
	// Solo continuar si todos los campos requeridos han pasado la validación
	if (!areAllApproved(approved)) {
		onDialog('Por favor, complete correctamente todos los campos requeridos.');
		onLoader(false);
		buttonLoader();
	}
	buttonLoader(true);
	onLoader(true);
	let formData = $form.serializeArray();
	// Petición de creación de cuenta
	$.post(`${route.url}/registro-nuevo?ajax=true`, $.param(formData), response => {
		const { status, message } = $.parseResponse(response);
		onDialog(message);
	
		if (status === STATUS.ERROR || status === STATUS.WARNING) {
			onLoader(true);
			onDialog('', false);
			buttonLoader();
			return;
		}
		
		// Éxito o Acción Especial (e.g., 2FA)
		if (status === STATUS.SUCCESS || status === STATUS.WARNING) {
			onLoader(false);
			// Obtener el parámetro redirect
			const redirectUrl = getRedirectParam();
			// Si existe el parámetro redirect, redirigir a esa URL, de lo contrario recargar
			if (redirectUrl) {
				window.location.href = decodeURIComponent(redirectUrl);
			} else {
				setTimeout(() => location.href = route.url, 2000);
			}
		}


	}).catch(error => {
		onDialog('Fallo al enviar la solicitud al servidor.');
		onLoader(false);
		buttonLoader();
	}); 
}

/**
 * Redirige al usuario después de un registro exitoso.
 * @param {number} [type=0] - Tipo de redirección (0: home, 2: cuenta).
 */
const redirect = (type = 0) => location.href = route.url + '/' + (type === 2 ? 'cuenta/' : '');

/**
 * Alterna la visibilidad del campo de contraseña (Mostrar/Ocultar).
 */
const togglePasswordVisibility = () => {
	$iWantPassword.on('click', () => {
		const isVisible = $inputPassword.attr('type') === 'text';
		// Lógica: Si está visible, volvemos a 'password' y ponemos el icono 'lock'
		$inputPassword.attr({ type: (isVisible ? 'password' : 'text') });
	});
}

async function executeCaptcha({ instance, key, action }) {
   await new Promise(resolve => instance.ready(resolve));
   const token = await instance.execute(key, { action });
   response.value = token;
   $('#registrarme').prop('disabled', false);
}

$(() => {

	// Verificamos mientras escribimos y cuando salimos del foco
	$form.on('focusout keyup', 'input', function() {
		checkField(this)
	});

	// Asignar evento change para inputs tipo radio y checkbox (ej: 'terminos')
	$form.on('change', 'input[type="checkbox"]', function() {
		checkField(this)
	});

	// Asignar evento submit al formulario de registro
	$form.submit(e => {
		e.preventDefault();
		createAccount();
	});

	togglePasswordVisibility();

	const config = (captcha.type === 'recaptcha') ? 
	{ instance: grecaptcha, key: captcha.key, action: 'submit' } : 
	{ instance: grecaptcha.enterprise, key: captcha.key, action: 'LOGIN' };
	executeCaptcha(config);

});
