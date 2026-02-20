/* Denuncias */
const denuncia = {
	nueva(type, obj_id, obj_title, obj_user) {
		const params = $.param({ obj_id, obj_title, obj_user });
		$('#loading').fadeIn(250); 
		$.post(`${route.url}/denuncia-${type}.php`, params, response => {
			denuncia.dialog(response, obj_id, type);
			$('#loading').fadeOut(350);
		});
	},
	dialog(html, obj_id, type) {
		dialog.init({
			title: `Denunciar ${type}`,
			body: html,
			buttons: {
				confirm: {
					text: 'Enviar',
					action: () => denuncia.enviar(obj_id, type)
				}
			}
		});
	},
	enviar(obj_id, type) {
		const params = $.param({
			obj_id, 
			razon: $('select[name=razon]').val(), 
			extras: $('textarea[name=extras]').val()
		});
		//
		$('#loading').fadeIn(250);                    
		$.post(`${route.url}/denuncia-${type}.php`, params, response => {
			console.log(response)
			const { status, message } = $.parseResponse(response);
			dialog.alert((status ? "Bien" : "Error"), message);
			$('#loading').fadeOut(350);
		});
	}
}