function irACategoria(cat) {
	if (cat === 'root' || cat === 'linea') return;
	const baseUrl = route.url.replace(/\/$/, '');
	let path;
	switch (cat) {
		case -1:
			path = '/';
			break;
		case -2:
			path = '/posts/';
			break;
		default:
			path = `/posts/${encodeURIComponent(cat)}/`;
	}
	window.location.href = baseUrl + path;
}

const GGET_KEY_MAP = Object.freeze({
	key: 'user_key',
	postid: 'postid',
	fotoid: 'fotoid',
	temaid: 'temaid'
});
const queryParam = (key, withoutAmp = false) => {
	const realKey = GGET_KEY_MAP[key];
	if (!realKey) return '';

	const value = global_data?.[realKey];
	if (value == null || value === '') return '';

	const prefix = withoutAmp ? '' : '&';
	return `${prefix}${key}=${encodeURIComponent(value)}`;
};

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
					action: () => bloquear(`'${user}'`, true, `'${lugar}'`, true)
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
	const params = new URLSearchParams({
		user,
		...queryParam('key') && { user_key: queryParam('key') },
		...(bloqueado && { bloquear: 1 })
	});
	$.post(`${route.url}/bloqueos-cambiar.php`, params.toString()).done(response => {
		const { status, message } = $.parseResponse(response);
		dialog.alert('Bloquear Usuarios', message);
		if (status === 1) {
			actualizarUIBloqueo(user, bloqueado, lugar);
		}
	})
	.fail(() => {
		dialog.reintentar(`bloquear("${user}", ${bloqueado}, "${lugar}", true)`);
	})
	.always(() => dialog.close);
}

const media = {
	popup(response, short) {
		const total = parseInt(response, 10) || 0;
		const $alert = $(`#alerta_${short}`);
		const current = parseInt($alert.find('span').text(), 10) || 0;
		if (total <= 0) {
			$alert.remove();
			return;
		}
		if (total === current) return;
		let label = '', clase = '';
		if(short === 'mps') {
			label = (total === 1) ? ' mensaje' : ' mensajes';
			clase = 'mensajes';
		} else {
			label = (total === 1) ? ' notificación' : ' notificaciones';
			clase = 'monitor';
		}
		let $container = $alert;
		if (!$container.length) {
			$container = $(`
				<div class="alertas" id="alerta_${short}"><a title=""><span></span></a></div>
			`).appendTo(`.userInfoLogin .${clase}`);
		}
		$container.find('a').attr('title', total + label).find('span').text(total);
		$container.stop(true).animate({ top: '-=5px' }, 100).animate({ top: '+=5px' }, 100);
	},
	show(last, name, short) {
		const $ref = $(`a[name=${name}]`);
		const $list = $(`#${short}_list`);
		// Limpiar alert previo si existe
		$(`#alerta_${short}`).remove();
		// Marcar monitor activo y quitar spinner
		$ref.parent('li').addClass(name.toLowerCase() + '-notificaciones');
		$ref.children('span').removeClass('spinner');
		if (!last) return;
		// Mostrar lista y rellenar contenido
		$list.show().children('ul').html(last);
	},
	close(name, short) {
		const $list = $(`#${short}_list`);
		const $ref = $(`a[name=${name}]`);
		$list.hide();
		$ref.parent('li').removeClass(`${name.toLowerCase()}-notificaciones`);
	}
}

