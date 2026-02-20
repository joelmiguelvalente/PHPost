/**
 * Funcion para bloquear usuarios
*/
const configBloqueo = {
	perfil: {
		selector: '#bloquear_cambiar',
		remove: 'bloquearU desbloquearU',
		add: bloqueado => bloqueado ? 'desbloquearU' : 'bloquearU',
		withClasses: true
	},
	mis_bloqueados: {
		selector: bloqueado => `.bloquear_usuario_${user}`,
		remove: 'bloqueadosU desbloqueadosU',
		add: bloqueado => bloqueado ? 'desbloqueadosU' : 'bloqueadosU',
		withClasses: true
	},
	mensajes: {
		selector: '#bloquear_cambiar',
		withClasses: false
	}
};
const actualizarUIBloqueo = (user, bloqueado, lugar) => {
	if (configBloqueo[lugar]) {
		const cfg = BLOQUEO_UI[lugar];
		const $el = $(typeof cfg.selector === 'function' ? cfg.selector(user) : cfg.selector);
		$el.text(bloqueado ? 'Desbloquear' : 'Bloquear');
		if (cfg.classes) {
			$el.removeClass(cfg.remove).addClass(cfg.add(bloqueado));
		}
		$el.off('click.bloqueo').on('click.bloqueo', e => {
			e.preventDefault();
			bloquear(user, !bloqueado, lugar);
		});
	}
	if (lugar === 'respuestas' || lugar === 'comentarios') {
		$(`.bloquear_${user}`).toggle(!bloqueado);
		$(`.desbloquear_${user}`).toggle(bloqueado);
	}
}

const bloquear = (user, bloqueado, lugar, aceptar) => {
	if(!aceptar && bloqueado) {
		dialog.init({
			title: 'Bloquear usuario',
			body: '&iquest;Realmente deseas bloquear a este usuario?',
			buttons: {
				confirm: {
					text: 'Si, bloquear',
					action: () => bloquear(user, true, lugar, true)
				},
				cancel: {
					text: 'No, cerrar'
				}
			}
		});
		return;
	}
	if(bloqueado) {
		dialog.loading('Procesando...');
	}
	let params = $.param({
		user,
		bloqueado
	});
	api('bloqueos-cambiar.php', params, response => {
		const { status, message } = $.parseResponse(response);
		dialog.alert('Bloquear Usuarios', message);
		if (status === 1) actualizarUIBloqueo(user, bloqueado, lugar);
	}, {
	   error: ({ xhr, status, error }) => {
	   	dialog.reintentar(bloquear(user, bloqueado, lugar, true));
	   }
	});
}