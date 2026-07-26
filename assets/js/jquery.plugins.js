'use strict';

function empty(val) {
	if (val == null || val === false || val !== val) return true;
	if (typeof val === 'string') return val === '' || val === '0';
	if (typeof val === 'number' || typeof val === 'bigint') return val == 0;
	if (Array.isArray(val)) return val.length === 0;
	if (typeof val === 'object') return Object.keys(val).length === 0;
	return false;
}

/**
 * htmlspecialchars_decode() — equivalente a PHP
 * Convierte &amp; &lt; &gt; &quot; &#039; → & < > " '
 */
function htmlspecialchars_decode(str) {
	if (typeof str !== 'string' || str.indexOf('&') === -1) return str;
	return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#039;/g, "'");
}

/**
 * number_format() — equivalente a PHP
 * @param {number} num
 * @param {number} decimals       — cifras decimales (default 0)
 * @param {string} decPoint       — separador decimal (default '.')
 * @param {string} thousandsSep   — separador de miles (default ',')
 */
function number_format(num, decimals = 0, decPoint = '.', thousandsSep = ',') {
	const fixed = Math.abs(Number(num)).toFixed(decimals);
	const [int, dec] = fixed.split('.');
	const intFmt = int.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
	const result = dec !== undefined ? intFmt + decPoint + dec : intFmt;
	return num < 0 ? '-' + result : result;
}

jQuery.extend(jQuery.easing, {
	easeOutBounce: function(x, t, b, c, d) {
		if ((t /= d) < (1 / 2.75)) {
			return c * (7.5625 * t * t) + b;
		} else if (t < (2 / 2.75)) {
			return c * (7.5625 * (t -= (1.5 / 2.75)) * t + .75) + b;
		} else if (t < (2.5 / 2.75)) {
			return c * (7.5625 * (t -= (2.25 / 2.75)) * t + .9375) + b;
		} else {
			return c * (7.5625 * (t -= (2.625 / 2.75)) * t + .984375) + b;
		}
	}
});

$.parseResponse = (request) => {
	const sepIndex = request.indexOf(':');
	if (sepIndex === -1) return {
		status: 0,
		message: request
	}; // fallback
	return {
		status: parseInt(request.substring(0, sepIndex), 10),
		message: request.substring(sepIndex + 1).trim()
	};
};

const youtubeId = (url) => {
	// Validación estricta de entrada (OWASP Input Validation)
	if (typeof url !== 'string' || !url.trim()) {
		console.error('YouTube ID extractor: Entrada inválida. Se requiere URL string no vacía');
		return false;
	}
	const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e|embed|watch)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
	const match = url.match(regExp);
	// Validación explícita del ID (11 caracteres válidos de YouTube)
	return (match && match[1] && match[1].length === 11) ? match[1] : false;
};