/* Notificaciones */
const notifica = {
	cache: {},
	retry: [],
	handleNumber(block, parse, additional = '') {
		let parsear = parseInt(parse);
		let value = number_format(parsear);
		if(additional !== '') {
			value += ` ${additional}`;
		}
		$(block).html(this.handleNumber(value));
	},
	handleResponse(response, onSuccess, onError = null) {
		const parts = response.split('-');
		const result = {
			ok: parts[0] === '0',
			raw: parts,
			id: parts[1] ?? null,
			value: parts[2] ?? null,
			message: parts[3] ?? null
		};
		if (result.ok) {
			onSuccess(result);
		} else {
			if (onError) {
				onError(result);
			} else if (result.message) {
				dialog.alert('Notificaciones', result.message);
			}
		}
	},
	userMenuHandle(response) {
		this.handleResponse(response, res => {
			const cache_id = 'following_' + res.id;
			this.cache[cache_id] = 0;
			$('div.avatar-box').children('ul').hide();
		});
	},
	userInPostHandle(response) {
		this.handleResponse(response, res => {
			$('.follow_user_post, .unfollow_user_post').toggle();
			this.handleNumber('.metadata-usuario > .nData.user_follow_count', res.value);
			this.userMenuHandle(response);
		});
	},
	userInMonitorHandle(response, obj) {
		this.handleResponse(response, () => $(obj).fadeOut(() => $(obj).remove()));	
	},
	inPostHandle(response) {
		this.handleResponse(response, res => {
			$('a.follow_post, a.unfollow_post').parent('li').toggle();
			this.handleNumber('.post-estadisticas .icons.monitor', res.value);
		});
	},
	inComunidadHandle(response) {
		this.handleResponse(response, res => {
			$('.follow_comunidad, .unfollow_comunidad').toggle();
			this.handleNumber('.comunidad_seguidores', res.value, 'Seguidores');
		});
	},
	temaInComunidadHandle(response) {
		this.handleResponse(response, res => {
			$('.followBox > .follow_tema, .unfollow_tema').toggle();
			this.handleNumber('.tema_notifica_count', res.value, 'Seguidores');
		});
	},
	ruserInAdminHandle(response) {
		this.handleResponse(response, res => $('.ruser' + res.id).toggle());
	},
	listInAdminHandle(response) {
		this.handleResponse(response, res => {
			const $items = $('.list' + res.id);
			$items.toggle();
			$items.first().closest('li').children('div:first').fadeTo(0, $items.first().is(':hidden') ? 0.5 : 1);
		});	
	},
	spamHandle(response) {
		const parts = response.split('-');
		if (parts.length === 2) {
			dialog.alert('Notificaciones', parts[1]);
		} else {
			dialog.close();
		}
	},
	ajax(params, callback, target = null) {
		const $target = target ? $(target) : null;
		if ($target?.hasClass('spinner')) return;
		const request = { params, callback, target };
		this.retry = request;
		const isCount = params.includes('action=count');
		if ($target) {
			$target.addClass('spinner');
		}
		$('#loading').fadeIn(250);
		$.post(`${route.url}/notificaciones-ajax.php`, params.join('&') + queryParam('key'), response => {
			if ($target) {
				$target.removeClass('spinner');
			}
			callback(response, target);
		}).fail(() => {
			if (!isCount) {
				dialog.reintentar(`notifica.ajax(${JSON.stringify(this.retry.params)})`);
			}
		}).always(() => dialog.close);
	},
	follow(type, id, cb, obj) {
		this.ajax(['action=follow', `type=${type}`, `obj=${id}`], cb, obj);
	},
	unfollow(type, id, cb, obj) {
		this.ajax(['action=unfollow', `type=${type}`, `obj=${id}`], cb, obj);
	},
	spam(id, cb, param) {
		this.ajax(['action=spam', `${param}=${id}`], cb);
	},
	handleRecomendar(id, type) {
		dialog.init({
			title: 'Recomendar',
			boody: `¿Quieres recomendar este ${type} a tus seguidores?`,
			buttons: {
				confirm: {
					text: 'Recomendar',
					action: () => notifica.spam(id, notifica.spamHandle, `${type}id`)
				}
			}
		});
	},
	last() {
		const $list = $('#mon_list');
		const $monitor = $('a[name=Monitor]');
		const count = parseInt($('#alerta_mon > a > span').text(), 10) || 0;
		mensaje.close();
		// Si está visible → cerrar
		if ($list.is(':visible')) {
			$list.fadeOut();
			$monitor.parent('li').removeClass('monitor-notificaciones');
			return;
		}
		const hasCache = this.cache.last !== undefined;
		// Mostrar panel
		$monitor.children('span').addClass('spinner');
		$monitor.parent('li').addClass('monitor-notificaciones');
		$list.slideDown();
		// Pedir datos si hace falta
		if (!hasCache || count > 0) {
			this.ajax(['action=last'], response => {
				this.cache.last = response;
				this.show();
			});
		} else {
			this.show();
		}
	},
	check() {
		this.ajax(['action=count'], notifica.popup);
	},
	popup(response) {
		media.popup(response, 'mon');
	},
	show() {
		media.show(notifica.cache.last, 'Monitor', 'mon');
	},
	filter(x, obj) {
		let fid = [];
		let inputs = $('.check-filter input');
		inputs.map((pos, input) => {
			if($(input).prop('checked')) fid.push(input.id)
		})
		$.post(`${route.url}/notificaciones-filtro.php`, { fid })
		.fail(() => console.error('Error al filtrar notificaciones'));  
	},
	close() {
		media.close('Monitor', 'mon');
	}
}

