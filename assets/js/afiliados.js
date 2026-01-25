function isValidUrl(value) {
	try {
		const url = new URL(value);
		return ['http:', 'https:'].includes(url.protocol);
	} catch {
		return false;
	}
}

function isImageUrl(value) {
	if (!isValidUrl(value)) return false;

	return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(value);
}

function showMessage(message) {
	$('#AFStatus span').fadeOut().text(message).fadeIn();
	return false;
}

const afiliado = {
	vars: [],
	nuevo() {
		$.get(`${route.url}/afiliado-form.php`, response => {
			dialog.init({
				maskClose: false,
				title: 'Nueva Afiliaci&oacute;n',
				body: response,
				buttons: {
					confirm: { text: 'Enviar', action: () => afiliado.enviar(0) },
					cancel: { text: 'Cancelar', action: 'close' }
				}
			});
		});
	},
	enviar() {
		const inputs = $('#AFormInputs :input');
		let status = true;
		const params = {};

		inputs.each((_, field) => {
			let value = $(field).val().trim();
			const name = $(field).attr('name');
			// valor por defecto
			if (name === 'sid' && value === '') {
				value = 'offKey';
			}
			// requerido
			if (value === '' && status) {
				const label = $(field).parent().find('label').text();
				status = showMessage(`No has completado el campo ${label}`);
				return;
			}
			// validación URL
			if (status && name === 'url' && !isValidUrl(value)) {
				status = showMessage('La URL ingresada no es válida');
				return;
			}
			// validación banner (URL + imagen)
			if (status && name === 'banner' && !isImageUrl(value)) {
				status = showMessage('El banner debe ser una URL de imagen válida');
				return;
			}
			if (status) {
				params[name] = value;
			}
		});
		if (status) {
			dialog.loading('Enviando...', 'Nueva Afiliación');
			afiliado.enviando(params);
		}
	},
	enviando(params) {
		$('#loading').fadeIn(250); 
		$.post(`${route.url}/afiliado-nuevo.php`, params, response => {
			const { status, message } = $.parseResponse(response);
			if(status === 0) {
				$('#AFStatus > span').fadeOut().text('La URL es incorrecta').fadeIn();
				return;
			}
			if(status === 1) {
				dialog.alert('Bien', message);
				return;
			}
			if(status === 2) {
				$('#AFStatus > span').fadeOut().text('Faltan datos').fadeIn();
				return;
			}
			$('#loading').fadeOut(350); 
		});
	},
	detalles(aid) {
		$('#loading').fadeIn(250);
		$.post(`${route.url}/afiliado-detalles.php`, { ref: aid }, response => {
			dialog.init({
				title: 'Detalles',
				body: response,
				buttons: { confirm: { text: 'Aceptar', action: () => 'close' } }
			});
			$('#loading').fadeOut(350); 
		}); 
	 }
}