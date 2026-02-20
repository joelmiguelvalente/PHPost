function desactivate(next = false) {
	if(next) {
		const param = $.param({ validar: true });
		$('#loading').fadeIn(250); 
		$.post(`${route.url}/cuenta-desactivar.php`, param, response => {
			const { status, message } = $.parseResponse(response);
			dialog.toast({
				type: (status ? 'success' : 'danger'),
				message: message,
				position: 'bottom-left'
			});
			$('#loading').fadeOut(250); 
		});
		return;
	}
	dialog.easy('Desactivar Cuenta', '&#191;Seguro que quiere desativar su cuenta?', 'Desactivar', () => desactivate(true));
}

const cuenta = {
	chgpais: () => {
		// Campo pais
		const pais = $("select[name=pais]").val();
		const estado = $("select[name=estado]");
		if(empty(pais)) estado.addClass('disabled').attr('disabled', 'disabled').val('');
		else {
			//Obtengo las estados
			$(estado).html('');
			$('#loading').fadeIn(250);
			$.get(`${route.url}/registro-geo.php?pais_code=${pais}`, response => {
				const { status, message } = $.parseResponse(response);
				if(status === 1) {
					estado.val('').append(message).removeAttr('disabled').focus();
				}
				$('#loading').fadeOut(250);
			});
		}
	},
	guardar_datos: () => {
		$('#loading').slideDown(250);
		const formData = $("form[name=editarcuenta]").serialize();
		$.post(`${route.url}/cuenta-guardar.php`, formData, response => {
			console.log(response)
			const { status, message } = $.parseResponse(response);
			dialog.toast({
				type: (status ? 'success' : 'danger'),
				title: 'Guardado',
				message: message,
				duration: 4000,
				position: 'top-right'
			});
		});
	}
}