/* Mensajes */
const mensaje = {
	save: {},
	cache: {},
	vars: [],
	// CREAR HTML
	form() {
		const { to, sub, msg, error } = this.save;
		let html = '';
		if(error) {
			html += `<div class="emptyData">${error}</div>`;
		}
		html += `<div style="display:grid;grid-template-columns:80px 1fr;gap:.5rem">
			<div class="m-col1">Para:</div>
			<div class="m-col2">
				<input type="text" value="${to ?? ''}" maxlength="16" id="msg_to" name="msg_to" style="width:95%;"/> 
				<span style="font-size: 10px;">(Ingrese el nombre de usuario)</span>
			</div>
			<div class="m-col1">Asunto:</div>
			<div class="m-col2">
				<input type="text" value="${sub ?? ''}" maxlength="100" id="msg_subject" name="msg_subject" style="width:95%;"/>
			</div>
			<div class="m-col1">Mensaje:</div>
			<div class="m-col2">
				<textarea rows="10" id="msg_body" name="msg_body" style="height:100px; width:95%;">${msg ?? ''}</textarea>
			</div>
		</div>`
		return html;                          
	},
	// FUNCIONES AUX
	checkform(response) {
		const parse = parseInt(response);
		if(parse === 0) {
			mensaje.enviar(1);
		} else if(parse === 1 || parse === 2) {
			const msg = parse === 1 ? 'No es posible enviarse mensajes a s&iacute; mismo.' : 'Este usuario no existe. Por favor, verif&iacute;calo.';
			mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], msg);
		}   
	},
	alert(response) {
		dialog.alert('Aviso', response);  
	},
	eliminar(id, type) {
		mensaje.ajax('editar', 'ids=' + id + '&act=delete', function(){
			if(parseInt(type) === 2) {
				location.href = route.url + '/mensajes/';
			}
			const cid = id.split(':');
			$('#mp_' + cid[0]).remove(); 
		});
	},
	marcar(mid, type, active, mark, obj) {
		const action = (active === 0) ? 'read' : 'unread';
		const show = (active === 0) ? 'unread' : 'read';
		// originalmente era asi mid:type, pero lo separe!
		const ids = `${mid}:${type}`;
		mensaje.ajax('editar', `ids=${ids}&act=${action}`, function() {
			if(mark !== 1) {
				location.href = route.url + '/mensajes/';
			}
			// CAMBIAR ENTRE LEIDO Y NO LEIDO
			const cid = id.split(':');
			$('#mp_' + mid)[(action === 'read' ? 'removeClass' : 'addClass')]('unread');
			//
			$(obj).parent().find('a').hide();
			$(obj).parent().find('.' + show).show();
		
		});
	},
	// POST
	ajax(action, params, fn) {
		$('#loading').fadeIn(250);
		$.post(`${route.url}/mensajes-${action}.php`, params, response => {
			fn(response);
			$('#loading').fadeOut(350);
		});
	},
	// PREPARAR EL ENVIO
	nuevo(to, sub, msg, error = '') {
		Object.assign(this.save, { to, sub, msg, error });
		dialog.init({
			title: 'Nuevo mensaje',
			body: this.form(),
			buttons: {
				confirm: { text: 'Enviar', action: () => mensaje.enviar(0) },
				cancel: { text: 'Cancelar', action: 'close' }
			}
		});
	},
	// ENVIAR...
	enviar(enviar) {
		// DATOS
		Object.assign(this.save, {
			to: $('#msg_to').val(),
			sub: $('#msg_subject').val(),
			msg: $('#msg_body').val()
		});
		// COMPROBAR
		if(enviar === 0) {
			if(!this.save.to || !this.save.msg) {
				return this.nuevo(this.save.to, this.save.sub, this.save.msg, (!this.save.to ? 'Especifique destinatario.' : 'El mensaje está vacío.'));
			}
			dialog.loading('Verificando...');
			this.ajax('validar', `para=${this.save.to}`, this.checkform);
		} else {
			dialog.loading('Enviando...');
			this.ajax('enviar', `para=${this.save.to}&asunto=${encodeURIComponent(this.save.sub)}&mensaje=${encodeURIComponent(this.save.msg)}`, this.alert);
		}
	},
	// RESPONDER
	responder(mp_id) {
		this.vars['mp_id'] = $('#mp_id').val();
		this.vars['mp_body'] = encodeURIComponent($('#respuesta').bbcode());
		if(this.vars['mp_body'] === '') {
			$('#respuesta').focus();
			return;
		}
		//
		this.ajax('respuesta', `id=${this.vars['mp_id']}&body=${this.vars['mp_body']}`, response => {
			const { status, message } = $.parseResponse(response);
			$('#respuesta').val('');
			if(status === 0) dialog.alert("Error", message);
			if(status === 1) $('#historial').append($(message).fadeIn('slow'));
			$('#respuesta').focus();
		});
	},
	last() {
		const $list = $('#mp_list');
		const $mensage = $('a[name=Mensajes]');
		const count = parseInt($('#alerta_mps > a > span').text(), 10) || 0;
		notifica.close();
		// Si está visible → cerrar
		if ($list.is(':visible')) {
			$list.fadeOut();
			$mensage.parent('li').removeClass('monitor-notificaciones');
			return;
		}
		const hasCache = this.cache.last !== undefined;
		// Mostrar panel
		$mensage.children('span').addClass('spinner');
		$mensage.parent('li').addClass('monitor-notificaciones');
		$list.slideDown();
		// Pedir datos si hace falta
		if (!hasCache || count > 0) {
			this.ajax('lista', '', response => {
				this.cache.last = response;
				this.show();
			});
		} else {
			this.show();
		}
	},
	popup(response) {
		media.popup(response, 'mps');
	},
	show() {
		media.show(mensaje.cache.last, 'Mensajes', 'mp');
	},
	close() {
		media.close('Mensajes', 'mp');
	}
}

