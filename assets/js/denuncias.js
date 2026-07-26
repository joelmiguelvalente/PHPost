/* Denuncias */
const denuncia = {
	nueva(type, obj_id, obj_title, obj_user) {
		$('#loading').fadeIn(250); 
		api(`denuncia-${type}`, { obj_id, obj_title, obj_user }, response => {
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
		const params = {
			obj_id, 
			razon: $('select[name=razon]').val(), 
			extras: $('textarea[name=extras]').val()
		};
		//
		$('#loading').fadeIn(250);                    
		api(`denuncia-${type}`, params, response => {
			console.log(response)
			const { status, message } = $.parseResponse(response);
			dialog.alert((status ? "Bien" : "Error"), message);
			$('#loading').fadeOut(350);
		});
	}
}
