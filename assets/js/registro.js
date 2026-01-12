'use strict';

// --- CONSTANTES Y CONFIGURACIÓN ---

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

/**
 * Constructor de peticiones POST simplificado para el módulo de registro.
 * @param {string} page - El endpoint PHP (ej: 'check-nick').
 * @param {object|string} data - Los datos a enviar (objeto o string serializado).
 * @returns {Promise<string>} La promesa de la respuesta del servidor (texto).
 */
const up = {
	post: async function(page, data) {
		return await $.post(`/registro-${page}.php?ajax=true`, data);
	}
};

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
		// Quitamos .removeAttr('style') y ponemos el width aquí para asegurar la animación
		'width': `${(strength / 4) * 100}%` 
	});
	$textEl.html(PASSWORD_LEVEL.texts[strength]);
}

/**
 * Valida el campo Nick contra el servidor.
 * @param {string} inputNameElement - 'nick'.
 * @param {string} inputValue - Valor del campo.
 * @param {string} inputIDElement - Selector de ID (ej: '#nick').
 */
function validateNick(inputNameElement, inputValue, inputIDElement) {
	// Validaciones de longitud mínima/máxima antes de contactar al servidor
  if (inputValue.length < 4) {
		approved[inputNameElement] = displayMessage(inputIDElement, `Debe ser mayor a 4 caracteres`, STATUS.INFO);
		return;
	} else if (inputValue.length > 20) {
		approved[inputNameElement] = displayMessage(inputIDElement, `Debe ser menor a 20 caracteres`, STATUS.INFO);
		return;
	}
	displayMessage(inputIDElement, `Comprobando ${inputNameElement}...`, STATUS.WARNING);
	$.post(`${route.url}/registro-check-nick.php?ajax=true`, $.param({ nick: inputValue }), response => {
		approved[inputNameElement] = validateField(inputNameElement, response);
	});
}

/**
 * Valida el campo Email contra el servidor.
 * @param {string} inputNameElement - 'email'.
 * @param {string} inputValue - Valor del campo.
 * @param {string} inputIDElement - Selector de ID (ej: '#email').
 */
function validateEmail(inputNameElement, inputValue, inputIDElement) {
	displayMessage(inputIDElement, `Comprobando ${inputNameElement}...`, STATUS.WARNING);
	$.post(`${route.url}/registro-check-email.php?ajax=true`, { email: inputValue }, response => {
		approved[inputNameElement] = validateField(inputNameElement, response);
	});
}

/**
 * Valida la contraseña (fortaleza local y contra el nick).
 * @param {string} inputNameElement - 'password'.
 * @param {string} inputIDElement - Selector de ID (ej: '#password').
 */
function validatePassword(inputNameElement, inputIDElement) {
	const valueOfPassword = $("#password").val();
	const valueOfNick = $("#nick").val();
	
	checkStrength(valueOfPassword); // Actualiza UX de fortaleza

	let message = '';
	let type = STATUS.SUCCESS; // Asumimos éxito a menos que haya reglas locales
	
	if (valueOfPassword === valueOfNick) {
		message = 'No puede ser igual al Nick';
		type = STATUS.ERROR;
	} else if (valueOfPassword.length < 4) {
		message = 'Debe tener al menos 4 caracteres';
		type = STATUS.ERROR;
	}

	// Si hay un error local, lo mostramos y terminamos la validación
	if (type === STATUS.ERROR) {
		approved[inputNameElement] = displayMessage(inputIDElement, message, type);
	} else {
		// Si no hay error local, mostramos el mensaje de éxito o info (como 'Comprobando...')
		// Tu código original usaba validateField para esto, lo cual es incorrecto
		// porque validateField espera la respuesta del servidor.
		approved[inputNameElement] = displayMessage(inputIDElement, 'Contraseña OK. ', STATUS.SUCCESS);
	}
}

/**
 * Función principal que dirige la validación del campo.
 * @param {HTMLElement} element - El elemento DOM que disparó el evento.
 */