/* IMAGENES */
const imagenes = {
	total: 0,
	offset: -250,
	delay: 5000,
	$container: $('#imContent'),
	presentacion() {
		this.$container.animate({ top: '0px' }, 1000, 'easeOutQuad', () => {
			this.$container.css({ top: `${this.offset}px` });
			for (let i = this.total; i > 0; i--) {
				$(`#img_${i}`).html($(`#img_${i - 1}`).html());
			}
			$(`#img_0`).html($(`#img_${this.total}`).html());
			setTimeout(() => this.presentacion(), this.delay);
		});
	}
};

// NEWS
const news = {
	total: 0,
	current: 1,
	delay: 7000,
	$items: $('#top_news > li'),
	slider() {
		if (this.total <= 1) return;
		this.current = this.current < this.total ? this.current + 1 : 1;
		this.$items.hide();
		$(`#new_${this.current}`).fadeIn();
		setTimeout(() => this.slider(), this.delay);
	}
};

const closeIfClickOutside = ({ panel, trigger, onClose }, $target) => {
	if (panel.is(':visible') && !$target.closest(panel).length && !$target.closest(trigger).length) {
		onClose();
	}
};

$(document).ready(() => {
	$('body').off('click.uiClose').on('click.uiClose', e => {
		const $target = $(e.target);
		// Notificaciones
		closeIfClickOutside({ panel: $('#mon_list'), trigger: 'a[name=Monitor]', onClose: () => notifica.last() }, $target);
		// Mensajes
		closeIfClickOutside({ panel: $('#mp_list'), trigger: 'a[name=Mensajes]', onClose: () => mensaje.last() }, $target);
	});

	/* NOTICIAS */
	news.total = $('#top_news > li').length;
	news.slider();
	/* IMAGENES */
	imagenes.presentacion();
	notifica.popup(global_data.notifica);
	mensaje.popup(global_data.mensaje);
});