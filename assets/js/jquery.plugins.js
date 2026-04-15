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
	template: `<div class="dialog-mask"><div class="dialog"></div></div>`,
	config: {},
	open() {
		if ($('.dialog-mask').length) {
			$('.dialog').empty();
			return;
		}
		$('body').append(this.template);

		if (this.config.maskClose) {
			$('.dialog-mask').on('click', e => {
				if ($(e.target).is('.dialog-mask')) this.close();
			});
		}
	},
	close() {
		$('.dialog-mask').remove();
	},
	header(title) {
		const btnClose = this.config.buttonClose ? `<button class="dialog-close">&times;</button>` : '';
		const html = `<div class="dialog-header"><h3>${title}</h3>${btnClose}</div>`;
		$('.dialog').append(html);
		$('.dialog-close').on('click', () => this.close());
	},
	body(content) {
		const html = `<div class="dialog-body">${content}</div>`;
		$('.dialog').append(html);
	},
	footer(buttons) {
		let html = `<div class="dialog-footer">`;
		Object.entries(buttons).forEach(([key, btn]) => {
			html += `<button class="btn-${key}">${btn.text}</button>`;
		});
		html += `</div>`;
		$('.dialog').append(html);
		Object.entries(buttons).forEach(([key, btn]) => {
			$(`.btn-${key}`).on('click', () => {
				if (btn.action === 'close') this.close();
				else if (typeof btn.action === 'function') btn.action();
				else if (typeof btn.action === 'string') eval(btn.action);
			});
		});
	},
	loadingView() {
		const html = `<div class="dialog-loading"><span class="loading-spinner"></span><p>Cargando...</p></div>`;
		$('.dialog').append(html);
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
		if (this.config.title) this.header(this.config.title);
		if (this.config.loading) {
			this.loadingView();
			return;
		}
		if (this.config.body) this.body(this.config.body);
		if (this.config.buttons) this.footer(this.config.buttons);
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
			$container = $(`<div class="dialog-toast-container toast-${config.position}"></div>`);
			$('body').append($container);
		}
		const $toast = $(`<div class="dialog-toast ${config.type}">${config.title ? `<h4>${config.title}</h4>` : ''}<div>${config.message}</div></div>`);
	$container.append($toast);
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
	  headers: options.headers || {},
	  success: response => {
		 success(response);
		 $('#loading').fadeOut(350);
	  },
	  error: (xhr, status, error) => {
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
		link.href = 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.11.1/build/styles/default.min.css';
		document.head.appendChild(link);

		const script = document.createElement('script');
		script.src = 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.11.1/build/highlight.min.js';
		script.onload = () => {
			// Asegurar que hljs esté listo
			if (typeof hljs !== 'undefined') {
				hljs.configure({ ignoreUnescapedHTML: true }); // ⚠️ Clave: evita advertencias
			}
			resolve();
		};
		document.head.appendChild(script);
	});
}
