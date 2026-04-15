const moderacion = {
	posts: {
		ocultar(pid) {
			const razon = $('#d_razon').val();
			if(razon.length <= 1 || razon.length > 50) {
				dialog.alert('Error', (razon.length <= 1 ? 'Introduzca una raz&oacute;n' : 'La raz&oacute;n debe tener menos de 50 letras.'));
				$('#d_razon').focus();
				return;
			} else {
				api('moderacion-posts?do=ocultar', { razon, pid }, response => {
					const { status, message } = $.parseResponse(response);
					const title = status === 1 ? 'Hecho' : 'Opps!';
					dialog.alert(title, message, true);
				});
			}
		}
	},
	view(postid) {
		api('moderacion-posts?do=view', { postid }, response => {
			dialog.init({
				title: '...', 
				body: response, 
				buttons: { 
					cancel: { text: 'Cerrar' } 
				}
			});
		});
	},
	borrar(pid, redirect, aceptar) {
		if(!aceptar) {
			api('moderacion-posts?do=borrar', {}, response => {
				dialog.init({ title: 'Borrar Post', body: response, 
					buttons: { 
						confirm: { text: 'Borrar', action: () => moderacion.posts.borrar(pid, redirect, 1) } 
					}
				});
			});
			return;
		} else {
			dialog.loading('Eliminando...', 'Borrar Post');
			const razon = $('#razon').val()
			const razon_desc = $('input[name=razon_desc]').val();
			if($('#send_b').prop('checked')){
				const send_b = 'yes';
			}
			api('moderacion-posts?do=borrar', { pid, razon, razon_desc, send_b }, response => {
				const { status, message } = $.parseResponse(response);
				if(status === 0) {
					dialog.alert('Error', message);
					return;
				}
				moderacion.redirect({ 
					url: "/moderacion/posts", 
					redirect, 
					page: 'posts', 
					target: `#report_${pid}`, 
					message 
				});
			});
		}
	},
	mensajes: {
		borrar(mpid, few) {
			if(!few){
				dialog.easy('Borrar Mensaje', '&#191;Quiere eliminar <b>toda</b> la conversaci&oacute;n?', 'S&iacute; borrar', () => moderacion.mensajes.borrar(mpid, 1));
				return;
			} else {
				api('moderacion-mps?do=borrar', { mpid }, response => {
					const { status, message } = $.parseResponse(response);
					dialog.alert((status === 0 ? 'Opps!' : 'Hecho'), message, false);
					$('#report_' + mpid).fadeOut(); 
				});
	  		}
		}
	},
	fotos: {
		borrar(fid, redirect, aceptar) {
			if(!aceptar) {
				api('moderacion-fotos?do=borrar', { fid }, response => {
					dialog.easy('Borrar Foto', response, 'Borrar foto', () => moderacion.fotos.borrar(fid, redirect, 1));
					$('#report_' + fid).fadeOut(); 
				});
			} else {
				dialog.loading('Eliminando...', 'Borrar Foto');
				const razon = $('#razon').val()
				const razon_desc = $('input[name=razon_desc]').val();
				api('moderacion-fotos?do=borrar', { fid, razon, razon_desc }, response => {
					const { status, message } = $.parseResponse(response);
					if(status === 0) {
						dialog.alert('Error', message, false);
						return;
					}
					moderacion.redirect({ 
						url: "/moderacion/fotos", 
						redirect, 
						page: 'fotos', 
						target: `#report_${fid}`, 
						message 
					});
				});
			}
		}
	},
	usuarios: {
		action(uid, action, redirect) {
			let esAviso = (action === 'aviso');
			const btn_txt = esAviso ? 'Enviar' : 'Suspender';
			const titulo = esAviso ? 'Enviar Aviso/Alerta' : 'Suspender usuario';
			const funcion = `set_${action}`;
			moderacion.loadDialog(`/moderacion-users?do=${action}`, { uid }, titulo, btn_txt,
				() => moderacion.usuarios[funcion](uid, redirect)
			);
		},
		set_aviso(uid, redirect, type = '') {
			const av_type = $('#mod_type').val();
			const av_subject = $('#mod_subject').val();
			const av_body = $('#mod_body').val();
			moderacion.sendData('/moderacion-users?do=aviso', { uid, av_type, av_subject, av_body }, uid, redirect);
		},
		set_ban(uid, redirect, type = '') {
			const b_time = $('#mod_time').val();
			const b_cant = $('#mod_cant').val();
			const b_causa = $('#mod_causa').val();
			//
			moderacion.sendData('/moderacion-users?do=ban', { uid, b_time, b_cant, b_causa }, uid, redirect, '');
		}
	},
	loadDialog(endpoint, params, title, text, action) {
		api(endpoint, params, response => dialog.easy(title, response, text, action));
	},
	sendData(endpoint, params, id, redirect, type) {
		dialog.loading('Procesando...', 'Espere');
		api(endpoint, params, response => {
			const { status, message } = $.parseResponse(response);
			if(status === 0) {
				dialog.alert('Error', message, false);
				return;
			}
			dialog.alert('Aviso', message);
			moderacion.redirect({ 
				url: `/moderacion/${type}`, 
				redirect, 
				page: '',
				target: `#report_${id}`, 
				message 
			}); 
		});
	},
	reboot(id, type, hdo, redirect) {
		api(`moderacion-${type}?do=${hdo}`, { id }, response => {
			const { status, message } = $.parseResponse(response);
			if(status === 0) {
				dialog.alert('Error', message, false);
				return;
			}
			dialog.alert('Aviso', message);
			$('#report_' + id).fadeOut();
			moderacion.redirect({ 
				url: `/moderacion/${type}`, 
				redirect, 
				page: '',
				target: `#report_${id}`, 
				message 
			}); 
		});
	},
	redirect({ url, redirect, page, target, message }) {
		if(redirect === 'true' || redirect === page) {
			let show = (redirect === page)
			if(show) dialog.alert('Aviso', message);
			let endpoint = `${route.url}/` + (show ? `${page}/` : `${url}/`);
			setTimeout(() => document.location.href =  endpoint, 1200);
		} else {
			$(target).slideUp();   
		}
	}
}