const dialog = {
	default: {
		show: true,
		backdrop: true,
		maskClose: true,
		buttonClose: false,
		classAux: '',
		title: '',
		body: '',
		loading: false,
		buttons: {
			confirm: {
				action: 'close',
				text: 'Aceptar'
			},
			cancel: {
				action: 'close',
				text: 'Cerrar'
			}
		}
	},
	template: `<div class="dialog-mask" role="dialog" aria-modal="true"><div class="dialog" id="dialog-panel"><div class="dialog-header"><h3 id="dialog-title"></h3></div><div class="dialog-body" id="dialog-body"></div><div class="dialog-footer"></div></div></div>`,
	config: {},
	previousFocus: null,
	_keydownHandler: null,
	open() {
		if ($('.dialog-mask').length) {
			$('#dialog-title').text('');
			$('#dialog-body').empty();
			$('.dialog-footer').empty();
			return;
		}
		this.previousFocus = document.activeElement;
		$('body').append(this.template);
		const mask = $('.dialog-mask')[0];
		const panel = $('#dialog-panel')[0];

		this._keydownHandler = (e) => {
			if (e.key === 'Escape') {
				if (this.config.maskClose) { this.close(); }
				return;
			}
			if (e.key !== 'Tab') return;
			const focusable = panel.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
			if (!focusable.length) return;
			const first = focusable[0];
			const last = focusable[focusable.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault(); last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault(); first.focus();
			}
		};
		mask.addEventListener('keydown', this._keydownHandler);

		if (this.config.maskClose) {
			$('.dialog-mask').on('click', e => {
				if (e.target === mask) this.close();
			});
		}
	},
	close() {
		const mask = $('.dialog-mask')[0];
		if (mask && this._keydownHandler) {
			mask.removeEventListener('keydown', this._keydownHandler);
			this._keydownHandler = null;
		}
		$('.dialog-mask').remove();
		if (this.previousFocus && typeof this.previousFocus.focus === 'function') {
			this.previousFocus.focus();
			this.previousFocus = null;
		}
	},
	header(title) {
		const btnClose = this.config.buttonClose ? `<button class="dialog-close" aria-label="Cerrar">&times;</button>` : '';
		$('#dialog-title').text(title);
		if (btnClose) {
			$('.dialog-header').append(btnClose);
			$('.dialog-close').on('click', () => this.close());
		}
	},
	body(content) {
		$('#dialog-body').html(content);
	},
	footer(buttons) {
		let html = '';
		Object.entries(buttons).forEach(([key, btn]) => {
			html += `<button class="btn-${key}">${btn.text}</button>`;
		});
		$('.dialog-footer').html(html);
		Object.entries(buttons).forEach(([key, btn]) => {
			$(`.btn-${key}`).on('click', () => {
				if (btn.action === 'close') this.close();
				else if (typeof btn.action === 'function') btn.action();
			});
		});
	},
	loadingView() {
		$('#dialog-body').html(`<div class="dialog-loading" aria-live="polite" role="status"><span class="loading-spinner" aria-hidden="true"></span><p>Cargando...</p></div>`);
	},
	init(args = {}) {
		this.config = {
			...this.default,
			...args,
			buttons: {
				...this.default.buttons,
				...args.buttons
			}
		};
		if (!this.config.show) return;
		this.open();
		$('.dialog').addClass(this.config.classAux);
		const mask = $('.dialog-mask')[0];
		if (this.config.title) {
			mask.setAttribute('aria-labelledby', 'dialog-title');
			this.header(this.config.title);
		} else {
			mask.removeAttribute('aria-labelledby');
		}
		if (this.config.loading) {
			mask.setAttribute('aria-describedby', 'dialog-body');
			this.loadingView();
			const firstBtn = document.querySelector('.dialog-footer button');
			if (firstBtn) firstBtn.focus();
			else $('#dialog-panel').focus();
			return;
		}
		if (this.config.body) {
			this.body(this.config.body);
			mask.setAttribute('aria-describedby', 'dialog-body');
		}
		if (this.config.buttons) this.footer(this.config.buttons);
		const firstBtn = document.querySelector('.dialog-footer button');
		if (firstBtn) firstBtn.focus();
		else $('#dialog-panel').attr('tabindex', '-1').focus();
	},
	easy(title, body, text, action = 'close') {
		this.init({
			title,
			body,
			buttons: {
				confirm: {
					text,
					action
				}
			}
		});
	},
	alert(title, body, reload = false, buttons = null) {
		this.close();
		this.init({
			title,
			body,
			buttons: buttons || {
				confirm: {
					text: 'Aceptar',
					action: 'close'
				}
			}
		});
		if (reload) {
			setTimeout(() => location.reload(), 1500);
		}
	},
	loading(body = 'Procesando', title = 'Espere...') {
		this.close();
		this.init({
			title,
			body,
			loading: true,
			buttonClose: false,
			maskClose: false
		});
	},
	reintentar(reintentar) {
		setTimeout(function() {
			dialog.close();
			dialog.init({
				title: 'Error',
				body: 'Error al intentar procesar lo solicitado',
				buttons: {
					confirm: {
						text: 'Reintentar',
						action: () => reintentar
					},
					cancel: {
						text: 'Cancelar',
						action: 'close'
					}
				}
			});
		}, 200);
	}
};

