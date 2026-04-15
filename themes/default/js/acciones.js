'use strict';
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
	userid: 'user_key',
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
			`).appendTo(`.navbar-user .${clase}`);
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
		$ref.parent('div').addClass(name.toLowerCase() + '-notificaciones');
		$ref.children('span').removeClass('spinner');
		if (!last) return;
		// Mostrar lista y rellenar contenido
		$list.show().children(`[dropdown-open=${name}]`).html(last);
	},
	close(name, short) {
		const $list = $(`#${short}_list`);
		const $ref = $(`a[name=${name}]`);
		$list.hide();
		$ref.parent('div').removeClass(`${name.toLowerCase()}-notificaciones`);
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
		$(block).html(value);
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
		notifica.handleResponse(response, res => {
			const cache_id = 'following_' + res.id;
			notifica.cache[cache_id] = 0;
			$('div.avatar-box').children('ul').hide();
		});
	},
	userInPostHandle(response) {
		notifica.handleResponse(response, res => {
			notifica.handleNumber('[data-count-follow]', res.value);
			notifica.userMenuHandle(response);
		});
	},
	userInMonitorHandle(response, obj) {
		notifica.handleResponse(response, () => $(obj).fadeOut(() => $(obj).remove()));	
	},
	inPostHandle(response) {
		notifica.handleResponse(response, res => {
			notifica.handleNumber('[data-post-follow]', res.value);
		});
	},
	inComunidadHandle(response) {
		notifica.handleResponse(response, res => {
			$('.follow_comunidad, .unfollow_comunidad').toggle();
			notifica.handleNumber('.comunidad_seguidores', res.value, 'Seguidores');
		});
	},
	temaInComunidadHandle(response) {
		notifica.handleResponse(response, res => {
			$('.followBox > .follow_tema, .unfollow_tema').toggle();
			notifica.handleNumber('.tema_notifica_count', res.value, 'Seguidores');
		});
	},
	ruserInAdminHandle(response) {
		notifica.handleResponse(response, res => $('.ruser' + res.id).toggle());
	},
	listInAdminHandle(response) {
		notifica.handleResponse(response, res => {
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
	follow({ action, type, id, fn, obj }) {
		notifica.ajax([`action=${action}`, `type=${type}`, `obj=${id}`], fn, obj);
	},
	spam(id, cb, param) {
		notifica.ajax(['action=spam', `${param}=${id}`], cb);
	},
	ajax(params, callback, target = null) {
		const $target = target ? $(target) : null;
		if ($target?.hasClass('spinner')) return;
		const request = { params, callback, target };
		notifica.retry = request;
		const isCount = params.includes('action=count');
		if ($target) {
			$target.addClass('spinner');
		}
		api('notificaciones-ajax', params.join('&') + queryParam('userkey'), response => {
			if ($target) {
				$target.removeClass('spinner');
			}
			callback(response, target);
		}, {
			error: ({ xhr, status, error }) => {
				if (!isCount) {
					dialog.reintentar(`notifica.ajax(${JSON.stringify(notifica.retry.params)})`);
				}
			}
		});
	},
	handleRecomendar(id, type) {
		dialog.easy('Recomendar', `¿Quieres recomendar este ${type} a tus seguidores?`, 'Recomendar', () => notifica.spam(id, notifica.spamHandle, `${type}id`))
	},
	last() {
		const $list = $('#mon_list');
		const $monitor = $('a[name=Monitor]');
		const count = parseInt($('#alerta_mon > a > span').text(), 10) || 0;
		mensaje.close();
		usuario.close();
		// Si está visible → cerrar
		if ($list.is(':visible')) {
			$list.fadeOut();
			$monitor.parent('div').removeClass('monitor-notificaciones');
			return;
		}
		const hasCache = notifica.cache.last !== undefined;
		// Mostrar panel
		$monitor.children('span').addClass('spinner');
		$monitor.parent('div').addClass('monitor-notificaciones');
		$list.slideDown();
		// Pedir datos si hace falta
		if (!hasCache || count > 0) {
			notifica.ajax(['action=last'], response => {
				notifica.cache.last = response;
				notifica.show();
			});
		} else {
			notifica.show();
		}
	},
	check() {
		notifica.ajax(['action=count'], notifica.popup);
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
			if($(input).prop('checked')) fid.push($(input).data('type'))
		})
		$.post(`${route.url}/notificaciones-filtro`, { fid })
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
		const { to, sub, msg, error } = mensaje.save;
		let html = '';
		if(error) {
			html += `<div class="alert-empty">${error}</div>`;
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
			mensaje.nuevo(mensaje.save.to, mensaje.save.sub, mensaje.save.msg, msg);
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
	  	mensaje.ajax('editar', { ids: `${mid}:${type}`, act: action }, function() {
	  	   if (mark !== 1) {
	  	      location.href = route.url + '/mensajes/';
	  	   }
	  	   $('#mp_' + mid)[(action === 'read' ? 'removeClass' : 'addClass')]('unread');
	  	   $(obj).parent().find('span.marcar').hide();
	  	   $(obj).parent().find('span.marcar.' + (active === 0 ? 'unread' : 'read')).show();
	  	});
	},
	// POST
	ajax(action, params, fn) {
		api(`mensajes-${action}`, params, response => {
			fn(response);
		});
	},
	// PREPARAR EL ENVIO
	nuevo(to, sub = '', msg = '', error = '') {
		dialog.easy('Nuevo mensaje', mensaje.form(), 'Enviar', () => mensaje.enviar(0))
	},
	// ENVIAR...
	enviar(enviar) {
		// DATOS
		if (enviar === 0) {
			Object.assign(mensaje.save, {
				to: $('#msg_to').val(),
				sub: $('#msg_subject').val(),
				msg: $('#msg_body').val()
			});
		}
		// COMPROBAR
		if(enviar === 0) {
			if(!mensaje.save.to || !mensaje.save.msg) {
				return mensaje.nuevo(mensaje.save.to, mensaje.save.sub, mensaje.save.msg, (!mensaje.save.to ? 'Especifique destinatario.' : 'El mensaje está vacío.'));
			}
			dialog.loading('Verificando...');
			mensaje.ajax('validar', `para=${mensaje.save.to}`, mensaje.checkform);
		} else {
			dialog.loading('Enviando...');
			mensaje.ajax('enviar', `para=${mensaje.save.to}&asunto=${encodeURIComponent(mensaje.save.sub)}&mensaje=${encodeURIComponent(mensaje.save.msg)}`, mensaje.alert);
			mensaje.save = {};
		}
	},
	// RESPONDER
	responder(mp_id) {
		mensaje.vars['mp_id'] = $('#mp_id').val();
		mensaje.vars['mp_body'] = encodeURIComponent($('#respuesta').val());
		if(mensaje.vars['mp_body'] === '') {
			$('#respuesta').focus();
			return;
		}
		//
		mensaje.ajax('respuesta', `id=${mensaje.vars['mp_id']}&body=${mensaje.vars['mp_body']}`, response => {
			const { status, message } = $.parseResponse(response);
			$('#respuesta').val('');
			if(status === 0) dialog.alert("Error", message);
			if(status === 1) $('#historial').append($(message).fadeIn('slow'));
			$('#respuesta').focus();
		});
	},
	last() {
		const $list = $('#mp_list');
		const $mensaje = $('a[name=Mensajes]');
		const count = parseInt($('#alerta_mps > a > span').text(), 10) || 0;
		notifica.close();
		usuario.close();
		// Si está visible → cerrar
		if ($list.is(':visible')) {
			$list.fadeOut();
			$mensaje.parent('div').removeClass('monitor-notificaciones');
			return;
		}
		const hasCache = mensaje.cache.last !== undefined;
		// Mostrar panel
		$mensaje.children('span').addClass('spinner');
		$mensaje.parent('div').addClass('monitor-notificaciones');
		$list.slideDown();
		// Pedir datos si hace falta
		if (!hasCache || count > 0) {
			mensaje.ajax('lista', '', response => {
				mensaje.cache.last = response;
				mensaje.show();
			});
		} else {
			mensaje.show();
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

const usuario = {
	last() {
		notifica.close();
		mensaje.close();
		const $list = $('#user_list');
		if ($list.is(':visible')) {
			$list.fadeOut();
			return;
		}
		$list.slideDown();
		usuario.show();
	},
	show() {
		media.show(undefined, 'Usuario', 'user');
	},
	close() {
		media.close('Usuario', 'user');
	}
}

/* IMAGENES */
const imagenes = {
	total: 0,
	offset: -250,
	delay: 5000,
	$container: $('#imContent'),
	presentacion() {
		imagenes.$container.animate({ top: '0px' }, 1000, 'easeOutQuad', () => {
			imagenes.$container.css({ top: `${imagenes.offset}px` });
			for (let i = imagenes.total; i > 0; i--) {
				$(`#img_${i}`).html($(`#img_${i - 1}`).html());
			}
			$(`#img_0`).html($(`#img_${imagenes.total}`).html());
			setTimeout(() => imagenes.presentacion(), imagenes.delay);
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
		if (news.total <= 1) return;
		news.current = news.current < news.total ? news.current + 1 : 1;
		news.$items.hide();
		$(`#new_${news.current}`).fadeIn();
		setTimeout(() => news.slider(), news.delay);
	}
};

const closeIfClickOutside = ({ panel, trigger, onClose }, $target) => {
	if (panel.is(':visible') && !$target.closest(panel).length && !$target.closest(trigger).length) {
		onClose();
	}
};

$(document).ready(() => {

	$('body').off('click.uiClose').on('click.uiClose', e => {
		[
		   { panel: '#mon_list', trigger: 'a[name=Monitor]', onClose: () => notifica.last() },
		   { panel: '#mp_list', trigger: 'a[name=Mensajes]', onClose: () => mensaje.last() },
		   { panel: '#user_list', trigger: 'a[name=Usuario]', onClose: () => usuario.last() }
		].forEach(({ panel, trigger, onClose }) => closeIfClickOutside(
			{ panel: $(panel), trigger, onClose }, 
			$(e.target)
		));
	});

	/* NOTICIAS */
	news.total = $('#top_news > li').length;
	news.slider();
	/* IMAGENES */
	imagenes.presentacion();
	notifica.popup(global_data.notifica);
	mensaje.popup(global_data.mensaje);
});
