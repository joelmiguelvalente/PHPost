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
	$('#AFStatus div').fadeOut().text(message).fadeIn();
	return false;
}

const afiliado = {
	vars: [],
	nuevo() {
		api('afiliado-form', null, response => {
			dialog.init({
				title: 'Nueva Afiliaci&oacute;n',
				body: response,
				buttons: {
					confirm: { text: 'Enviar', action: () => afiliado.enviar(0) },
					cancel: { text: 'Cancelar', action: 'close' }
				}
			});
		}, { method: 'GET' });
	},
	enviar() {
		const inputs = $('#AFormInputs').find(':input[name]').not(':button, :submit, :reset');
		let status = true;
	   const params = {};
	   inputs.each((_, field) => {
	      const $field = $(field);
	      const name = $field.attr('name');
	      let value = ($field.val() ?? '').toString().trim();
	      if (name === 'a_sid' && value === '') {
	         value = 'offKey';
	      }
	      if (value === '' && status) {
	         const label = $field.parent().find('label').text();
	         status = showMessage(`No has completado el campo ${label}`);
	         return false;
	      }
	      if (status && name === 'a_url' && !isValidUrl(value)) {
	         status = showMessage('La URL ingresada no es válida');
	         return false;
	      }
	      if (status && name === 'a_banner' && !isImageUrl(value)) {
	      	status = showMessage('El banner debe ser una URL de imagen válida');
	      	return false;
	      }
	      params[name] = value;
	   });
	   if (status) {
	      dialog.loading('Enviando...', 'Nueva Afiliación');
	      afiliado.enviando(params);
	   }
	},
	enviando(params) {
		$('#loading').fadeIn(250);
		api('afiliado-nuevo', params, response => {
			const { status, message } = $.parseResponse(response);
			if(status === 0) {
				$('#AFStatus > span').fadeOut().text(message).fadeIn();
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
		api('afiliado-detalles', { ref: aid }, response => {
			dialog.init({
				title: 'Detalles',
				body: response,
				buttons: { confirm: { text: 'Aceptar', action: () => 'close' } }
			});
			$('#loading').fadeOut(350);
		});
	},
	borrar(afid, gew) {
    	if(!gew) {
			dialog.init({
				title: 'Borrar Afiliado',
				body: '&#191;Quiere borrar este afiliado?',
		      buttons: {
		         confirm: { text: 'Borrar afiliado', action: () => afiliado.borrar(afid, true) },
		      }
		   });
		   return;
      }
      $('#loading').fadeIn(250);
      api('afiliado-borrar', { afid }, response => {
      	const { status, message } = $.parseResponse(response);
      	dialog.alert(status ? 'Hecho' : 'Opps', message, false);
      	if(status) {
      		$(`#few_${afid}`).remove();
      	}
      });
      $('#loading').fadeOut(350);
   },
   activar(aid) {
   	$('#loading').fadeIn(250);
      api('afiliado-setactive', { aid }, response => {
      	const { status, message } = $.parseResponse(response);
      	dialog.alert(status ? 'Hecho' : 'Opps', message, false);
      	if(status === 1 || status === 2) {
				let color = (status === 1) ? 'green' : 'purple';
				$(`#status_afiliado_${aid} > span`).removeClass('bg-purple-100 text-purple-800 bg-green-100 text-green-800')
				.addClass(`bg-${color}-100 text-${color}-800`)
				.text((status === 1 ? 'Activa' : 'Inactiva'))
      	}
      });
   	$('#loading').fadeOut(250);
  	}
}