// complemento de dialog
dialog.toast = function(options = {}) {
		dialog.close();
		const config = {
			type: 'info',
			title: '',
			message: '',
			duration: 3000,
			position: 'top-right',
			...options
		};
		let $container = $(`.dialog-toast-container.toast-${config.position}`);
		if (!$container.length) {
			$container = $(`<div class="dialog-toast-container toast-${config.position}" role="status" aria-live="polite"></div>`);
			$('body').append($container);
		}
		const $toast = $(`<div class="dialog-toast ${config.type}">${config.title ? `<h4>${config.title}</h4>` : ''}<div>${config.message}</div></div>`);
	$container.append($toast);
	$toast[0].focus();
	setTimeout(() => {
		$toast.css('animation', 'toastOut 0.2s ease forwards');
		setTimeout(() => $toast.remove(), 200);
	}, config.duration);
};

const api = (page, param, success, options = {}) => {
	const settings = {
		url: `${route.url}/${page}`,
		type: (options.method || 'POST').toUpperCase(),
		data: param,
		dataType: options.type || 'text',
		timeout: options.timeout || 10000,
		headers: { 'X-CSRF-Token': global_data?.csrf_token ?? '', ...(options.headers || {}) },
		success: response => {
		 	success(response);
		 	$('#loading').fadeOut(350);
	  	},
		error: (xhr, status, error) => {
			if (status === 'error' && xhr?.status === 419) {
				dialog.alert('Sesión expirada', 'Por seguridad, tu sesión ha expirado. Recarga la página e intenta de nuevo.');
				$('#loading').fadeOut(350);
				if (options.error) options.error({ xhr, status, error });
				return;
			}
			if (options.error) {
				options.error({ xhr, status, error });
				$('#loading').fadeOut(350);
			}
		},
		beforeSend: options.beforeSend || (() => $('#loading').fadeIn(350))
	};
	// Solo permitir GET o POST
	if (!['GET', 'POST'].includes(settings.type)) {
	 	settings.type = 'POST';
	}
	// Inyectar CSRF en el body para POST cuando data es un objeto plano
	if (settings.type !== 'GET' && settings.data && typeof settings.data === 'object'
		&& !(settings.data instanceof FormData) && global_data?.csrf_token) {
		settings.data = { ...settings.data, csrf_token: global_data.csrf_token };
	}
	return $.ajax(settings);
};

function initLazyLoading() {
	const observer = new IntersectionObserver((entries, self) => {
		entries.forEach((entry) => {
			if (!entry.isIntersecting) return;
			const img = entry.target;
			// Primero activar todos los <source> del <picture> padre
			const picture = img.closest('picture');
			if (picture) {
				picture.querySelectorAll('source[data-srcset]').forEach(source => {
					source.srcset = source.getAttribute('data-srcset');
					source.removeAttribute('data-srcset');
				});
			}
			// Luego activar el <img> (dispara la evaluación de <picture>)
			const dataSrc = img.getAttribute('data-src');
			if (dataSrc) {
				img.src = dataSrc;
				img.removeAttribute('data-src');
			}
			self.unobserve(img);
		});
	}, { rootMargin: '200px' });
	// Solo observar el <img>, él arrastra a sus <source>
	document.querySelectorAll('picture img[data-src]').forEach(function(img) {
	  	img.onerror = function () {
		 	this.onerror = null;
		 	this.src = this.dataset.fallbackPng;
		 	const p = this.closest('picture');
		 	if (p) {
				const sources = p.querySelectorAll('source');
				if (sources[0]) sources[0].srcset = this.dataset.fallbackAvif;
				if (sources[1]) sources[1].srcset = this.dataset.fallbackWebp;
		 	}
	  	};
	  	observer.observe(img);
	});
}
initLazyLoading();

// Solo ejecutar si hay bloques pendientes
if (document.querySelector('[data-bbcode-code]')) {
	loadHighlightJS().then(() => {
		// Resaltar solo bloques NO procesados
		document.querySelectorAll('pre code[data-bbcode-code]').forEach(el => {
			if (!el.hasAttribute('data-highlighted')) {
				hljs.highlightElement(el);
			}
		});
	});
}

function loadHighlightJS() {
	return new Promise((resolve) => {
		if (window.hljs) return resolve();

		const link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11/build/styles/default.min.css';
		document.head.appendChild(link);

		const script = document.createElement('script');
		script.src = 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11/build/highlight.min.js';
		script.onload = () => {
			// Asegurar que hljs esté listo
			if (typeof hljs !== 'undefined') {
				hljs.configure({ ignoreUnescapedHTML: true });
			}
			resolve();
		};
		document.head.appendChild(script);
	});
}