function checkField(element) {
	const $element = $(element);
	const inputNameElement = $element.attr('name');
	const inputIDElement = $element.attr('id');
	let inputValue = $element.val();

	switch (inputNameElement) {
		case 'nick':
			validateNick(inputNameElement, inputValue, inputIDElement);
		break;
		case 'email':
			validateEmail(inputNameElement, inputValue, inputIDElement);
		break;
		case 'password':
			validatePassword(inputNameElement, inputIDElement);
		break;
		case 'terminos':
			let isChecked = $element.prop('checked');
			// Si no está marcado, tipo 0 (error), si sí, tipo 1 (success)
			const type = isChecked ? STATUS.SUCCESS : STATUS.ERROR;
			const msg = isChecked ? 'Términos aceptados' : 'Debes aceptar los términos';
			
			approved[inputNameElement] = displayMessage(inputIDElement, msg, type);
		break;
	}
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
function btnLoad(action = false) {
	const TXT_ACTION = action ? 'Creando nueva cuenta...' : 'Registrarse';
	$('#registrarme').attr({ disabled: action }).html(TXT_ACTION);
}

function showDialog(message, show = true) {
	$('#RegistroForm')[!show ? 'show' : 'hide']();
	$('.display-message').html(message)[show ? 'show' : 'hide']();
}

/**
 * Procesa el envío del formulario y la creación de la cuenta.
 */
function createAccount() {
	// Solo continuar si todos los campos requeridos han pasado la validación
	if (areAllApproved(approved)) {
		btnLoad(true);
		let formData = $('#RegistroForm').serializeArray();
		//showDialog('Estamos procesando...');
		// Petición de creación de cuenta
		$.post(`${route.url}/registro-nuevo.php?ajax=true`, $.param(formData), response => {
			const { status, message } = $.parseResponse(response);
		
			if (status === STATUS.ERROR || status === STATUS.WARNING) {
				showDialog(message);
				btnLoad();
				return;
			}
			
			// Éxito o Acción Especial (e.g., 2FA)
			if (status === STATUS.SUCCESS || status === STATUS.WARNING) { // Warning 2 puede ser redirigir
				showDialog(message, false);
				setTimeout(() => location.href = route.url, 5000);
			}
		}).catch(error => {
			showDialog('Fallo al enviar la solicitud al servidor.');
			btnLoad();
		});
	} else {
		showDialog('Por favor, complete correctamente todos los campos requeridos.');
	}
}

/**
 * Redirige al usuario después de un registro exitoso.
 * @param {number} [type=0] - Tipo de redirección (0: home, 2: cuenta).
 */
function redirect(type = 0) {
	location.href = route.url + '/' + (type === 2 ? 'cuenta/' : '');
}

/**
 * Alterna la visibilidad del campo de contraseña (Mostrar/Ocultar).
 */
function togglePasswordVisibility() {
	$iWantPassword.on('click', () => {
		const isVisible = $inputPassword.attr('type') === 'text';
		// Lógica: Si está visible, volvemos a 'password' y ponemos el icono 'lock'
		const newType = isVisible ? 'password' : 'text';
		$inputPassword.attr({ type: newType });
	});
}

$(() => {
	$('#RegistroForm').on('focusout keyup', 'input', function() {
		checkField(this)
	});

	// Asignar evento change para inputs tipo radio y checkbox (ej: 'terminos')
	$('#RegistroForm').on('change', 'input[type="checkbox"]', function() {
		checkField(this)
	});

	// Asignar evento submit al formulario de registro
	$('#RegistroForm').submit(function(e) {
		e.preventDefault();
		createAccount();
	});

	togglePasswordVisibility();

	const { app: { publicKey } } = global_data;
	
   grecaptcha.ready(function() {
   	grecaptcha.execute(publicKey, {action: 'submit'}).then(function(token) {
   		response.value = token;
   		$('#registrarme').removeAttr('disabled');
   	});
   });

});