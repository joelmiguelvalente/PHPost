/**
 * WysiBB - Editor BBCode WYSIWYG
 * Versión modernizada con sistema de configuración modular para botones y emojis.
 *
 * =====================================================================
 *  GUÍA RÁPIDA DE CONFIGURACIÓN
 * =====================================================================
 *
 * 1. CONFIGURAR BOTONES DE LA BARRA DE HERRAMIENTAS
 * -------------------------------------------------
 * Usa WysiBBConfig.setToolbar(...) ANTES de inicializar el editor.
 *
 *   // Barra simple
 *   WysiBBConfig.setToolbar('bold,italic,underline,|,link,image,|,smileBox');
 *
 *   // Agregar un botón nuevo personalizado
 *   WysiBBConfig.addButton('miboton', {
 *     title: 'Mi Botón',
 *     buttonHTML: '<span class="wysibb-icon wysibb-icon-bold"></span>',
 *     transform: {
 *       '<span class="mi-clase">{SELTEXT}</span>': '[mitag]{SELTEXT}[/mitag]'
 *     }
 *   });
 *
 *   // Quitar un botón existente de la barra (sin borrarlo, solo ocultarlo)
 *   WysiBBConfig.removeFromToolbar('badge', 'diff');
 *
 *   // Borrar completamente la definición de un botón
 *   WysiBBConfig.deleteButton('badge');
 *
 * 2. CONFIGURAR EMOJIS / SMILES
 * -----------------------------
 * Usa WysiBBConfig.setSmiles(...) para reemplazar toda la lista,
 * o los métodos granulares para agregar/quitar individualmente.
 *
 *   // Reemplazar toda la lista de emojis
 *   WysiBBConfig.setSmiles([
 *     { title: ':grin:',  img: '<img src="/smiles/1f601.png">', bbcode: ':grin:' },
 *     { title: ':joy:',   img: '<img src="/smiles/1f602.png">', bbcode: ':joy:'  },
 *   ]);
 *
 *   // Agregar emojis a la lista existente
 *   WysiBBConfig.addSmiles([
 *     { title: ':custom:', img: '<img src="/smiles/custom.png">', bbcode: ':custom:' }
 *   ]);
 *
 *   // Quitar emojis por bbcode
 *   WysiBBConfig.removeSmiles(':poop:', ':goblin:');
 *
 *   // Cambiar la URL base de todos los emojis de golpe
 *   WysiBBConfig.setSmilesBaseUrl('/ruta/nueva/smiles');
 *
 * 3. INICIALIZAR EL EDITOR (igual que siempre)
 * ---------------------------------------------
 *   $('#miTextarea').wysibb();
 *
 *   // O pasando opciones directamente (tienen prioridad sobre WysiBBConfig)
 *   $('#miTextarea').wysibb({ bbmode: false, autoresize: true });
 *
 * =====================================================================
 */

'use strict';

// ─── Compatibilidad retroactiva ──────────────────────────────────────────────
const wysi = { on: () => {}, off: () => {} };
let debug = false;

// ─── Pantalla completa ───────────────────────────────────────────────────────
const toFullScreen = () => {
	const isFullscreen = !$('.wysibb').attr('fullscreen');
	const maxHeight = $(window).height() - $('.wysibb-toolbar').height();

	if (isFullscreen) {
		$('body').css({ overflow: 'hidden' });
	} else {
		$('body, .wysibb').removeAttr('style');
	}

	$('.wysibb')[isFullscreen ? 'addClass' : 'removeClass']('fullscreen')
		.attr({ fullscreen: isFullscreen });

	$('.wysibb-body, .wysibb-texarea').css({
		'max-height': isFullscreen ? maxHeight : 500,
		height: isFullscreen ? maxHeight : ''
	});
};

// ─── Copyright ───────────────────────────────────────────────────────────────
const Copyright = {
	app: '<span class="powered">Powered by <a href="https://github.com/joelmiguelvalente/PHPost" target="_blank" rel="external" title="Repositorio en github">PHPost V3</a></span>'
};

// ─────────────────────────────────────────────────────────────────────────────
//  WysiBBConfig  — Sistema de configuración modular (botones + emojis)
// ─────────────────────────────────────────────────────────────────────────────
const WysiBBConfig = (() => {
	// Estado interno
	let _toolbarString = null;          // null = usar el default del editor
	let _customButtons = {};            // botones nuevos/sobreescritos
	let _removedButtons = new Set();    // botones quitados de la barra
	let _deletedButtons = new Set();    // botones completamente eliminados
	let _smileList = null;              // null = usar la lista default
	let _smilesBaseUrl = null;          // null = usar route.smiles

	return {
		// ── Toolbar ────────────────────────────────────────────────────────

		/**
		 * Reemplaza toda la lista de botones de la barra.
		 * @param {string} toolbar - Lista separada por comas, usar | para separador.
		 * @example WysiBBConfig.setToolbar('bold,italic,underline,|,link,image');
		 */
		setToolbar(toolbar) {
			_toolbarString = toolbar;
			return this;
		},

		/**
		 * Agrega o sobreescribe la definición de un botón.
		 * @param {string} name - Clave del botón (en minúsculas).
		 * @param {object} definition - Objeto de configuración del botón.
		 * @example
		 * WysiBBConfig.addButton('spoiler2', {
		 *   title: 'Spoiler grande',
		 *   buttonHTML: '<span class="wysibb-icon wysibb-icon-spoiler"></span>',
		 *   transform: { '<div class="spoiler2">{SELTEXT}</div>': '[spoiler2]{SELTEXT}[/spoiler2]' }
		 * });
		 */
		addButton(name, definition) {
			_customButtons[name.toLowerCase()] = definition;
			_deletedButtons.delete(name.toLowerCase());
			return this;
		},

		/**
		 * Quita botones de la BARRA DE HERRAMIENTAS (siguen existiendo como comandos).
		 * @param {...string} names - Nombres de botones a ocultar.
		 * @example WysiBBConfig.removeFromToolbar('badge', 'diff', 'divider');
		 */
		removeFromToolbar(...names) {
			names.forEach(n => _removedButtons.add(n.toLowerCase()));
			return this;
		},

		/**
		 * Vuelve a mostrar en la barra botones previamente ocultados.
		 * @param {...string} names
		 */
		restoreToToolbar(...names) {
			names.forEach(n => _removedButtons.delete(n.toLowerCase()));
			return this;
		},

		/**
		 * Elimina completamente la definición de un botón (no aparece en barra ni funciona como comando).
		 * @param {...string} names
		 */
		deleteButton(...names) {
			names.forEach(n => {
				_deletedButtons.add(n.toLowerCase());
				_removedButtons.add(n.toLowerCase());
			});
			return this;
		},

		// ── Emojis / Smiles ────────────────────────────────────────────────

		/**
		 * Reemplaza TODA la lista de emojis.
		 * Cada item: { title: ':code:', img: '<img src="...">', bbcode: ':code:' }
		 * @param {Array} list
		 * @example
		 * WysiBBConfig.setSmiles([
		 *   { title: ':grin:', img: '<img src="/s/grin.png">', bbcode: ':grin:' }
		 * ]);
		 */
		setSmiles(list) {
			_smileList = Array.isArray(list) ? [...list] : [];
			return this;
		},

		/**
		 * Agrega emojis a la lista existente (o a la lista default si no se reemplazó antes).
		 * @param {Array} list
		 */
		addSmiles(list) {
			if (!Array.isArray(list)) return this;
			if (_smileList === null) {
				// Se fusionará con la lista default al momento de init
				_smileList = { _pending_add: list };
			} else if (_smileList._pending_add) {
				_smileList._pending_add.push(...list);
			} else {
				_smileList.push(...list);
			}
			return this;
		},

		/**
		 * Quita emojis de la lista por su bbcode.
		 * @param {...string} bbcodes - Ej: ':poop:', ':goblin:'
		 * @example WysiBBConfig.removeSmiles(':poop:', ':goblin:');
		 */
		removeSmiles(...bbcodes) {
			const toRemove = new Set(bbcodes);
			if (_smileList === null) {
				// Marcamos para filtrar en init
				_smileList = { _pending_remove: toRemove };
			} else if (_smileList._pending_remove) {
				bbcodes.forEach(c => _smileList._pending_remove.add(c));
			} else if (Array.isArray(_smileList)) {
				_smileList = _smileList.filter(s => !toRemove.has(s.bbcode));
			}
			return this;
		},

		/**
		 * Cambia la URL base de los emojis.
		 * Útil si moviste los archivos de imagen.
		 * @param {string} baseUrl - Ej: '/assets/emojis'
		 */
		setSmilesBaseUrl(baseUrl) {
			_smilesBaseUrl = baseUrl;
			return this;
		},

		// ── Internos (usados por el editor) ───────────────────────────────

		/** @internal Devuelve la toolbar configurada o null */
		getToolbar() { return _toolbarString; },

		/** @internal Devuelve botones custom/sobreescritos */
		getCustomButtons() { return { ..._customButtons }; },

		/** @internal Devuelve set de botones eliminados definitivamente */
		getDeletedButtons() { return _deletedButtons; },

		/** @internal Construye el string de toolbar filtrado */
		applyToolbarFilters(defaultToolbar) {
			let toolbar = _toolbarString || defaultToolbar;
			if (_removedButtons.size === 0) return toolbar;
			return toolbar.split(',')
				.filter(b => !_removedButtons.has(b.trim().toLowerCase()))
				.join(',');
		},

		/** @internal Resuelve la smileList final */
		resolveSmileList(defaultSmileList) {
			let base;

			if (_smileList === null) {
				base = [...defaultSmileList];
			} else if (Array.isArray(_smileList)) {
				base = [..._smileList];
			} else {
				// Objeto con pendientes
				base = [...defaultSmileList];
				if (_smileList._pending_remove) {
					const toRemove = _smileList._pending_remove;
					base = base.filter(s => !toRemove.has(s.bbcode));
				}
				if (_smileList._pending_add) {
					base.push(..._smileList._pending_add);
				}
			}

			// Cambiar baseUrl si se configuró
			if (_smilesBaseUrl) {
				base = base.map(s => {
					const newImg = s.img.replace(/src="[^"]*\/([^"/]+\.(?:png|gif|jpg|webp|svg))"/,
						`src="${_smilesBaseUrl}/$1"`);
					return { ...s, img: newImg };
				});
			}

			return base;
		},

		/** @internal Limpia toda la configuración (útil en tests) */
		reset() {
			_toolbarString = null;
			_customButtons = {};
			_removedButtons = new Set();
			_deletedButtons = new Set();
			_smileList = null;
			_smilesBaseUrl = null;
		}
	};
})();

// ─────────────────────────────────────────────────────────────────────────────
//  Sync al hacer hover en botones del foro
// ─────────────────────────────────────────────────────────────────────────────
$(function() {
	$('button, input, .btn_g, .answerCitar').on('mouseenter', function() {
		if ($('.wysibb-texarea').length) {
			$('.wysibb-texarea').sync();
		}
	});
});

// ─────────────────────────────────────────────────────────────────────────────
//  Plugin principal
// ─────────────────────────────────────────────────────────────────────────────
(function($) {
	'use strict';

	$.wysibb = function(txtArea, settings) {
		$(txtArea).data('wbb', this);
		this.txtArea = txtArea;
		this.$txtArea = $(txtArea);
		const id = this.$txtArea.attr('id') || this.setUID(this.txtArea);

		// ── Barra de herramientas default ─────────────────────────────────
		const defaultToolbar = 'bold,italic,underline,strike,sup,sub,|,image,video,link,|,fontcolor,fontsize,fontfamily,|,smileBox,bullist,numlist,|,spoiler,messages,table,quote,code,kbd,|,justifyleft,justifycenter,justifyright,justify,|,highlight,mention,badge,diff,divider,|,removeFormat,fullscreen';

		this.options = {
			bbmode:            false,
			onlyBBmode:        false,
			themeName:         'default',
			bodyClass:         '',
			tabInsert:         true,
			toolbar:           true,
			hotkeys:           true,
			showHotkeys:       true,
			autoresize:        true,
			resize_maxheight:  500,
			loadPageStyles:    true,
			traceTextarea:     true,
			smileConversion:   true,
			// img upload config
			imgupload:         true,
			img_uploadurl:     (typeof route !== 'undefined' ? route.url : '') + '/inc/extras/wysibbupload.php',
			img_maxwidth:      800,
			img_maxheight:     640,
			// Barra de herramientas — puede ser sobreescrita por WysiBBConfig
			buttons: WysiBBConfig.applyToolbarFilters(defaultToolbar),

			// ── Definiciones de botones ───────────────────────────────────
			allButtons: {
				bold: {
					title: 'Negrita',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-bold"></span>',
					excmd: 'bold',
					hotkey: 'ctrl+b',
					transform: {
						'<b>{SELTEXT}</b>':       '[b]{SELTEXT}[/b]',
						'<strong>{SELTEXT}</strong>': '[b]{SELTEXT}[/b]'
					}
				},
				italic: {
					title: 'Cursiva',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-italic"></span>',
					excmd: 'italic',
					hotkey: 'ctrl+i',
					transform: {
						'<i>{SELTEXT}</i>':   '[i]{SELTEXT}[/i]',
						'<em>{SELTEXT}</em>': '[i]{SELTEXT}[/i]'
					}
				},
				underline: {
					title: 'Subrayado',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-underline"></span>',
					excmd: 'underline',
					hotkey: 'ctrl+u',
					transform: { '<u>{SELTEXT}</u>': '[u]{SELTEXT}[/u]' }
				},
				strike: {
					title: 'Tachado',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-strike"></span>',
					excmd: 'strikeThrough',
					transform: {
						'<strike>{SELTEXT}</strike>': '[s]{SELTEXT}[/s]',
						'<s>{SELTEXT}</s>':           '[s]{SELTEXT}[/s]'
					}
				},
				sup: {
					title: 'Superíndice',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-sup"></span>',
					excmd: 'superscript',
					transform: { '<sup>{SELTEXT}</sup>': '[sup]{SELTEXT}[/sup]' }
				},
				sub: {
					title: 'Subíndice',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-sub"></span>',
					excmd: 'subscript',
					transform: { '<sub>{SELTEXT}</sub>': '[sub]{SELTEXT}[/sub]' }
				},
				highlight: {
					type: 'colorpicker',
					valueBBname: 'highlight',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-highlight"></span>',
					excmd: 'backColor',
					subInsert: true,
					colors: '#FFF176,#C8E6C9,#BBDEFB,#FCE4EC,#FFE0B2',
					title: 'Resaltar texto',
					transform: {
						'<mark class="bbc-highlight" data-color="{COLOR}">{SELTEXT}</mark>':
							'[highlight={COLOR}]{SELTEXT}[/highlight]'
					}
				},
				mention: {
					title: 'Mencionar usuario',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-mention"></span>',
					subInsert: true,
					modal: {
						title: 'Mencionar usuario',
						width: '400px',
						tabs: [{ input: [{ param: 'SELTEXT', title: 'Nombre de usuario' }] }]
					},
					transform: {
						'<span class="bbc-mention">{SELTEXT}</span>': '[mention]{SELTEXT}[/mention]'
					}
				},
				badge: {
					title: 'Etiqueta (badge)',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-alert"></span>',
					modal: {
						title: 'Insertar badge',
						width: '400px',
						tabs: [{
							input: [
								{ param: 'SELTEXT', title: 'Texto', type: 'div' },
								{ param: 'COLOR', title: 'Color (blue, green, red, yellow, gray, purple)' }
							]
						}]
					},
					transform: {
						'<span class="bbc-badge" data-color="{COLOR}">{SELTEXT}</span>': '[badge={COLOR}]{SELTEXT}[/badge]',
						'<span class="bbc-badge">{SELTEXT}</span>': '[badge]{SELTEXT}[/badge]'
					}
				},
				diff: {
					title: 'Bloque diff',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-codeview"></span>',
					onlyClearText: true,
					transform: {
						'<div class="bbc-diff">{SELTEXT}</div>': '[diff]{SELTEXT}[/diff]'
					}
				},
				divider: {
					title: 'Separador',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-hr"></span>',
					transform: {
						'<hr class="bbc-hr">':        '[divider]',
						'<hr class="bbc-hr dashed">': '[divider=dashed]',
						'<hr class="bbc-hr thick">':  '[divider=thick]'
					}
				},
				link: {
					title: 'Enlace',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-link"></span>',
					modal: {
						title: 'Insertar enlace',
						width: '500px',
						tabs: [{
							input: [
								{ param: 'SELTEXT', title: 'Texto enlazado', type: 'div' },
								{ param: 'URL', title: 'URL', validation: '^http(s)?://' }
							]
						}]
					},
					transform: {
						'<a href="{URL}">{SELTEXT}</a>': '[url={URL}]{SELTEXT}[/url]',
						'<a href="{URL}">{URL}</a>':     '[url]{URL}[/url]'
					}
				},
				image: {
					title: 'Imagen',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-img"></span>',
					modal: {
						title: 'Insertar imagen',
						width: '600px',
						tabs: [
							{
								title: 'Añadir desde URL',
								input: [
									{ param: 'SRC',     title: 'URL de la imagen', validation: '^http(s)?://.*?\\.(jpg|png|gif|jpeg|webp|avif)$' },
									{ param: 'CAPTION', title: 'Texto alternativo (Caption)', type: 'text' },
									{ param: 'WIDTH',   title: 'Ancho (opcional)', type: 'number', validation: '^[0-9]+$' },
									{ param: 'HEIGHT',  title: 'Alto (opcional)',  type: 'number', validation: '^[0-9]+$' }
								]
							},
							{
								title: 'Subir imagen',
								html: '<div id="imguploader"><form id="fupform" class="upload" action="{img_uploadurl}" method="post" enctype="multipart/form-data" target="fupload"><input type="hidden" name="iframe" value="1"/><input type="hidden" name="idarea" value="' + id + '" /><div class="fileupload"><input id="fileupl" class="file" type="file" name="img" /><button id="nicebtn" class="wbb-button">Elegir una imagen</button></div></form></div><iframe id="fupload" name="fupload" src="about:blank" frameborder="0" style="width:0;height:0;display:none"></iframe></div>'
							}
						],
						onLoad: function() {},
						onSubmit: function(cmd, opt, queryState) {
							const src     = this.$modal.find('input[name="SRC"]').val();
							const caption = this.$modal.find('input[name="CAPTION"]').val();
							const width   = this.$modal.find('input[name="WIDTH"]').val();
							const height  = this.$modal.find('input[name="HEIGHT"]').val();
							if (!src) { alert('Por favor, introduce una URL de imagen válida.'); return false; }
							let bbcode = '[image';
							const opts = [];
							if (caption) opts.push('"' + caption.replace(/"/g, '&quot;') + '"');
							if (width)   opts.push('width=' + width);
							if (height)  opts.push('height=' + height);
							if (opts.length) bbcode += '=' + opts.join(' ');
							bbcode += ']' + src + '[/image]';
							this.insertAtCursor(bbcode);
							this.closeModal();
							this.updateUI();
							return false;
						}
					},
					transform: {
						'<figure class="bbc-figure" style="width:200px;height:500px;"><img src="{SRC}" alt="{CAPTION}" width="200" height="500" /></figure>': '[image="{CAPTION}" width=200 height=500]{SRC}[/image]',
						'<img src="{SRC}" alt="{CAPTION}" />': '[image="{CAPTION}"]{SRC}[/image]',
						'<img src="{SRC}" />':                 '[image]{SRC}[/image]'
					}
				},
				bullist: {
					title: 'Lista de viñetas',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-list"></span>',
					excmd: 'insertUnorderedList',
					transform: {
						'<ul>{SELTEXT}</ul>': '[list]{SELTEXT}[/list]',
						'<l>{SELTEXT}</li>':  '[item]{SELTEXT}[/item]'
					}
				},
				numlist: {
					title: 'Lista numerada',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-numlist"></span>',
					excmd: 'insertOrderedList',
					transform: {
						'<ol>{SELTEXT}</ol>': '[list=decimal]{SELTEXT}[/list]',
						'<li>{SELTEXT}</li>': '[item]{SELTEXT}[/item]'
					}
				},
				messages: {
					type: 'select',
					title: 'Mensajes',
					options: 'notice,info,warning,error,success'
				},
				notice: {
					title: 'Noticia',
					buttonText: 'notice',
					exvalue: '1',
					transform: { '<div class="bbcmsg notice">{SELTEXT}</div>': '[notice]{SELTEXT}[/notice]' }
				},
				info: {
					title: 'Información',
					buttonText: 'info',
					exvalue: '2',
					transform: { '<div class="bbcmsg info">{SELTEXT}</div>': '[info]{SELTEXT}[/info]' }
				},
				warning: {
					title: 'Advertencia',
					exvalue: '3',
					transform: { '<div class="bbcmsg warning">{SELTEXT}</div>': '[warning]{SELTEXT}[/warning]' }
				},
				error: {
					title: 'Error',
					exvalue: '4',
					transform: { '<div class="bbcmsg error">{SELTEXT}</div>': '[error]{SELTEXT}[/error]' }
				},
				success: {
					title: 'Éxito',
					exvalue: '5',
					transform: { '<div class="bbcmsg success">{SELTEXT}</div>': '[success]{SELTEXT}[/success]' }
				},
				spoiler: {
					title: 'Spoiler',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-spoiler"></span>',
					transform: {
						'<div class="spoiler"><div class="title" contenteditable="false"><a href="#" onclick="spoiler($(this)); return false;">Spoiler:</a></div><div class="body">{SELTEXT}</div></div>':
							'[spoiler]{SELTEXT}[/spoiler]'
					}
				},
				quote: {
					title: 'Citar',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-quote"></span>',
					transform: {
						'<blockquote><div class="cita" contenteditable="false"><strong>Cita:</strong></div><div class="citacuerpo">{SELTEXT}</div></blockquote>':
							'[quote]{SELTEXT}[/quote]',
						'<blockquote><div class="cita" contenteditable="false"><strong>{AUTOR}</strong> dijo:</div><div class="citacuerpo">{SELTEXT}</div></blockquote>':
							'[quote={AUTOR}]{SELTEXT}[/quote]'
					}
				},
				code: {
					title: 'Código',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-code"></span>',
					onlyClearText: true,
					transform: { '<code>{SELTEXT}</code>': '[code]{SELTEXT}[/code]' }
				},
				kbd: {
					title: 'Teclas',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-kbd"></span>',
					onlyClearText: true,
					transform: { '<kbd class="slug">{SELTEXT}</kbd>': '[kbd]{SELTEXT}[/kbd]' }
				},
				fontcolor: {
					type: 'colorpicker',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-color"></span>',
					excmd: 'foreColor',
					valueBBname: 'color',
					subInsert: true,
					colors: '#000000,#444444,#666666,#999999,#b6b6b6,#cccccc,#d8d8d8,#efefef,#f4f4f4,#ffffff,-,#ff0000,#980000,#ff7700,#ffff00,#00ff00,#00ffff,#1e84cc,#0000ff,#9900ff,#ff00ff,-,#f4cccc,#dbb0a7,#fce5cd,#fff2cc,#d9ead3,#d0e0e3,#c9daf8,#cfe2f3,#d9d2e9,#ead1dc,#ea9999,#dd7e6b,#f9cb9c,#ffe599,#b6d7a8,#a2c4c9,#a4c2f4,#9fc5e8,#b4a7d6,#d5a6bd,#e06666,#cc4125,#f6b26b,#ffd966,#93c47d,#76a5af,#6d9eeb,#6fa8dc,#8e7cc3,#c27ba0,#cc0000,#a61c00,#e69138,#f1c232,#6aa84f,#45818e,#3c78d8,#3d85c6,#674ea7,#a64d79,#900000,#85200C,#B45F06,#BF9000,#38761D,#134F5C,#1155Cc,#0B5394,#351C75,#741B47,#660000,#5B0F00,#783F04,#7F6000,#274E13,#0C343D,#1C4587,#073763,#20124D,#4C1130',
					transform: { '<font color="{COLOR}">{SELTEXT}</font>': '[color={COLOR}]{SELTEXT}[/color]' }
				},
				table: {
					type: 'table',
					title: 'Tabla',
					cols: 10,
					rows: 10,
					cellwidth: 20,
					transform: {
						'<table class="bbc-table">{SELTEXT}</table>':   '[table]{SELTEXT}[/table]',
						'<thead class="bbc-thead">{SELTEXT}</thead>':   '[thead]{SELTEXT}[/thead]',
						'<tbody class="bbc-tbody">{SELTEXT}</tbody>':   '[tbody]{SELTEXT}[/tbody]',
						'<tr class="bbc-tr">{SELTEXT}</tr>':            '[tr]{SELTEXT}[/tr]',
						'<th class="bbc-th">{SELTEXT}</th>':            '[th]{SELTEXT}[/th]',
						'<td class="bbc-td">{SELTEXT}</td>':            '[td]{SELTEXT}[/td]'
					},
					skipRules: true
				},
				fontsize: {
					type: 'select',
					title: 'Tamaño',
					options: 'fs_verysmall,fs_small,fs_normal,fs_big,fs_verybig'
				},
				fontfamily: {
					type: 'select',
					title: 'Fuente',
					excmd: 'fontName',
					valueBBname: 'font',
					options: [
						{ title: 'Arial',               exvalue: 'Arial' },
						{ title: 'Comic Sans MS',        exvalue: 'Comic Sans MS' },
						{ title: 'Courier New',          exvalue: 'Courier New' },
						{ title: 'Georgia',              exvalue: 'Georgia' },
						{ title: 'Lucida Sans Unicode',  exvalue: 'Lucida Sans Unicode' },
						{ title: 'Tahoma',               exvalue: 'Tahoma' },
						{ title: 'Times New Roman',      exvalue: 'Times New Roman' },
						{ title: 'Trebuchet MS',         exvalue: 'Trebuchet MS' },
						{ title: 'Verdana',              exvalue: 'Verdana' }
					],
					transform: { '<font face="{FONT}">{SELTEXT}</font>': '[font={FONT}]{SELTEXT}[/font]' }
				},
				smilebox: {
					type: 'smilebox',
					title: 'Emoticonos',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-smilebox"></span>'
				},
				justify: {
					title: 'Justificar',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-justify"></span>',
					groupkey: 'align',
					transform: { '<div style="text-align:justify">{SELTEXT}</div>': '[align=justify]{SELTEXT}[/align]' }
				},
				justifyleft: {
					title: 'Alinear a la izquierda',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-textleft"></span>',
					groupkey: 'align',
					transform: { '<div style="text-align:left">{SELTEXT}</div>': '[align=left]{SELTEXT}[/align]' }
				},
				justifyright: {
					title: 'Alinear a la derecha',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-textright"></span>',
					groupkey: 'align',
					transform: { '<div style="text-align:right">{SELTEXT}</div>': '[align=right]{SELTEXT}[/align]' }
				},
				justifycenter: {
					title: 'Alinear al centro',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-textcenter"></span>',
					groupkey: 'align',
					transform: { '<div style="text-align:center">{SELTEXT}</div>': '[align=center]{SELTEXT}[/align]' }
				},
				video: {
					title: 'Insertar Vídeo',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-video"></span>',
					modal: {
						title: 'Insertar Vídeo',
						width: '600px',
						tabs: [{ title: 'Insertar Vídeo', input: [{ param: 'SRC', title: 'URL del vídeo' }] }],
						onSubmit: function(cmd, opt, queryState) {
							let url = (this.$modal.find('input[name="SRC"]').val() || '').trim();
							const patterns = {
								youtube:     /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/,
								vimeo:       /vimeo\.com\/(\d{6,10})/,
								dailymotion: /dailymotion\.com\/video\/([a-zA-Z0-9]+)/,
								tiktok:      /tiktok\.com\/@.*\/video\/(\d+)/
							};
							let match;
							for (const platform in patterns) {
								match = url.match(patterns[platform]);
								if (match) break;
							}
							if (match?.[1]) {
								this.insertAtCursor('[video]' + url + '[/video]');
							} else {
								alert('URL de vídeo no válida o plataforma no soportada');
							}
							this.closeModal();
							this.updateUI();
							return false;
						}
					},
					transform: {
						'<lite-youtube loading="lazy" videoid="{SRC}" style="width: 640px; height: 390px; background-image: url(\'https://i.ytimg.com/vi/{SRC}/maxresdefault.jpg\');"></lite-youtube>': '[video]https://youtu.be/{SRC}[/video]',
						'<div class="video-container"><iframe src="https://player.vimeo.com/video/{SRC}" width="640" height="360" frameborder="0" allowfullscreen></iframe></div>': '[video]https://vimeo.com/{SRC}[/video]',
						'<div class="video-container"><iframe frameborder="0" width="640" height="360" src="https://www.dailymotion.com/embed/video/{SRC}?autoplay=0" allow="autoplay" allowfullscreen></iframe></div>': '[video]https://dailymotion.com/video/{SRC}[/video]',
						'<blockquote class="tiktok-embed" cite="{SRC}"><a href="{SRC}"></a></blockquote><script async src="https://www.tiktok.com/embed.js"></script>': '[video]{SRC}[/video]'
					}
				},
				// Opciones de fontsize
				fs_verysmall: {
					title: 'Diminuta', buttonText: 'fs1', excmd: 'fontSize', exvalue: '1',
					transform: {
						'<span style="font-size: 10px;">{SELTEXT}</span>': '[size=10]{SELTEXT}[/size]',
						'<font size="1">{SELTEXT}</font>': '[size=10]{SELTEXT}[/size]'
					}
				},
				fs_small: {
					title: 'Pequeña', buttonText: 'fs2', excmd: 'fontSize', exvalue: '2',
					transform: {
						'<span style="font-size: 12px;">{SELTEXT}</span>': '[size=12]{SELTEXT}[/size]',
						'<font size="2">{SELTEXT}</font>': '[size=12]{SELTEXT}[/size]'
					}
				},
				fs_normal: {
					title: 'Normal', buttonText: 'fs3', excmd: 'fontSize', exvalue: '3',
					transform: {
						'<span style="font-size: 16px;">{SELTEXT}</span>': '[size=16]{SELTEXT}[/size]',
						'<font size="3">{SELTEXT}</font>': '[size=16]{SELTEXT}[/size]',
						'<span style="font-size: {SIZE}px;">{SELTEXT}</span>': '[size={SIZE}]{SELTEXT}[/size]'
					}
				},
				fs_big: {
					title: 'Grande', buttonText: 'fs4', excmd: 'fontSize', exvalue: '4',
					transform: {
						'<span style="font-size: 18px;">{SELTEXT}</span>': '[size=18]{SELTEXT}[/size]',
						'<font size="4">{SELTEXT}</font>': '[size=18]{SELTEXT}[/size]'
					}
				},
				fs_verybig: {
					title: 'Enorme', buttonText: 'fs5', excmd: 'fontSize', exvalue: '5',
					transform: {
						'<span style="font-size: 24px;">{SELTEXT}</span>': '[size=24]{SELTEXT}[/size]',
						'<font size="5">{SELTEXT}</font>': '[size=24]{SELTEXT}[/size]'
					}
				},
				fullscreen: {
					title: 'Maximizar',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-fullscreen"></span>',
					cmd: toFullScreen
				},
				removeformat: {
					title: 'Eliminar formato',
					buttonHTML: '<span class="wysibb-icon wysibb-icon-removeformat"></span>',
					excmd: 'removeFormat'
				}
			},

			// ── Transformaciones del sistema ──────────────────────────────
			systr: {
				'<br/>': '\n',
				'<span class="wbbtab">{SELTEXT}</span>': '   {SELTEXT}'
			},

			// ── Reglas custom (tabla) ─────────────────────────────────────
			customRules: {
				table: [['[table]{SELTEXT}[/table]', { seltext: { rgx: false, attr: false, sel: false } }]],
				thead: [['[thead]{SELTEXT}[/thead]', { seltext: { rgx: false, attr: false, sel: false } }]],
				tr:    [['[tr]{SELTEXT}[/tr]',       { seltext: { rgx: false, attr: false, sel: false } }]],
				th:    [['[th]{SELTEXT}[/th]',       { seltext: { rgx: false, attr: false, sel: false } }]],
				td:    [['[td]{SELTEXT}[/td]',       { seltext: { rgx: false, attr: false, sel: false } }]]
			},

			// ── Lista de emojis/smiles — resuelta a través de WysiBBConfig ─
			smileList: WysiBBConfig.resolveSmileList(_buildDefaultSmileList()),

			attrWrap: ['src', 'color', 'href']
		};

		// Aplicar botones eliminados definitivamente
		const deletedBtns = WysiBBConfig.getDeletedButtons();
		deletedBtns.forEach(k => { delete this.options.allButtons[k]; });

		// Aplicar botones custom/sobreescritos de WysiBBConfig
		Object.assign(this.options.allButtons, WysiBBConfig.getCustomButtons());

		// FIX para Opera — esperar a que el iframe cargue
		this.inited = this.options.onlyBBmode;

		// Auto-detectar themePrefix
		if (!this.options.themePrefix) {
			$('link').each($.proxy(function(idx, el) {
				const match = $(el).get(0).href.match(/(.*\/)(.*)\/wbbtheme\.css.*$/);
				if (match !== null) {
					this.options.themeName   = match[2];
					this.options.themePrefix = match[1];
				}
			}, this));
		}

		// WBBPRESET (compatibilidad retroactiva)
		if (typeof WBBPRESET !== 'undefined') {
			if (WBBPRESET.allButtons) {
				$.each(WBBPRESET.allButtons, $.proxy(function(k, v) {
					if (v.transform && this.options.allButtons[k]) {
						delete this.options.allButtons[k].transform;
					}
				}, this));
			}
			$.extend(true, this.options, WBBPRESET);
		}

		// Settings pasados directamente al constructor
		if (settings && settings.allButtons) {
			$.each(settings.allButtons, $.proxy(function(k, v) {
				if (v.transform && this.options.allButtons[k]) {
					delete this.options.allButtons[k].transform;
				}
			}, this));
		}
		$.extend(true, this.options, settings);
		this.init();
	};

	// ─────────────────────────────────────────────────────────────────────────
	//  Prototype — toda la lógica interna del editor
	// ─────────────────────────────────────────────────────────────────────────
	$.wysibb.prototype = {
		lastid: 1,

		init() {
			$.log('Init', this);

			// Detección de móvil
			this.isMobile = /android|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i
				.test(navigator.userAgent || navigator.vendor || window.opera);

			if (this.options.onlyBBmode === true) { this.options.bbmode = true; }

			this.controllers = [];

			this.options.buttons = this.options.buttons.toLowerCase();
			this.options.buttons = this.options.buttons.split(',');

			this.options.allButtons['_systr'] = {};
			this.options.allButtons['_systr']['transform'] = this.options.systr;

			this.smileFind();
			this.initTransforms();
			this.build();
			this.initModal();

			if (this.options.hotkeys === true && !this.isMobile) {
				this.initHotkeys();
			}

			if (this.options.smileList && this.options.smileList.length > 0) {
				this.options.smileList.sort((a, b) => b.bbcode.length - a.bbcode.length);
			}

			this.$txtArea.parents('form').on('submit', $.proxy(function() {
				this.sync();
				return true;
			}, this));

			this.$txtArea.parents('form')
				.find("input[id*='preview'],input[id*='submit'],input[class*='preview'],input[class*='submit'],input[name*='preview'],input[name*='submit']")
				.on('mousedown', $.proxy(function() {
					this.sync();
					setTimeout($.proxy(function() {
						if (this.options.bbmode === false) {
							this.$txtArea.removeAttr('wbbsync').val('');
						}
					}, this), 1000);
				}, this));

			if (this.options.initCallback) {
				this.options.initCallback.call(this);
			}

			$.log(this);
		},

		initTransforms() {
			$.log('Create rules for transform HTML=>BB');
			const o = this.options;
			if (!o.rules)  { o.rules = {}; }
			if (!o.groups) { o.groups = {}; }
			const btnlist = o.buttons.slice();

			btnlist.push('_systr');
			for (let bidx = 0; bidx < btnlist.length; bidx++) {
				const ob = o.allButtons[btnlist[bidx]];
				if (!ob) { continue; }
				ob.en = true;

				if (ob.simplebbcode && Array.isArray(ob.simplebbcode) && ob.simplebbcode.length === 2) {
					ob.bbcode = ob.html = ob.simplebbcode[0] + '{SELTEXT}' + ob.simplebbcode[1];
					delete ob.transform;
					delete ob.modal;
				}

				if (ob.type === 'select' && typeof ob.options === 'string') {
					ob.options.split(',').forEach(op => {
						if (btnlist.includes(op)) btnlist.push(op);
					});
				}

				if (ob.transform && ob.skipRules !== true) {
					const obtr = $.extend({}, ob.transform);
					for (let bhtml in obtr) {
						let orightml = bhtml;
						const bbcode = obtr[bhtml];

						if (!ob.bbSelector) { ob.bbSelector = []; }
						if (!ob.bbSelector.includes(bbcode)) { ob.bbSelector.push(bbcode); }

						if (this.options.onlyBBmode === false) {
							bhtml = this.wrapAttrs(bhtml);
							const $bel = $(document.createElement('DIV')).append($(this.elFromString(bhtml, document)));
							let rootSelector = this.filterByNode($bel.children());

							if (rootSelector === 'div' || typeof o.rules[rootSelector] !== 'undefined') {
								$.log('create unique selector: ' + rootSelector);
								this.setUID($bel.children());
								rootSelector = this.filterByNode($bel.children());
								$.log('New rootSelector: ' + rootSelector);
								let nhtml2 = $bel.html();
								nhtml2 = this.unwrapAttrs(nhtml2);
								const obhtml = this.unwrapAttrs(bhtml);
								ob.transform[nhtml2] = bbcode;
								delete ob.transform[obhtml];
								bhtml = nhtml2;
								orightml = nhtml2;
							}

							if (!ob.excmd) {
								if (!ob.rootSelector) { ob.rootSelector = []; }
								ob.rootSelector.push(rootSelector);
							}

							if (typeof o.rules[rootSelector] === 'undefined') {
								o.rules[rootSelector] = [];
							}
							const crules = {};

							if (bhtml.match(/\{\S+?\}/)) {
								$bel.find('*').each($.proxy(function(idx, el) {
									const attributes = this.getAttributeList(el);
									$.each(attributes, $.proxy(function(i, item) {
										let attr = $(el).attr(item);
										if (item.substr(0, 1) === '_') { item = item.substr(1); }
										const r = attr.match(/\{\S+?\}/g);
										if (r) {
											for (let a = 0; a < r.length; a++) {
												let rname = r[a].substr(1, r[a].length - 2);
												rname = rname.replace(this.getValidationRGX(rname), '');
												const p = this.relFilterByNode(el, rootSelector);
												const regRepl = (attr !== r[a]) ? this.getRegexpReplace(attr, r[a]) : false;
												crules[rname.toLowerCase()] = { sel: p ? p.trim() : false, attr: item, rgx: regRepl };
											}
										}
									}, this));

									let sl = [];
									if (!$(el).is('iframe')) {
										$(el).contents().filter(function() { return this.nodeType === 3; }).each($.proxy(function(i, rel) {
											const txt = rel.textContent || rel.data;
											if (typeof txt === 'undefined') { return true; }
											const r = txt.match(/\{\S+?\}/g);
											if (r) {
												for (let a = 0; a < r.length; a++) {
													let rname = r[a].substr(1, r[a].length - 2);
													rname = rname.replace(this.getValidationRGX(rname), '');
													const p = this.relFilterByNode(el, rootSelector);
													let regRepl = (txt !== r[a]) ? this.getRegexpReplace(txt, r[a]) : false;
													let sel = p ? p.trim() : false;
													if (sl.includes(sel) || $(rel).parent().contents().length > 1) {
														const nel = $('<span>').html('{' + rname + '}');
														this.setUID(nel, 'wbb');
														const start = (txt.indexOf(rname) + rname.length) + 1;
														const after_txt = txt.substr(start, txt.length - start);
														rel.data = txt.substr(0, txt.indexOf(rname) - 1);
														$(rel).after(this.elFromString(after_txt, document)).after(nel);
														sel = (sel ? sel + ' ' : '') + this.filterByNode(nel);
														regRepl = false;
													}
													crules[rname.toLowerCase()] = { sel, attr: false, rgx: regRepl };
													sl.push(sel);
												}
											}
										}, this));
									}
									sl = null;
								}, this));

								let nbhtml = $bel.html();
								nbhtml = this.unwrapAttrs(nbhtml);
								if (orightml !== nbhtml) {
									delete ob.transform[orightml];
									ob.transform[nbhtml] = bbcode;
									bhtml = nbhtml;
								}
							}

							o.rules[rootSelector].push([bbcode, crules]);

							if (ob.onlyClearText === true) {
								if (!this.cleartext) { this.cleartext = {}; }
								this.cleartext[rootSelector] = btnlist[bidx];
							}

							if (ob.groupkey) {
								if (!o.groups[ob.groupkey]) { o.groups[ob.groupkey] = []; }
								o.groups[ob.groupkey].push(rootSelector);
							}
						}
					}

					if (ob.rootSelector) { this.sortArray(ob.rootSelector, -1); }

					const htmll = $.map(ob.transform, (bb, html) => html).sort((a, b) => (b[0] || '').length - (a[0] || '').length);
					ob.bbcode = ob.transform[htmll[0]];
					ob.html   = htmll[0];
				}
			}

			this.options.btnlist = btnlist;
			$.extend(o.rules, this.options.customRules);

			// Smile rules
			o.srules = {};
			if (this.options.smileList) {
				$.each(o.smileList, $.proxy(function(i, sm) {
					const $sm = $(this.strf(sm.img, o));
					const f   = this.filterByNode($sm);
					o.srules[f] = [sm.bbcode, sm.img];
				}, this));
			}

			for (const rootsel in o.rules) {
				this.options.rules[rootsel].sort((a, b) => b[0].length - a[0].length);
			}

			this.rsellist = Object.keys(this.options.rules);
			this.sortArray(this.rsellist, -1);
		},

		// BUILD
		build() {
			$.log('Build editor');
			this.$editor = $('<div>').addClass('wysibb');
			if (this.isMobile) { this.$editor.addClass('wysibb-mobile'); }
			if (this.options.direction) { this.$editor.css('direction', this.options.direction); }

			this.$editor.insertAfter(this.txtArea).append(this.txtArea);
			this.startHeight = this.$txtArea.outerHeight();
			this.$txtArea.addClass('wysibb-texarea');
			this.buildToolbar();
			this.$txtArea.wrap('<div class="wysibb-text">');

			if (this.options.onlyBBmode === false) {
				const width    = this.$txtArea.outerWidth();
				const height   = this.options.minheight || this.$txtArea.outerHeight();
				const maxheight = this.options.resize_maxheight;
				const mheight  = (this.options.autoresize === true) ? this.options.resize_maxheight : height;

				this.$body = $(this.strf('<div class="wysibb-text-editor" style="width:{width}px;max-height:{maxheight}px;min-height:{height}px;"></div>', {
					width, maxheight: mheight, height
				})).insertAfter(this.$txtArea);
				this.body = this.$body[0];
				this.$txtArea.hide();

				if (height > 32) { this.$toolbar.css('max-height', height); }

				$.log('WysiBB loaded');
				this.$body.addClass('wysibb-body').addClass(this.options.bodyClass);
				if (this.options.direction) { this.$body.css('direction', this.options.direction); }

				if ('contentEditable' in this.body) {
					this.body.contentEditable = true;
					try {
						document.execCommand('StyleWithCSS', false, false);
						this.$body.append('<span></span>');
					} catch (e) {}
				} else {
					this.options.onlyBBmode = this.options.bbmode = true;
				}

				if (this.txtArea.value.length > 0) { this.txtAreaInitContent(); }

				// Limpieza al pegar desde editores externos
				this.$body.on('keydown', $.proxy(function(e) {
					if ((e.which === 86 && (e.ctrlKey || e.metaKey)) || (e.which === 45 && (e.shiftKey || e.metaKey))) {
						if (!this.$pasteBlock) {
							this.saveRange();
							this.$pasteBlock = $(this.elFromString('<div style="opacity:0;" contenteditable="true"><br></div>'));
							this.$pasteBlock.appendTo(this.body);
							setTimeout($.proxy(function() {
								this.clearPaste(this.$pasteBlock);
								let rdata = '<span>' + this.$pasteBlock.html() + '</span>';
								this.$body.attr('contentEditable', 'true');
								this.$pasteBlock.blur().remove();
								this.body.focus();
								if (this.cleartext && this.isInClearTextBlock()) {
									rdata = this.toBB(rdata).replace(/\n/g, '<br/>').replace(/\s{3}/g, '<span class="wbbtab"></span>');
								}
								rdata = rdata.replace(/\t/g, '<span class="wbbtab"></span>');
								this.selectRange(this.lastRange);
								this.insertAtCursor(rdata, false);
								this.lastRange = false;
								this.$pasteBlock = false;
							}, this), 1);
							this.selectNode(this.$pasteBlock[0]);
						}
						return true;
					}
				}, this));

				this.$body.on('keydown', $.proxy(function(e) {
					if (e.which === 13) {
						const isLi = this.isContain(this.getSelectNode(), 'li');
						if (!isLi) {
							if (e.preventDefault) { e.preventDefault(); }
							this.checkForLastBR(this.getSelectNode());
							this.insertAtCursor('<br/>', false);
						}
					}
				}, this));

				if (this.options.tabInsert === true) {
					this.$body.on('keydown', $.proxy(this.pressTab, this));
				}

				this.$body.on('mouseup keyup', $.proxy(this.updateUI, this));
				this.$body.on('mousedown', $.proxy(function(e) {
					this.clearLastRange();
					this.checkForLastBR(e.target);
				}, this));

				if (this.options.traceTextarea === true) {
					$(document).on('mousedown', $.proxy(this.traceTextareaEvent, this));
					this.$txtArea.val('');
				}

				if (this.options.hotkeys === true) {
					this.$body.on('keydown', $.proxy(this.presskey, this));
				}

				if (this.options.smileConversion === true) {
					this.$body.on('keyup', $.proxy(this.smileConversion, this));
				}

				this.inited = true;

				if (this.options.autoresize === true) {
					this.$bresize = $(this.elFromString('<div class="bottom-resize-line"></div>')).appendTo(this.$editor)
						.wdrag({ scope: this, axisY: true, height });
				}

				this.imgListeners();
			}

			this.$editor.append(Copyright.app);

			this.$txtArea.on('mouseup keyup', $.proxy(function() {
				clearTimeout(this.uitimer);
				this.uitimer = setTimeout($.proxy(this.updateUI, this), 100);
			}, this));

			if (this.options.hotkeys === true) {
				$(document).on('keydown', $.proxy(this.presskey, this));
			}
		},

		buildToolbar() {
			if (this.options.toolbar === false) { return false; }
			this.$toolbar = $('<div>').addClass('wysibb-toolbar').prependTo(this.$editor);

			let $btnContainer;
			$.each(this.options.buttons, $.proxy(function(i, bn) {
				const opt = this.options.allButtons[bn];
				if (i === 0 || bn === '|' || bn === '-') {
					if (bn === '-') { this.$toolbar.append('<div>'); }
					$btnContainer = $('<div class="wysibb-toolbar-container">').appendTo(this.$toolbar);
				}
				if (opt) {
					if      (opt.type === 'colorpicker') { this.buildColorpicker($btnContainer, bn, opt); }
					else if (opt.type === 'table')       { this.buildTablepicker($btnContainer, bn, opt); }
					else if (opt.type === 'select')      { this.buildSelect($btnContainer, bn, opt); }
					else if (opt.type === 'smilebox')    { this.buildSmilebox($btnContainer, bn, opt); }
					else                                 { this.buildButton($btnContainer, bn, opt); }
				}
			}, this));

			// Fix: ocultar tooltip en hover rápido
			this.$toolbar.find('.btn-tooltip').on('mouseenter', function() {
				$(this).parent().css('overflow', 'hidden');
			}).on('mouseleave', function() {
				$(this).parent().css('overflow', 'visible');
			});

			// Botón de cambio de modo BBCode / WYSIWYG
			const $bbsw = $(document.createElement('div'))
				.addClass('wysibb-toolbar-container modeSwitch')
				.html('<div class="wysibb-toolbar-btn mswitch" unselectable="on"><span class="btn-inner modesw" unselectable="on"><span class="wysibb-icon wysibb-icon-codeview"></span></span><span class="btn-tooltip">Ver código<ins></ins></span></div>')
				.appendTo(this.$toolbar);

			if (this.options.bbmode === true) {
				$bbsw.children('.wysibb-toolbar-btn').addClass('on');
			}
			if (this.options.onlyBBmode === false) {
				$bbsw.children('.wysibb-toolbar-btn').click($.proxy(function(e) {
					$(e.currentTarget).toggleClass('on');
					this.modeSwitch();
				}, this));
			}
		},

		buildButton(container, bn, opt) {
			if (typeof container !== 'object') { container = this.$toolbar; }
			const btnHTML = opt.buttonHTML
				? $(this.strf(opt.buttonHTML, this.options)).addClass('btn-inner')
				: this.strf('<span class="btn-inner btn-text">{text}</span>', { text: opt.buttonText.replace(/</g, '&lt;') });

			const hotkey = (this.options.hotkeys === true && this.options.showHotkeys === true && opt.hotkey)
				? ' <span class="tthotkey">[' + opt.hotkey + ']</span>' : '';

			const $btn = $('<div class="wysibb-toolbar-btn wbb-' + bn + '">').appendTo(container)
				.append(btnHTML)
				.append(this.strf('<span class="btn-tooltip">{title}<ins>{hotkey}</ins></span>', { title: opt.title, hotkey: opt.hotkey || '' }));

			this.controllers.push($btn);
			$btn.on('queryState', $.proxy(function(e) {
				this.queryState(bn) ? $(e.currentTarget).addClass('on') : $(e.currentTarget).removeClass('on');
			}, this));
			$btn.mousedown($.proxy(function(e) {
				e.preventDefault();
				this.execCommand(bn, opt.exvalue || false);
				$(e.currentTarget).trigger('queryState');
			}, this));
		},

		buildColorpicker(container, bn, opt) {
			const $btn = $('<div class="wysibb-toolbar-btn wbb-dropdown wbb-cp">').appendTo(container)
				.append('<span class="wysibb-icon wysibb-icon-color"></span><ins class="caret-down"></ins>')
				.append(this.strf('<span class="btn-tooltip">{title}<ins/></span>', { title: opt.title }));
			const $cpline   = $btn.find('.cp-line');
			const $dropblock = $('<div class="wbb-list">').appendTo($btn);
			$dropblock.append('<div class="nc">Auto</div>');
			const colorlist = opt.colors ? opt.colors.split(',') : [];
			colorlist.forEach(c => {
				c = c.trim();
				if (c === '-') { $dropblock.append('<span class="pl"></span>'); }
				else           { $dropblock.append(this.strf('<div class="sc" style="background:{color}" title="{color}"></div>', { color: c })); }
			});
			const basecolor = $(document.body).css('color');

			this.controllers.push($btn);
			$btn.on('queryState', $.proxy(function() {
				$cpline.css('background-color', basecolor);
				const r = this.queryState(bn, true);
				if (r) {
					$cpline.css('background-color', this.options.bbmode ? r.color : r);
					$btn.find('.ve-tlb-colorpick span.fonticon').css('color', this.options.bbmode ? r.color : r);
				}
			}, this));
			$btn.mousedown($.proxy(function(e) { e.preventDefault(); this.dropdownclick('.wbb-cp', '.wbb-list', e); }, this));
			$btn.find('.sc').mousedown($.proxy(function(e) {
				e.preventDefault();
				this.selectLastRange();
				this.execCommand(bn, $(e.currentTarget).attr('title'));
				$btn.trigger('queryState');
			}, this));
			$btn.find('.nc').mousedown($.proxy(function(e) {
				e.preventDefault();
				this.selectLastRange();
				this.execCommand(bn, basecolor);
				$btn.trigger('queryState');
			}, this));
			$btn.mousedown(e => { if (e.preventDefault) e.preventDefault(); });
		},

		buildTablepicker(container, bn, opt) {
			const $btn = $('<div class="wysibb-toolbar-btn wbb-dropdown wbb-tbl">').appendTo(container)
				.append('<span class="btn-inner wysibb-icon wysibb-icon-table"></span><ins class="caret-down"></ins>')
				.append(this.strf('<span class="btn-tooltip">{title}<ins/></span>', { title: opt.title }));
			const $listblock  = $('<div class="wbb-list">').appendTo($btn);
			const $dropblock  = $('<div>').css({ position: 'relative', 'box-sizing': 'border-box' }).appendTo($listblock);
			const rows = opt.rows || 10;
			const cols = opt.cols || 10;
			let allcount = rows * cols;
			$dropblock.css('height', (rows * opt.cellwidth + 2) + 'px');
			for (let j = 1; j <= cols; j++) {
				for (let h = 1; h <= rows; h++) {
					$dropblock.append('<div class="tbl-sel" style="width:' + (j * 100 / cols) + '%;height:' + (h * 100 / rows) + '%;z-index:' + (--allcount) + '" title="' + h + ',' + j + '"></div>');
				}
			}
			$btn.find('.tbl-sel').mousedown($.proxy(function(e) {
				e.preventDefault();
				const rc   = $(e.currentTarget).attr('title').split(',');
				const rows = parseInt(rc[0]);
				const cols = parseInt(rc[1]);
				let code = this.options.bbmode ? '[table]' : '<table class="bbctab">';
				code += this.options.bbmode ? '[thead][tr]' : '<thead><tr>';
				for (let j = 1; j <= cols; j++) { code += this.options.bbmode ? '[th][/th]' : '<th><br></th>'; }
				code += this.options.bbmode ? '[/tr][/thead]' : '</tr></thead>';
				code += this.options.bbmode ? '[tbody]' : '<tbody>';
				for (let i = 2; i <= rows; i++) {
					code += this.options.bbmode ? '[tr]' : '<tr>';
					for (let j = 1; j <= cols; j++) { code += this.options.bbmode ? '[td][/td]' : '<td><br></td>'; }
					code += this.options.bbmode ? '[/tr]' : '</tr>';
				}
				code += this.options.bbmode ? '[/tbody]' : '</tbody>';
				code += this.options.bbmode ? '[/table]' : '</table>';
				this.insertAtCursor(code);
			}, this));
			$btn.mousedown($.proxy(function(e) { e.preventDefault(); this.dropdownclick('.wbb-tbl', '.wbb-list', e); }, this));
		},

		buildSelect(container, bn, opt) {
			const $btn    = $('<div class="wysibb-toolbar-btn wbb-select wbb-' + bn + '">').appendTo(container)
				.append(this.strf('<span class="btn-inner wysibb-icon wysibb-icon-' + bn + '"></span><ins class="caret-down"></ins>', opt))
				.append(this.strf('<span class="btn-tooltip">{title}<ins/></span>', { title: opt.title }));
			const $sblock  = $('<div class="wbb-list">').appendTo($btn);
			const $sval    = $btn.find('span.val');
			const olist    = Array.isArray(opt.options) ? opt.options : opt.options.split(',');
			const $selectbox = this.isMobile ? $('<select>').addClass('wbb-selectbox') : '';

			for (let i = 0; i < olist.length; i++) {
				const oname = olist[i];
				if (typeof oname === 'string') {
					const option = this.options.allButtons[oname];
					if (option) {
						if (option.html) {
							$('<span>').addClass('option').attr('oid', oname).attr('cmdvalue', option.exvalue).appendTo($sblock).append(this.strf(option.html, { seltext: option.title }));
						} else {
							$sblock.append(this.strf('<span class="option" oid="' + oname + '" cmdvalue="' + option.exvalue + '">{title}</span>', option));
						}
						if (this.isMobile) {
							$selectbox.append($('<option>').attr('oid', oname).attr('cmdvalue', option.exvalue).text(option.title));
						}
					}
				} else {
					const params = { seltext: oname.title };
					params[opt.valueBBname] = oname.exvalue;
					$('<span>').addClass('option').attr('oid', bn).attr('cmdvalue', oname.exvalue).appendTo($sblock).append(this.strf(opt.html, params));
					if (this.isMobile) {
						$selectbox.append($('<option>').attr('oid', bn).attr('cmdvalue', oname.exvalue).text(oname.title));
					}
				}
			}

			if (this.isMobile && $selectbox) {
				$btn.append($selectbox);
				$selectbox.on('change', $.proxy(function(e) {
					e.preventDefault();
					const $o       = $(e.currentTarget).find(':selected');
					const oid      = $o.attr('oid');
					const cmdvalue = $o.attr('cmdvalue');
					const opt      = this.options.allButtons[oid];
					this.execCommand(oid, opt.exvalue || cmdvalue || false);
					$(e.currentTarget).trigger('queryState');
				}, this));
			}

			this.controllers.push($btn);
			$btn.on('queryState', $.proxy(function() {
				$sval.text(opt.title);
				$btn.find('.option.selected').removeClass('selected');
				$btn.find('.option').each($.proxy(function(i, el) {
					const $el      = $(el);
					const r        = this.queryState($el.attr('oid'), true);
					const cmdvalue = $el.attr('cmdvalue');
					if ((cmdvalue && r === cmdvalue) || (!cmdvalue && r)) {
						$sval.text($el.text());
						$el.addClass('selected');
						return false;
					}
				}, this));
			}, this));
			$btn.mousedown($.proxy(function(e) { e.preventDefault(); this.dropdownclick('.wbb-select', '.wbb-list', e); }, this));
			$btn.find('.option').mousedown($.proxy(function(e) {
				e.preventDefault();
				const oid      = $(e.currentTarget).attr('oid');
				const cmdvalue = $(e.currentTarget).attr('cmdvalue');
				const opt      = this.options.allButtons[oid];
				this.execCommand(oid, opt.exvalue || cmdvalue || false);
				$(e.currentTarget).trigger('queryState');
			}, this));
		},

		buildSmilebox(container, bn, opt) {
			if (this.options.smileList && this.options.smileList.length > 0) {
				const $btnHTML = $(this.strf(opt.buttonHTML, opt)).addClass('btn-inner');
				const $btn     = $('<div class="wysibb-toolbar-btn wbb-smilebox wbb-' + bn + '">').appendTo(container)
					.append($btnHTML)
					.append(this.strf('<span class="btn-tooltip">{title}</span>', { title: opt.title }));
				const $sblock  = $('<div class="wbb-list">').appendTo($btn);

				$.each(this.options.smileList, $.proxy(function(i, sm) {
					$('<span>').addClass('smile').appendTo($sblock).append($(this.strf(sm.img, this.options)).attr('title', sm.title));
				}, this));

				$btn.mousedown($.proxy(function(e) { e.preventDefault(); this.dropdownclick('.wbb-smilebox', '.wbb-list', e); }, this));
				$btn.find('.smile').mousedown($.proxy(function(e) {
					e.preventDefault();
					this.insertAtCursor(this.options.bbmode ? this.toBB($(e.currentTarget).html()) : $($(e.currentTarget).html()));
				}, this));
			}
		},

		updateUI(e) {
			if (!e || (e.which >= 8 && e.which <= 46) || e.which > 90 || e.type === 'mouseup') {
				$.each(this.controllers, $.proxy(function(i, $btn) { $btn.trigger('queryState'); }, this));
			}
			this.disNonActiveButtons();
		},

		initModal() {
			this.$modal = $('#wbbmodal');
			if (this.$modal.length === 0) {
				$.log('Init modal');
				this.$modal = $('<div>').attr('id', 'wbbmodal').prependTo(document.body)
					.html('<div class="wbbm"><div class="wbbm-title"><span class="wbbm-title-text"></span><span class="wbbclose" title="Cerrar">×</span></div><div class="wbbm-content"></div><div class="wbbm-bottom"><button id="wbbm-submit" class="wbb-button">Guardar</button><button id="wbbm-cancel" class="wbb-cancel-button">Cancelar</button><button id="wbbm-remove" class="wbb-remove-button">Borrar</button></div></div>')
					.hide();
				this.$modal.find('#wbbm-cancel,.wbbclose').click($.proxy(this.closeModal, this));
				this.$modal.on('click', $.proxy(function(e) {
					if ($(e.target).parents('.wbbm').length === 0) { this.closeModal(); }
				}, this));
				$(document).on('keydown', $.proxy(this.escModal, this));
			}
		},

		initHotkeys() {
			$.log('initHotkeys');
			this.hotkeys = [];
			const klist = '0123456789       abcdefghijklmnopqrstuvwxyz';
			$.each(this.options.allButtons, $.proxy(function(cmd, opt) {
				if (opt.hotkey) {
					const keys = opt.hotkey.split('+');
					if (keys && keys.length >= 2) {
						let metasum = 0;
						const key   = keys.pop();
						keys.forEach(k => {
							switch (k.toLowerCase().trim()) {
								case 'ctrl':  metasum += 1; break;
								case 'shift': metasum += 4; break;
								case 'alt':   metasum += 7; break;
							}
						});
						if (metasum > 0) {
							if (!this.hotkeys['m' + metasum]) { this.hotkeys['m' + metasum] = []; }
							this.hotkeys['m' + metasum]['k' + (klist.indexOf(key) + 48)] = cmd;
						}
					}
				}
			}, this));
		},

		presskey(e) {
			if (e.ctrlKey || e.shiftKey || e.altKey) {
				const metasum = (e.ctrlKey ? 1 : 0) + (e.shiftKey ? 4 : 0) + (e.altKey ? 7 : 0);
				if (this.hotkeys['m' + metasum]?.['k' + e.which]) {
					this.execCommand(this.hotkeys['m' + metasum]['k' + e.which], false);
					e.preventDefault();
					return false;
				}
			}
		},

		// COMMAND FUNCTIONS
		execCommand(command, value) {
			$.log('execCommand: ' + command);
			const opt = this.options.allButtons[command];
			if (opt.en !== true) { return false; }
			const queryState = this.queryState(command, value);
			const skipcmd    = this.isInClearTextBlock();
			if (skipcmd && skipcmd !== command) { return; }

			if (opt.excmd) {
				if (this.options.bbmode) {
					if (queryState && opt.subInsert !== true) {
						this.wbbRemoveCallback(command, value);
					} else {
						const v = {};
						if (opt.valueBBname && value) { v[opt.valueBBname] = value; }
						this.insertAtCursor(this.getBBCodeByCommand(command, v));
					}
				} else {
					this.execNativeCommand(opt.excmd, value || false);
				}
			} else if (!opt.cmd) {
				this.wbbExecCommand.call(this, command, value, queryState);
			} else {
				opt.cmd.call(this, command, value, queryState);
			}
			this.updateUI();
		},

		queryState(command, withvalue) {
			const opt = this.options.allButtons[command];
			if (opt.en !== true) { return false; }
			if (this.options.bbmode) {
				if (opt.bbSelector) {
					for (let i = 0; i < opt.bbSelector.length; i++) {
						const b = this.isBBContain(opt.bbSelector[i]);
						if (b) { return this.getParams(b, opt.bbSelector[i], b[1]); }
					}
				}
				return false;
			} else {
				const node = this.getSelectNode();
				if (opt.excmd) {
					if (withvalue) {
						try {
							let v = (document.queryCommandValue(opt.excmd) + '').replace(/'/g, '');
							if (opt.excmd === 'foreColor') { v = this.rgbToHex(v); }
							return v;
						} catch (e) { return false; }
					} else {
						try {
							if (['bold', 'italic', 'underline', 'strikeThrough'].includes(opt.excmd) && $(node).is('img')) {
								return false;
							} else if (opt.excmd === 'underline' && $(node).closest('a').length > 0) {
								return false;
							} else {
								return document.queryCommandState(opt.excmd);
							}
						} catch (e) { return false; }
					}
				} else {
					if (Array.isArray(opt.rootSelector)) {
						for (let i = 0; i < opt.rootSelector.length; i++) {
							const n = this.isContain(node, opt.rootSelector[i]);
							if (n) { return this.getParams(n, opt.rootSelector[i]); }
						}
					}
					return false;
				}
			}
		},

		wbbExecCommand(command, value, queryState) {
			$.log('wbbExecCommand');
			const opt = this.options.allButtons[command];
			if (opt) {
				if (opt.modal) {
					if (typeof opt.modal === 'function') {
						opt.modal.call(this, command, opt.modal, queryState);
					} else {
						this.showModal.call(this, command, opt.modal, queryState);
					}
				} else {
					if (queryState && opt.subInsert !== true) {
						this.wbbRemoveCallback(command);
					} else {
						if (opt.groupkey) {
							const groupsel = this.options.groups[opt.groupkey];
							if (groupsel) {
								const snode = this.getSelectNode();
								$.each(groupsel, $.proxy(function(i, sel) {
									const is = this.isContain(snode, sel);
									if (is) {
										const $sp = $('<span>').html(is.innerHTML);
										const id  = this.setUID($sp);
										$(is).replaceWith($sp);
										this.selectNode(this.$editor.find('#' + id)[0]);
										return false;
									}
								}, this));
							}
						}
						this.wbbInsertCallback(command, value);
					}
				}
			}
		},

		wbbInsertCallback(command, paramobj) {
			if (typeof paramobj !== 'object') { paramobj = {}; }
			$.log('wbbInsertCallback: ' + command);
			const data = this.getCodeByCommand(command, paramobj);
			this.insertAtCursor(data);
			if (this.seltextID && data.indexOf(this.seltextID) !== -1) {
				const snode = this.$body.find('#' + this.seltextID)[0];
				this.selectNode(snode);
				$(snode).removeAttr('id');
				this.seltextID = false;
			}
		},

		wbbRemoveCallback(command, clear) {
			$.log('wbbRemoveCallback: ' + command);
			const opt = this.options.allButtons[command];
			if (this.options.bbmode) {
				const pos = this.getCursorPosBB();
				let stextnum = 0;
				$.each(opt.bbSelector, $.proxy(function(i, bbcode) {
					const stext = bbcode.match(/\{[\s\S]+?\}/g);
					$.each(stext, function(n, s) {
						if (s.toLowerCase() === '{seltext}') { stextnum = n; return false; }
					});
					const a = this.isBBContain(bbcode);
					if (a) {
						this.txtArea.value = this.txtArea.value.substr(0, a[1]) +
							this.txtArea.value.substr(a[1], this.txtArea.value.length - a[1])
								.replace(a[0][0], clear === true ? '' : a[0][stextnum + 1]);
						this.setCursorPosBB(a[1]);
						return false;
					}
				}, this));
			} else {
				const node = this.getSelectNode();
				$.each(opt.rootSelector, $.proxy(function(i, s) {
					const root  = this.isContain(node, s);
					if (!root) { return true; }
					const $root = $(root);
					const cs    = this.options.rules[s][0][1];
					if ($root.is('span[wbb]') || !$root.is('span,font')) {
						if (clear === true || (!cs || !cs['seltext'])) {
							this.setCursorByEl($root);
							$root.remove();
						} else {
							if (cs && cs['seltext'] && cs['seltext']['sel']) {
								let htmldata = $root.find(cs['seltext']['sel']).html();
								if (opt.onlyClearText === true) {
									htmldata = this.getHTML(htmldata, true, true);
									htmldata = htmldata.replace(/\&#123;/g, '{').replace(/\&#125;/g, '}');
								}
								$root.replaceWith(htmldata);
							} else {
								let htmldata = $root.html();
								if (opt.onlyClearText === true) {
									htmldata = this.getHTML(htmldata, true);
									htmldata = htmldata.replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&#123;/g, '{').replace(/\&#125;/g, '}');
								}
								$root.replaceWith(htmldata);
							}
						}
						return false;
					} else {
						const rng  = this.getRange();
						let shtml  = this.getSelectText();
						if (shtml === '') { shtml = '<br>'; }
						else { shtml = this.clearFromSubInsert(shtml, command); }
						const ins = this.elFromString(shtml);
						const before_rng = rng.cloneRange();
						const after_rng  = rng.cloneRange();
						this.insertAtCursor('<span id="wbbdivide"></span>');
						const div = $root.find('span#wbbdivide').get(0);
						before_rng.setStart(root.firstChild, 0);
						before_rng.setEndBefore(div);
						after_rng.setStartAfter(div);
						after_rng.setEndAfter(root.lastChild);
						const bf = this.getSelectText(false, before_rng);
						const af = this.getSelectText(false, after_rng);
						if (af !== '') { $root.after($root.clone().html(af)); }
						if (clear !== true) { $root.after(ins); }
						$root.html(bf);
						if (clear !== true) { this.selectNode(ins); }
						return false;
					}
				}, this));
			}
		},

		execNativeCommand(cmd, param) {
			this.body.focus();
			if (cmd === 'insertHTML') {
				const sel = this.getSelection();
				const e   = this.elFromString(param);
				const rng = this.lastRange || this.getRange();
				rng.deleteContents();
				rng.insertNode(e);
				rng.collapse(false);
				sel.removeAllRanges();
				sel.addRange(rng);
			} else {
				if (typeof param === 'undefined') { param = false; }
				if (this.lastRange) { $.log('Last range select'); this.selectLastRange(); }
				document.execCommand(cmd, false, param);
			}
		},

		getCodeByCommand(command, paramobj) {
			return this.options.bbmode ? this.getBBCodeByCommand(command, paramobj) : this.getHTMLByCommand(command, paramobj);
		},

		getBBCodeByCommand(command, params) {
			if (!this.options.allButtons[command]) { return ''; }
			if (typeof params === 'undefined') { params = {}; }
			params = this.keysToLower(params);
			if (!params['seltext']) { params['seltext'] = this.getSelectText(true); }

			let bbcode = this.options.allButtons[command].bbcode;
			bbcode = bbcode.replace(/\{(.*?)(\[.*?\])*\}/g, function(str, p, vrgx) {
				if (vrgx) {
					const vrgxp = new RegExp(vrgx + '+', 'i');
					if (typeof params[p.toLowerCase()] !== 'undefined' && !params[p.toLowerCase()].toString().match(vrgxp)) { return ''; }
				}
				return typeof params[p.toLowerCase()] === 'undefined' ? '' : params[p.toLowerCase()];
			});

			let rbbcode = null, maxpcount = 0;
			if (this.options.allButtons[command].transform) {
				const tr = this.sortArray($.map(this.options.allButtons[command].transform, (html, bb) => bb), -1);
				$.each(tr, function(i, v) {
					let valid = true, pcount = 0;
					const pname = {};
					v = v.replace(/\{(.*?)(\[.*?\])*\}/g, function(str, p, vrgx) {
						const vrgxp = vrgx ? new RegExp(vrgx + '+', 'i') : null;
						p = p.toLowerCase();
						if (typeof params[p] === 'undefined' || (vrgx && !params[p].toString().match(vrgxp))) { valid = false; }
						if (typeof params[p] !== 'undefined' && !pname[p]) { pname[p] = 1; pcount++; }
						return typeof params[p] === 'undefined' ? '' : params[p];
					});
					if (valid && pcount > maxpcount) { rbbcode = v; maxpcount = pcount; }
				});
			}
			return rbbcode || bbcode;
		},

		getHTMLByCommand(command, params) {
			if (!this.options.allButtons[command]) { return ''; }
			params = this.keysToLower(params || {});
			if (!params['seltext']) {
				params['seltext'] = this.getSelectText(false);
				if (params['seltext'] === '') { params['seltext'] = '<br>'; }
				else {
					params['seltext'] = this.clearFromSubInsert(params['seltext'], command);
					if (this.options.allButtons[command].onlyClearText === true) {
						params['seltext'] = this.toBB(params['seltext']).replace(/\</g, '&lt;').replace(/\n/g, '<br/>').replace(/\s{3}/g, '<span class="wbbtab"></span>');
					}
				}
			}

			let postsel = '';
			this.seltextID = 'wbbid_' + (++this.lastid);
			if (command !== 'link' && command !== 'img') {
				params['seltext'] = '<span id="' + this.seltextID + '">' + params['seltext'] + '</span>';
			} else {
				postsel = '<span id="' + this.seltextID + '"><br></span>';
			}

			let html = this.options.allButtons[command].html;
			html = html.replace(/\{(.*?)(\[.*?\])*\}/g, function(str, p, vrgx) {
				if (vrgx) {
					const vrgxp = new RegExp(vrgx + '+', 'i');
					if (typeof params[p.toLowerCase()] !== 'undefined' && !params[p.toLowerCase()].toString().match(vrgxp)) { return ''; }
				}
				return typeof params[p.toLowerCase()] === 'undefined' ? '' : params[p.toLowerCase()];
			});

			let rhtml = null, maxpcount = 0;
			if (this.options.allButtons[command].transform) {
				const tr = this.sortArray($.map(this.options.allButtons[command].transform, (bb, html) => html), -1);
				$.each(tr, function(i, v) {
					let valid = true, pcount = 0;
					const pname = {};
					v = v.replace(/\{(.*?)(\[.*?\])*\}/g, function(str, p, vrgx) {
						const vrgxp = vrgx ? new RegExp(vrgx + '+', 'i') : null;
						p = p.toLowerCase();
						if (typeof params[p] === 'undefined' || (vrgx && !params[p].toString().match(vrgxp))) { valid = false; }
						if (typeof params[p] !== 'undefined' && !pname[p]) { pname[p] = 1; pcount++; }
						return typeof params[p] === 'undefined' ? '' : params[p];
					});
					if (valid && pcount > maxpcount) { rhtml = v; maxpcount = pcount; }
				});
			}
			return (rhtml || html) + postsel;
		},

		// SELECTION FUNCTIONS
		getSelection() {
			return window.getSelection ? window.getSelection() : document.selection.createRange();
		},

		getSelectText(fromTxtArea, range) {
			if (fromTxtArea) {
				this.txtArea.focus();
				if ('selectionStart' in this.txtArea) {
					const l = this.txtArea.selectionEnd - this.txtArea.selectionStart;
					return this.txtArea.value.substr(this.txtArea.selectionStart, l);
				}
				return document.selection.createRange().text;
			} else {
				this.body.focus();
				if (!range) { range = this.getRange(); }
				if (window.getSelection && range) { return $('<div>').append(range.cloneContents()).html(); }
			}
			return '';
		},

		getRange() {
			if (window.getSelection) {
				const sel = this.getSelection();
				if (sel.getRangeAt && sel.rangeCount > 0) { return sel.getRangeAt(0); }
				if (sel.anchorNode) {
					const range = document.createRange();
					range.setStart(sel.anchorNode, sel.anchorOffset);
					range.setEnd(sel.focusNode, sel.focusOffset);
					return range;
				}
			}
			return null;
		},

		insertAtCursor(code, forceBBMode) {
			if (typeof code !== 'string') { code = $('<div>').append(code).html(); }
			if ((this.options.bbmode && typeof forceBBMode === 'undefined') || forceBBMode === true) {
				const clbb = code.replace(/.*(\[\/\S+?\])$/, '$1');
				let p = this.getCursorPosBB() + ((code.indexOf(clbb) !== -1 && code.match(/\[.*\]/)) ? code.indexOf(clbb) : code.length);
				if (this.txtArea.selectionStart || this.txtArea.selectionStart === '0') {
					this.txtArea.value = this.txtArea.value.substring(0, this.txtArea.selectionStart) + code + this.txtArea.value.substring(this.txtArea.selectionEnd, this.txtArea.value.length);
				}
				if (p < 0) { p = 0; }
				this.setCursorPosBB(p);
			} else {
				this.execNativeCommand('insertHTML', code);
				const node = this.getSelectNode();
				if (!$(node).closest('table,tr,td')) { this.splitPrevNext(node); }
			}
		},

		getSelectNode(rng) {
			this.body.focus();
			if (!rng) { rng = this.getRange(); }
			if (!rng) { return this.$body; }
			let sn = rng.commonAncestorContainer;
			if ($(sn).is('.imgWrap')) { sn = $(sn).children('img')[0]; }
			return sn;
		},

		getCursorPosBB() {
			if ('selectionStart' in this.txtArea) { return this.txtArea.selectionStart; }
			return 0;
		},

		setCursorPosBB(pos) {
			if (this.options.bbmode && window.getSelection) {
				this.txtArea.selectionStart = pos;
				this.txtArea.selectionEnd   = pos;
			}
		},

		selectNode(node, rng) {
			if (!rng) { rng = this.getRange(); }
			if (!rng) { return; }
			if (window.getSelection) {
				const sel = this.getSelection();
				rng.selectNodeContents(node);
				sel.removeAllRanges();
				sel.addRange(rng);
			}
		},

		selectRange(rng) {
			if (rng && window.getSelection) {
				const sel = this.getSelection();
				sel.removeAllRanges();
				sel.addRange(rng);
			}
		},

		cloneRange(rng) { return rng ? rng.cloneRange() : null; },
		getRangeClone() { return this.cloneRange(this.getRange()); },

		saveRange() {
			this.setBodyFocus();
			this.lastRange = this.getRangeClone();
		},

		selectLastRange() {
			if (this.lastRange) {
				this.body.focus();
				this.selectRange(this.lastRange);
				this.lastRange = false;
			}
		},

		setBodyFocus() {
			$.log('Set focus to WysiBB editor');
			if (this.options.bbmode) {
				if (!this.$txtArea.is(':focus')) { this.$txtArea.focus(); }
			} else {
				if (!this.$body.is(':focus')) { this.$body.focus(); }
			}
		},

		clearLastRange() { this.lastRange = false; },

		// TRANSFORM FUNCTIONS
		filterByNode(node) {
			const $n      = $(node);
			const tagName = $n.get(0).tagName.toLowerCase();
			let filter    = tagName;
			const attributes = this.getAttributeList($n.get(0));
			$.each(attributes, $.proxy(function(i, item) {
				let v = $n.attr(item);
				if (item.substr(0, 1) === '_') { item = item.substr(1); }
				if (v && !v.match(/\{.*?\}/)) {
					if (item === 'style') {
						v.split(';').forEach(f => { if (f && f.length > 0) filter += '[' + item + '*="' + f.trim() + '"]'; });
					} else {
						filter += '[' + item + '="' + v + '"]';
					}
				} else if (v && item === 'style') {
					const vf = v.substr(0, v.indexOf('{'));
					if (vf) {
						vf.split(';').forEach(f => { filter += '[' + item + '*="' + f + '"]'; });
					}
				} else {
					filter += '[' + item + ']';
				}
			}, this));
			const idx = $n.parent().children(filter).index($n);
			if (idx > 0) { filter += ':eq(' + $n.index() + ')'; }
			return filter;
		},

		relFilterByNode(node, stop) {
			let p = '';
			$.each(this.options.attrWrap, function(i, a) { stop = stop.replace('[' + a, '[_' + a); });
			while (node && node.tagName !== 'BODY' && !$(node).is(stop)) {
				p = this.filterByNode(node) + ' ' + p;
				if (node) { node = node.parentNode; }
			}
			return p;
		},

		getRegexpReplace(str, validname) {
			return str.replace(/(\(|\)|\[|\]|\.|\*|\?|\:|\\)/g, '\\$1')
				.replace(/\s+/g, '\\s+')
				.replace(validname.replace(/(\(|\)|\[|\]|\.|\*|\?|\:|\\)/g, '\\$1'), '(.+)')
				.replace(/\{\S+?\}/g, '.*');
		},

		getBBCode() {
			if (!this.options.rules) { return this.$txtArea.val(); }
			if (this.options.bbmode) { return this.$txtArea.val(); }
			this.clearEmpty();
			this.removeLastBodyBR();
			return this.toBB(this.$body.html());
		},

		toBB(data) {
			if (!data) { return ''; }
			const $e = (typeof data === 'string') ? $('<span>').html(data) : $(data);
			$e.find('div,blockquote,p').each(function() {
				if (this.nodeType !== 3 && this.lastChild && this.lastChild.tagName === 'BR') { $(this.lastChild).remove(); }
			});
			if ($e.is('div,blockquote,p') && $e[0].nodeType !== 3 && $e[0].lastChild && $e[0].lastChild.tagName === 'BR') {
				$($e[0].lastChild).remove();
			}
			$e.find('ul > br, table > br, tr > br').remove();
			let outbb = '';
			$.each(this.options.srules, $.proxy(function(s, bb) { $e.find(s).replaceWith(bb[0]); }, this));
			$e.contents().each($.proxy(function(i, el) {
				const $el = $(el);
				if (el.nodeType === 3) {
					outbb += el.data.replace(/\n+/, '').replace(/\t/g, '   ');
				} else {
					let processed = false;
					for (let j = 0; j < this.rsellist.length; j++) {
						const rootsel = this.rsellist[j];
						if ($el && $el.is(rootsel)) {
							const rlist = this.options.rules[rootsel];
							for (let i = 0; i < rlist.length; i++) {
								let bbcode     = rlist[i][0];
								const crules   = rlist[i][1];
								let skip       = false, keepElement = false;
								if (!$el.is('br')) { bbcode = bbcode.replace(/\n/g, '<br>'); }
								bbcode = bbcode.replace(/\{(.*?)(\[.*?\])*\}/g, $.proxy(function(str, s, vrgx) {
									const c = crules[s.toLowerCase()];
									if (typeof c === 'undefined') { $.log('Param: {' + s + '} not found'); skip = true; }
									const $cel = c.sel ? $(el).find(c.sel) : $(el);
									if (c.attr && !$cel.attr(c.attr)) { skip = true; return s; }
									let cont = c.attr ? $cel.attr(c.attr) : $cel.html();
									if (typeof cont === 'undefined' || cont === null) { skip = true; return s; }
									let regexp = c.rgx;
									if (regexp && c.attr === 'style' && regexp.substr(regexp.length - 1, 1) !== ';') { regexp += ';'; }
									if (c.attr === 'style' && cont && cont.substr(cont.length - 1, 1) !== ';') { cont += ';'; }
									const rgx = regexp ? new RegExp(regexp, '') : false;
									if (rgx) {
										if (cont.match(rgx)) {
											const m = cont.match(rgx);
											if (m && m.length === 2) { cont = m[1]; }
										} else { cont = ''; }
									}
									if (c.attr && skip === false) {
										if (c.attr === 'style') {
											keepElement = true;
											let nstyle = '';
											const r = c.rgx.replace(/^\.\*\?/, '').replace(/\.\*$/, '').replace(/;$/, '');
											$cel.attr('style').split(';').forEach(style => {
												if (style && !style.match(r)) { nstyle += style + ';'; }
											});
											nstyle ? $cel.attr('style', nstyle) : $cel.removeAttr('style');
										} else if (c.rgx === false) {
											keepElement = true;
											$cel.removeAttr(c.attr);
										}
									}
									if ($el.is('table,tr,td,font')) { keepElement = true; }
									return cont || '';
								}, this));
								if (skip) { continue; }
								if ($el.is('img,br,hr')) {
									outbb += bbcode;
									return true;
								} else {
									if (keepElement && !$el.attr('notkeep')) {
										if ($el.is('table,tr,td')) {
											bbcode = this.fixTableTransform(bbcode);
											outbb += this.toBB($('<span>').html(bbcode));
										} else {
											$el.empty().html('<span>' + bbcode + '</span>');
										}
									} else {
										if ($el.is('iframe')) {
											outbb += bbcode;
										} else {
											$el.empty().html(bbcode);
											outbb += this.toBB($el);
										}
										break;
									}
								}
							}
						}
					}
					if (!$el || $el.is('iframe,img')) { return true; }
					outbb += this.toBB($el);
				}
			}, this));
			return outbb;
		},

		getHTML(bbdata, init, skiplt) {
			if (!this.options.bbmode && !init) { return this.$body.html(); }
			if (!skiplt) { bbdata = bbdata.replace(/</g, '&lt;').replace(/\{/g, '&#123;').replace(/\}/g, '&#125;'); }
			bbdata = bbdata.replace(/\[code\]([\s\S]*?)\[\/code\]/g, function(s) {
				let content = s.substr('[code]'.length, s.length - '[code]'.length - '[/code]'.length);
				// Solo escapar si NO contiene entidades HTML previas
				if (
					!content.includes('&#91;') && !content.includes('&#93;')) {
				  	content = content.replace(/\[/g, '&#91;').replace(/\]/g, '&#93;');
				}
				return '[code]' + content + '[/code]';
			});
			bbdata = bbdata.replace(/\[diff\]([\s\S]*?)\[\/diff\]/gi, function(match, content) {
				const html = content.replace(/^\n|\n$/g, '').split('\n').map(line => {
					let cls = 'bbc-diff-line';
					if (line.match(/^\+/))     cls += ' add';
					else if (line.match(/^-/)) cls += ' del';
					else if (line.match(/^@@/)) cls += ' hdr';
					return '<div class="' + cls + '">' + line + '</div>';
				}).join('');
				return '<div class="bbc-diff">' + html + '</div>';
			});

			$.each(this.options.btnlist, $.proxy(function(i, b) {
				if (b !== '|' && b !== '-') {
					if (!this.options.allButtons[b] || !this.options.allButtons[b].transform) { return true; }
					$.each(this.options.allButtons[b].transform, $.proxy(function(html, bb) {
						html = html.replace(/\n/g, '');
						const a  = [];
						let bbrx = bb.replace(/(\(|\)|\[|\]|\.|\*|\?|\:|\\|\\)/g, '\\$1');
						bbrx = bbrx.replace(/\{(.*?)(\\\[.*?\\\])*\}/gi, $.proxy(function(str, s, vrgx) {
							a.push(s);
							if (vrgx) { vrgx = vrgx.replace(/\\/g, ''); return '(' + vrgx + '*?)'; }
							return '([\\s\\S]*?)';
						}, this));
						let am;
						while ((am = (new RegExp(bbrx, 'mgi')).exec(bbdata)) !== null) {
							if (am) {
								const r = {};
								$.each(a, $.proxy(function(i, k) { r[k] = am[i + 1]; }, this));
								let nhtml = html.replace(/\{(.*?)(\[.*?\])\}/g, '{$1}');
								nhtml = this.strf(nhtml, r);
								bbdata = bbdata.replace(am[0], nhtml);
							}
						}
					}, this));
				}
			}, this));

			$.each(this.options.systr, function(html, bb) {
				bb = bb.replace(/(\(|\)|\[|\]|\.|\*|\?|\:|\\|\\)/g, '\\$1').replace(' ', '\\s');
				bbdata = bbdata.replace(new RegExp(bb, 'g'), html);
			});

			const $wrap = $(this.elFromString('<div>' + bbdata + '</div>'));
			this.getHTMLSmiles($wrap);
			return $wrap.html();
		},

		getHTMLSmiles(rel) {
			$(rel).contents().filter(function() { return this.nodeType === 3; }).each($.proxy(this.smileRPL, this));
		},

		smileRPL(i, el) {
			let ndata = el.data;
			$.each(this.options.smileList, $.proxy(function(i, row) {
				const fidx = ndata.indexOf(row.bbcode);
				if (fidx !== -1) {
					const afternode_txt = ndata.substring(fidx + row.bbcode.length, ndata.length);
					const afternode     = document.createTextNode(afternode_txt);
					el.data = ndata = el.data.substr(0, fidx);
					$(el).after(afternode).after(this.strf(row.img, this.options));
					this.getHTMLSmiles(el.parentNode);
					return false;
				}
				this.getHTMLSmiles(el);
			}, this));
		},

		// UTILS
		setUID(el, attr) {
			const id = 'wbbid_' + (++this.lastid);
			if (el) { $(el).attr(attr || 'id', id); }
			return id;
		},

		keysToLower(o) {
			$.each(o, function(k, v) {
				if (k !== k.toLowerCase()) { delete o[k]; o[k.toLowerCase()] = v; }
			});
			return o;
		},

		strf(str, data) {
			data = this.keysToLower($.extend({}, data));
			return str.replace(/\{([\w.]*)\}/g, function(str, key) {
				key = key.toLowerCase();
				const keys  = key.split('.');
				let   value = data[keys.shift().toLowerCase()];
				$.each(keys, function() { value = value?.[this]; });
				return value === null || value === undefined ? '' : value;
			});
		},

		elFromString(str) {
			if (str.indexOf('<') !== -1 && str.indexOf('>') !== -1) {
				const wr = document.createElement('SPAN');
				$(wr).html(str);
				this.setUID(wr, 'wbb');
				return $(wr).contents().length > 1 ? wr : wr.firstChild;
			}
			return document.createTextNode(str);
		},

		isContain(node, sel) {
			while (node && !$(node).hasClass('wysibb')) {
				if ($(node).is(sel)) { return node; }
				node = node ? node.parentNode : null;
			}
		},

		isBBContain(bbcode) {
			const pos = this.getCursorPosBB();
			const b   = this.prepareRGX(bbcode);
			const bbrgx = new RegExp(b, 'g');
			let a, lastindex = 0;
			while ((a = bbrgx.exec(this.txtArea.value)) !== null) {
				const p = this.txtArea.value.indexOf(a[0], lastindex);
				if (pos > p && pos < (p + a[0].length)) { return [a, p]; }
				lastindex = p + 1;
			}
		},

		prepareRGX(r) {
			return r.replace(/(\[|\]|\)|\(|\.|\*|\?|\:|\||\\)/g, '\\$1').replace(/\{.*?\}/g, '([\\s\\S]*?)');
		},

		checkForLastBR(node) {
			if (!node) { node = this.body; }
			if (node.nodeType === 3) { node = node.parentNode; }
			const $node = $(node);
			if ($node.is("span[id*='wbbid']")) { $node.parent(); }
			if (this.options.bbmode === false && $node.is('div,blockquote,code') && $node.contents().length > 0) {
				const l = $node[0].lastChild;
				if (!l || l.tagName !== 'BR') { $node.append('<br/>'); }
			}
			if (this.$body.contents().length > 0 && this.body.lastChild.tagName !== 'BR') {
				this.$body.append('<br/>');
			}
		},

		getAttributeList(el) {
			const a = [];
			$.each(el.attributes, function(i, attr) { if (attr.specified) a.push(attr.name); });
			return a;
		},

		clearFromSubInsert(html, cmd) {
			if (this.options.allButtons[cmd]?.rootSelector) {
				const $wr = $('<div>').html(html);
				$.each(this.options.allButtons[cmd].rootSelector, $.proxy(function(i, s) {
					let seltext = false;
					if (typeof this.options.rules[s]?.[0]?.[1]?.['seltext'] !== 'undefined') {
						seltext = this.options.rules[s][0][1]['seltext']['sel'];
					}
					let res = true;
					$wr.find('*').each(function() {
						if ($(this).is(s)) {
							if (seltext?.sel) { $(this).replaceWith($(this).find(seltext.sel.toLowerCase()).html()); }
							else              { $(this).replaceWith($(this).html()); }
							res = false;
						}
					});
					return res;
				}, this));
				return $wr.html();
			}
			return html;
		},

		splitPrevNext(node) {
			if (node.nodeType === 3) { node = node.parentNode; }
			const f = this.filterByNode(node).replace(/\:eq.*$/g, '');
			if ($(node.nextSibling).is(f)) { $(node).append($(node.nextSibling).html()); $(node.nextSibling).remove(); }
			if ($(node.previousSibling).is(f)) { $(node).prepend($(node.previousSibling).html()); $(node.previousSibling).remove(); }
		},

		modeSwitch() {
			if (this.options.bbmode) {
				this.$body.html(this.getHTML(this.$txtArea.val())).css('min-height', this.$txtArea.height());
				this.$txtArea.hide().removeAttr('wbbsync').val('');
				this.$body.show().focus();
			} else {
				this.$txtArea.val(this.getBBCode()).css('min-height', this.$body.height());
				this.$body.hide();
				this.$txtArea.show().focus();
			}
			this.options.bbmode = !this.options.bbmode;
		},

		clearEmpty() {
			this.$body.children().filter(emptyFilter).remove();
			function emptyFilter() {
				if (!$(this).is('span,font,a,b,i,u,s')) { return false; }
				if (!$(this).hasClass('wbbtab') && $(this).html().trim().length === 0) { return true; }
				else if ($(this).children().length > 0) {
					$(this).children().filter(emptyFilter).remove();
					if ($(this).html().length === 0 && this.tagName !== 'BODY') { return true; }
				}
			}
		},

		dropdownclick(bsel, tsel, e) {
			const $btn = $(e.currentTarget).closest(bsel);
			if ($btn.hasClass('dis')) { return; }
			if ($btn.attr('wbbshow')) {
				$btn.removeAttr('wbbshow');
				$(document).off('mousedown', this.dropdownhandler);
				this.lastRange = false;
			} else {
				this.saveRange();
				this.$editor.find('*[wbbshow]').each(function(i, el) {
					$(el).removeClass('on').find($(el).attr('wbbshow')).hide().end().removeAttr('wbbshow');
				});
				$btn.attr('wbbshow', tsel);
				$(document.body).on('mousedown', $.proxy(function(evt) { this.dropdownhandler($btn, bsel, tsel, evt); }, this));
				if (this.$body) { this.$body.on('mousedown', $.proxy(function(evt) { this.dropdownhandler($btn, bsel, tsel, evt); }, this)); }
			}
			$btn.find(tsel).toggle();
			$btn.toggleClass('on');
		},

		dropdownhandler($btn, bsel, tsel, e) {
			if ($(e.target).parents(bsel).length === 0) {
				$btn.removeClass('on').find(tsel).hide();
				$(document).off('mousedown', this.dropdownhandler);
				if (this.$body) { this.$body.off('mousedown', this.dropdownhandler); }
			}
		},

		rgbToHex(rgb) {
			if (rgb.substr(0, 1) === '#') { return rgb; }
			if (rgb.indexOf('rgb') === -1) {
				const color = parseInt(rgb);
				return '#' + (((color & 0x0000ff) << 16) | (color & 0x00ff00) | ((color & 0xff0000) >>> 16)).toString(16);
			}
			const digits = /(.*?)rgb\((\d+),\s*(\d+),\s*(\d+)\)/.exec(rgb);
			return '#' + this.dec2hex(parseInt(digits[2])) + this.dec2hex(parseInt(digits[3])) + this.dec2hex(parseInt(digits[4]));
		},

		dec2hex(d) { return d > 15 ? d.toString(16) : '0' + d.toString(16); },

		sync() {
			if (this.options.bbmode) { this.$body.html(this.getHTML(this.txtArea.value, true)); }
			else { this.$txtArea.attr('wbbsync', 1).val(this.getBBCode()); }
		},

		clearPaste(el) {
			const $block = $(el);
			$.each(this.options.rules, $.proxy(function(s, ar) {
				const $sf = $block.find(s).attr('wbbkeep', 1);
				if ($sf.length > 0) {
					$.each(ar[0][1], function(i, v) { if (v.sel) $sf.find(v.sel).attr('wbbkeep', 1); });
				}
			}, this));
			$block.find("*[wbbkeep!='1']").each($.proxy(function(i, el) {
				const $this = $(el);
				if ($this.is('div,p') && ($this.children().length === 0 || el.lastChild.tagName !== 'BR')) { $this.after('<br/>'); }
			}, this));
			$block.find('*[wbbkeep]').removeAttr('wbbkeep').removeAttr('style');
			$block.html(this.getHTML(this.toBB($block), true));
		},

		sortArray(ar, asc) {
			ar.sort((a, b) => (a.length - b.length) * (asc || 1));
			return ar;
		},

		smileFind() {
			if (this.options.smilefind) {
				const $smlist = $(this.options.smilefind).find('img[alt]');
				if ($smlist.length > 0) {
					this.options.smileList = [];
					$smlist.each($.proxy(function(i, el) {
						const $el = $(el);
						this.options.smileList.push({
							title:  $el.attr('title'),
							bbcode: $el.attr('alt'),
							img:    $el.removeAttr('alt').removeAttr('title')[0].outerHTML
						});
					}, this));
				}
			}
		},

		destroy() {
			this.$editor.replaceWith(this.$txtArea);
			this.$txtArea.removeClass('wysibb-texarea').show();
			this.$modal.remove();
			this.$txtArea.data('wbb', null);
		},

		pressTab(e) {
			if (e && e.which === 9) {
				if (e.preventDefault) { e.preventDefault(); }
				if (this.options.bbmode) { this.insertAtCursor('   ', false); }
				else { this.insertAtCursor('<span class="wbbtab"><br></span>', false); }
			}
		},

		removeLastBodyBR() {
			if (this.body.lastChild && this.body.lastChild.nodeType !== 3 && this.body.lastChild.tagName === 'BR') {
				this.body.removeChild(this.body.lastChild);
				this.removeLastBodyBR();
			}
		},

		traceTextareaEvent(e) {
			if ($(e.target).closest('div.wysibb').length === 0) {
				if ($(document.activeElement).is('div.wysibb-body')) { this.saveRange(); }
				setTimeout($.proxy(function() {
					const data = this.$txtArea.val();
					if (this.options.bbmode === false && data !== '' && $(e.target).closest('div.wysibb').length === 0 && !this.$txtArea.attr('wbbsync')) {
						this.selectLastRange();
						this.insertAtCursor(this.getHTML(data, true));
						this.$txtArea.val('');
					}
					if ($(document.activeElement).is('div.wysibb-body')) { this.lastRange = false; }
				}, this), 100);
			}
		},

		txtAreaInitContent() { this.$body.html(this.getHTML(this.txtArea.value, true)); },

		getValidationRGX(s) {
			if (s.match(/\[\S+\]/)) { return s.replace(/.*(\\*\[\S+\]).*/,'$1'); }
			return '';
		},

		smileConversion() {
			if (this.options.smileList?.length > 0) {
				const snode = this.getSelectNode();
				if (snode.nodeType === 3) {
					const ndata = snode.data;
					if (ndata.length >= 2 && !this.isInClearTextBlock(snode) && $(snode).parents('a').length === 0) {
						$.each(this.options.srules, $.proxy(function(i, sar) {
							const smbb = sar[0];
							const fidx = ndata.indexOf(smbb);
							if (fidx !== -1) {
								const afternode_txt    = ndata.substring(fidx + smbb.length, ndata.length);
								const afternode        = document.createTextNode(afternode_txt);
								const afternode_cursor = document.createElement('SPAN');
								snode.data = snode.data.substr(0, fidx);
								$(snode).after(afternode).after(afternode_cursor).after(this.strf(sar[1], this.options));
								this.selectNode(afternode_cursor);
								return false;
							}
						}, this));
					}
				}
			}
		},

		isInClearTextBlock() {
			if (this.cleartext) {
				let find = false;
				$.each(this.cleartext, $.proxy(function(sel, command) {
					if (this.queryState(command)) { find = command; return false; }
				}, this));
				return find;
			}
			return false;
		},

		wrapAttrs(html) {
			$.each(this.options.attrWrap, function(i, a) { html = html.replace(a + '="', '_' + a + '="'); });
			return html;
		},

		unwrapAttrs(html) {
			$.each(this.options.attrWrap, function(i, a) { html = html.replace('_' + a + '="', a + '="'); });
			return html;
		},

		disNonActiveButtons() {
			if (this.isInClearTextBlock()) {
				this.$toolbar.find('.wysibb-toolbar-btn:not(.on,.mswitch)').addClass('dis');
			} else {
				this.$toolbar.find('.wysibb-toolbar-btn.dis').removeClass('dis');
			}
		},

		setCursorByEl(el) {
			const sl = document.createTextNode('<br>');
			$(el).after(sl);
			this.selectNode(sl);
		},

		// img listeners
		imgListeners() { $(document).on('mousedown', $.proxy(this.imgEventHandler, this)); },

		imgEventHandler(e) {
			const $e = $(e.target);
			if (this.hasWrapedImage && ($e.closest('.wbb-img,#wbbmodal').length === 0 || $e.hasClass('wbb-cancel-button'))) {
				this.$body.find('.imgWrap').each(function() { $(this).replaceWith($(this).find('img')); });
				this.hasWrapedImage = false;
				this.updateUI();
			}
			if ($e.is('img') && $e.closest('.wysibb-body').length > 0) {
				$e.wrap("<span class='imgWrap'></span>");
				this.hasWrapedImage = $e;
				this.$body.focus();
				this.selectNode($e.parent()[0]);
			}
		},

		// MODAL WINDOW
		showModal(cmd, opt, queryState) {
			$.log('showModal: ' + cmd);
			this.saveRange();
			const $cont = this.$modal.find('.wbbm-content').html('');
			const $wbbm = this.$modal.find('.wbbm').removeClass('hastabs');
			this.$modal.find('span.wbbm-title-text').html(opt.title);

			if (opt.tabs && opt.tabs.length > 1) {
				$wbbm.addClass('hastabs');
				const $ul = $('<div class="wbbm-tablist">').appendTo($cont).append('<ul>').children('ul');
				$.each(opt.tabs, $.proxy(function(i, row) {
					if (i === 0) { row['on'] = 'on'; }
					$ul.append(this.strf('<li class="{on}" onClick="$(this).parent().find(\'.on\').removeClass(\'on\');$(this).addClass(\'on\');$(this).parents(\'.wbbm-content\').find(\'.tab-cont\').hide();$(this).parents(\'.wbbm-content\').find(\'.tab' + i + '\').show()">{title}</li>', row));
				}, this));
			}

			if (opt.width) { $wbbm.css('width', opt.width); }
			const $cnt = $('<div class="wbbm-cont">').appendTo($cont);
			queryState ? $wbbm.find('#wbbm-remove').show() : $wbbm.find('#wbbm-remove').hide();

			$.each(opt.tabs, $.proxy(function(i, r) {
				const $c = $('<div>').addClass('tab-cont tab' + i).attr('tid', i).appendTo($cnt);
				if (i > 0) { $c.hide(); }
				if (r.html) {
					$c.html(this.strf(r.html, this.options));
				} else {
					$.each(r.input, $.proxy(function(j, inp) {
						inp['value'] = queryState[inp.param.toLowerCase()];
						if (inp.param.toLowerCase() === 'seltext' && (!inp['value'] || inp['value'] === '')) {
							inp['value'] = this.getSelectText(this.options.bbmode);
						}
						if (inp['value'] && inp['value'].indexOf("<span id='wbbid") === 0 && $(inp['value']).is("span[id*='wbbid']")) {
							inp['value'] = $(inp['value']).html();
						}
						if (inp.type && inp.type === 'div') {
							$c.append(this.strf('<div class="wbbm-inp-row"><label>{title}</label><div class="inp-text div-modal-text" contenteditable="true" name="{param}">{value}</div></div>', inp));
						} else {
							$c.append(this.strf('<div class="wbbm-inp-row"><label>{title}</label><input class="inp-text modal-text" type="text" name="{param}" value="{value}"/></div>', inp));
						}
					}, this));
				}
			}, this));

			if (typeof opt.onLoad === 'function') { opt.onLoad.call(this, cmd, opt, queryState); }

			$wbbm.find('#wbbm-submit').click($.proxy(function() {
				if (typeof opt.onSubmit === 'function') {
					const r = opt.onSubmit.call(this, cmd, opt, queryState);
					if (r === false) { return; }
				}
				const params = {};
				let valid = true;
				this.$modal.find('.wbbm-inperr').remove();
				this.$modal.find('.wbbm-brdred').removeClass('wbbm-brdred');
				$.each(this.$modal.find('.tab-cont:visible .inp-text'), $.proxy(function(i, el) {
					const tid       = $(el).parents('.tab-cont').attr('tid');
					const pname     = $(el).attr('name').toLowerCase();
					const pval      = $(el).is('input,textarea,select') ? $(el).val() : $(el).html();
					const validation = opt.tabs[tid]['input'][i]['validation'];
					if (typeof validation !== 'undefined' && !pval.match(new RegExp(validation, 'i'))) {
						valid = false;
						$(el).after('<span class="wbbm-inperr">La información ingresada no es válida</span>').addClass('wbbm-brdred');
					}
					params[pname] = pval;
				}, this));

				if (valid) {
					this.selectLastRange();
					if (queryState) { this.wbbRemoveCallback(cmd, true); }
					this.wbbInsertCallback(cmd, params);
					this.closeModal();
					this.updateUI();
				}
			}, this));

			$wbbm.find('#wbbm-remove').click($.proxy(function() {
				this.selectLastRange();
				this.wbbRemoveCallback(cmd);
				this.closeModal();
				this.updateUI();
			}, this));

			$(document.body).css('overflow', 'hidden');
			if ($('body').height() > $(window).height()) { $(document.body).css('padding-right', '18px'); }
			this.$modal.show();
			if (this.isMobile) { $wbbm.css('margin-top', '10px'); }
			else { $wbbm.css('margin-top', ($(window).height() - $wbbm.outerHeight()) / 3 + 'px'); }
			setTimeout($.proxy(function() {
				const inp = this.$modal.find('.inp-text:visible')[0];
				if (inp) inp.focus();
			}, this), 10);
		},

		escModal(e) { if (e.which === 27) { this.closeModal(); } },

		closeModal() {
			$(document.body).css({ overflow: 'auto', 'padding-right': '0' }).off('keyup', this.escModal);
			this.$modal.find('#wbbm-submit,#wbbm-remove').off('click');
			this.$modal.hide();
			this.lastRange = false;
			return this;
		},

		getParams(src, s, offset) {
			const params = {};
			if (this.options.bbmode) {
				const stext = s.match(/\{[\s\S]+?\}/g);
				s = this.prepareRGX(s);
				const rgx  = new RegExp(s, 'g');
				let   val  = this.txtArea.value;
				if (offset > 0) { val = val.substr(offset, val.length - offset); }
				const a = rgx.exec(val);
				if (a) {
					$.each(stext, function(i, n) {
						params[n.replace(/\{|\}/g, '').replace(/"/g, "'").toLowerCase()] = a[i + 1];
					});
				}
			} else {
				const rules = this.options.rules[s][0][1];
				$.each(rules, $.proxy(function(k, v) {
					let value = '';
					const $v  = v.sel !== false ? $(src).find(v.sel) : $(src);
					if (v.attr !== false) { value = $v.attr(v.attr); }
					else { value = $v.html(); }
					if (value) {
						if (v.rgx !== false) {
							const m = value.match(new RegExp(v.rgx));
							if (m && m.length === 2) { value = m[1]; }
						}
						params[k] = value.replace(/"/g, "'");
					}
				}, this));
			}
			return params;
		},

		// imgUploader
		imgLoadModal() {
			$.log('imgLoadModal');
			if (this.options.imgupload === true) {
				this.$modal.find('#imguploader').dragfileupload({
					url: this.strf(this.options.img_uploadurl, this.options),
					extraParams: { maxwidth: this.options.img_maxwidth, maxheight: this.options.img_maxheight },
					themePrefix: this.options.themePrefix,
					themeName:   this.options.themeName,
					success: $.proxy(function(data) {
						this.$txtArea.insertImage(data.image_link, data.thumb_link);
						this.closeModal();
						this.updateUI();
					}, this)
				});
				this.$modal.find('#fileupl').on('change', function() { $('#fupform').submit(); });
				this.$modal.find('#fupform').on('submit', $.proxy(function(e) {
					$(e.target).parents('#imguploader').hide()
						.after('<div class="loader"><img src="' + (typeof route !== 'undefined' ? route.assets : '') + '/images/loading.gif" /><br/><br/><span>Cargando</span></div>')
						.parent().css('text-align', 'center');
				}, this));
			} else {
				this.$modal.find('.hastabs').removeClass('hastabs');
				this.$modal.find('#imguploader').parents('.tab-cont').remove();
				this.$modal.find('.wbbm-tablist').remove();
			}
		},

		// Browser fixes
		isChrome()  { return !!window.chrome; },

		fixTableTransform(html) {
			if (!html) { return ''; }
			if (this.options.buttons.includes('table')) {
				return html.replace(/\<(\/*?(table|tr|td|tbody))[^>]*\>/ig, '');
			} else {
				return html.replace(/\<(\/*?(table|tr|td))[^>]*\>/ig, '[$1]'.toLowerCase())
					.replace(/\<\/*tbody[^>]*\>/ig, '');
			}
		}
	};

	// ─── Logging ─────────────────────────────────────────────────────────────
	$.log = function(msg) {
		if (typeof debug !== 'undefined' && debug === true && typeof console !== 'undefined') {
			console.log(msg);
		}
	};

	// ─── jQuery plugin ────────────────────────────────────────────────────────
	$.fn.wysibb = function(settings) {
		return this.each(function() {
			if (!$(this).data('wbb')) { new $.wysibb(this, settings); }
		});
	};

	// ─── Drag resize ─────────────────────────────────────────────────────────
	$.fn.wdrag = function(opt) {
		if (!opt.scope) { opt.scope = this; }
		let start = { x: 0, y: 0, height: 0 };
		let drag;

		opt.scope.drag_mousedown = function(e) {
			e.preventDefault();
			start = { x: e.pageX, y: e.pageY, height: opt.height, sheight: opt.scope.$body.height() };
			drag = true;
			$(document).on('mousemove', $.proxy(opt.scope.drag_mousemove, this));
			$(this).addClass('drag');
		};
		opt.scope.drag_mouseup = function(e) {
			if (drag === true) {
				e.preventDefault();
				$(document).off('mousemove', opt.scope.drag_mousemove);
				$(this).removeClass('drag');
				drag = false;
			}
		};
		opt.scope.drag_mousemove = function(e) {
			e.preventDefault();
			const axisY = opt.axisY ? e.pageY - start.y : 0;
			if (axisY !== 0) {
				const nheight = start.sheight + axisY;
				if (nheight > start.height && nheight <= opt.scope.options.resize_maxheight) {
					const prop = opt.scope.options.autoresize === true ? 'min-height' : 'height';
					if (opt.scope.options.bbmode === true) { opt.scope.$txtArea.css(prop, nheight + 'px'); }
					else { opt.scope.$body.css(prop, nheight + 'px'); }
				}
			}
		};

		$(this).on('mousedown', opt.scope.drag_mousedown);
		$(document).on('mouseup', $.proxy(opt.scope.drag_mouseup, this));
	};

	// ─── API pública ──────────────────────────────────────────────────────────
	$.fn.getDoc = function() { return this.data('wbb').doc; };
	$.fn.getSelectText = function(fromTA) { return this.data('wbb').getSelectText(fromTA); };
	$.fn.bbcode = function(data) {
		if (typeof data !== 'undefined') {
			if (this.data('wbb').options.bbmode) { this.data('wbb').$txtArea.val(data); }
			else { this.data('wbb').$body.html(this.data('wbb').getHTML(data)); }
			return this;
		}
		return this.data('wbb').getBBCode();
	};
	$.fn.htmlcode = function(data) {
		if (!this.data('wbb').options.onlyBBMode && this.data('wbb').inited === true) {
			if (typeof data !== 'undefined') { this.data('wbb').$body.html(data); return this; }
			return this.data('wbb').getHTML(this.data('wbb').$txtArea.val());
		}
	};
	$.fn.getBBCode = function() { return this.data('wbb').getBBCode(); };
	$.fn.getHTML = function() { const w = this.data('wbb'); return w.getHTML(w.$txtArea.val()); };
	$.fn.getHTMLByCommand = function(cmd, p) { return this.data('wbb').getHTMLByCommand(cmd, p); };
	$.fn.getBBCodeByCommand = function(cmd, p) { return this.data('wbb').getBBCodeByCommand(cmd, p); };
	$.fn.insertAtCursor = function(data, fbm) { this.data('wbb').insertAtCursor(data, fbm); return this.data('wbb'); };
	$.fn.execCommand = function(cmd, val) { this.data('wbb').execCommand(cmd, val); return this.data('wbb'); };
	$.fn.insertImage = function(imgurl) { const e = this.data('wbb'); this.insertAtCursor(e.getCodeByCommand('img', { src: imgurl })); return e; };
	$.fn.sync = function() { this.data('wbb').sync(); return this.data('wbb'); };
	$.fn.destroy = function() { this.data('wbb').destroy(); };
	$.fn.queryState = function(cmd) { return this.data('wbb').queryState(cmd); };

})(jQuery);


// ─────────────────────────────────────────────────────────────────────────────
//  Lista default de emojis (función auxiliar, llamada desde el constructor)
// ─────────────────────────────────────────────────────────────────────────────
function _buildDefaultSmileList() {
	const base = (typeof route !== 'undefined' && route.smiles) ? route.smiles : '/smiles';
	const s = (file) => `<img src="${base}/${file}">`;
	return [
		{ title: ':poop:',          img: s('1f4a9.png'), bbcode: ':poop:' },
		{ title: ':goblin:',        img: s('1f47a.png'), bbcode: ':goblin:' },
		{ title: ':ghost:',         img: s('1f47b.png'), bbcode: ':ghost:' },
		{ title: ':alien:',         img: s('1f47d.png'), bbcode: ':alien:' },
		{ title: ':imp:',           img: s('1f47f.png'), bbcode: ':imp:' },
		{ title: ':blush:',         img: s('1f60a.png'), bbcode: ':blush:' },
		{ title: ':yum:',           img: s('1f60b.png'), bbcode: ':yum:' },
		{ title: ':relieved:',      img: s('1f60c.png'), bbcode: ':relieved:' },
		{ title: ':heart_eyes:',    img: s('1f60d.png'), bbcode: ':heart_eyes:' },
		{ title: ':sunglasses:',    img: s('1f60e.png'), bbcode: ':sunglasses:' },
		{ title: ':smirk:',         img: s('1f60f.png'), bbcode: ':smirk:' },
		{ title: ':kissing_closed_eyes:', img: s('1f61a.png'), bbcode: ':kissing_closed_eyes:' },
		{ title: ':stuck_out_tongue:', img: s('1f61b.png'), bbcode: ':stuck_out_tongue:' },
		{ title: ':stuck_out_tongue_winking_eye:', img: s('1f61c.png'), bbcode: ':stuck_out_tongue_winking_eye:' },
		{ title: ':stuck_out_tongue_closed_eyes:', img: s('1f61d.png'), bbcode: ':stuck_out_tongue_closed_eyes:' },
		{ title: ':pensive:',       img: s('1f61e.png'), bbcode: ':pensive:' },
		{ title: ':worried:',       img: s('1f61f.png'), bbcode: ':worried:' },
		{ title: ':sleepy:',        img: s('1f62a.png'), bbcode: ':sleepy:' },
		{ title: ':tired_face:',    img: s('1f62b.png'), bbcode: ':tired_face:' },
		{ title: ':grimacing:',     img: s('1f62c.png'), bbcode: ':grimacing:' },
		{ title: ':sob:',           img: s('1f62d.png'), bbcode: ':sob:' },
		{ title: ':open_mouth:',    img: s('1f62e.png'), bbcode: ':open_mouth:' },
		{ title: ':hushed:',        img: s('1f62f.png'), bbcode: ':hushed:' },
		{ title: ':smiley_cat:',    img: s('1f63a.png'), bbcode: ':smiley_cat:' },
		{ title: ':smile_cat:',     img: s('1f63b.png'), bbcode: ':smile_cat:' },
		{ title: ':heart_eyes_cat:',img: s('1f63c.png'), bbcode: ':heart_eyes_cat:' },
		{ title: ':kissing_cat:',   img: s('1f63d.png'), bbcode: ':kissing_cat:' },
		{ title: ':pouting_cat:',   img: s('1f63e.png'), bbcode: ':pouting_cat:' },
		{ title: ':crying_cat:',    img: s('1f63f.png'), bbcode: ':crying_cat:' },
		{ title: ':ogre:',          img: s('1f479.png'), bbcode: ':ogre:' },
		{ title: ':skull:',         img: s('1f480.png'), bbcode: ':skull:' },
		{ title: ':grinning:',      img: s('1f600.png'), bbcode: ':grinning:' },
		{ title: ':grin:',          img: s('1f601.png'), bbcode: ':grin:' },
		{ title: ':joy:',           img: s('1f602.png'), bbcode: ':joy:' },
		{ title: ':smiley:',        img: s('1f603.png'), bbcode: ':smiley:' },
		{ title: ':smile:',         img: s('1f604.png'), bbcode: ':smile:' },
		{ title: ':sweat_smile:',   img: s('1f605.png'), bbcode: ':sweat_smile:' },
		{ title: ':laughing:',      img: s('1f606.png'), bbcode: ':laughing:' },
		{ title: ':innocent:',      img: s('1f607.png'), bbcode: ':innocent:' },
		{ title: ':smiling_imp:',   img: s('1f608.png'), bbcode: ':smiling_imp:' },
		{ title: ':wink:',          img: s('1f609.png'), bbcode: ':wink:' },
		{ title: ':neutral_face:',  img: s('1f610.png'), bbcode: ':neutral_face:' },
		{ title: ':expressionless:',img: s('1f611.png'), bbcode: ':expressionless:' },
		{ title: ':unamused:',      img: s('1f612.png'), bbcode: ':unamused:' },
		{ title: ':sweat:',         img: s('1f613.png'), bbcode: ':sweat:' },
		{ title: ':confused:',      img: s('1f615.png'), bbcode: ':confused:' },
		{ title: ':confounded:',    img: s('1f616.png'), bbcode: ':confounded:' },
		{ title: ':kissing:',       img: s('1f617.png'), bbcode: ':kissing:' },
		{ title: ':kissing_heart:', img: s('1f618.png'), bbcode: ':kissing_heart:' },
		{ title: ':kissing_smiling_eyes:', img: s('1f619.png'), bbcode: ':kissing_smiling_eyes:' },
		{ title: ':angry:',         img: s('1f620.png'), bbcode: ':angry:' },
		{ title: ':rage:',          img: s('1f621.png'), bbcode: ':rage:' },
		{ title: ':cry:',           img: s('1f622.png'), bbcode: ':cry:' },
		{ title: ':persevere:',     img: s('1f623.png'), bbcode: ':persevere:' },
		{ title: ':triumph:',       img: s('1f624.png'), bbcode: ':triumph:' },
		{ title: ':disappointed_relieved:', img: s('1f625.png'), bbcode: ':disappointed_relieved:' },
		{ title: ':frowning:',      img: s('1f626.png'), bbcode: ':frowning:' },
		{ title: ':anguished:',     img: s('1f627.png'), bbcode: ':anguished:' },
		{ title: ':fearful:',       img: s('1f628.png'), bbcode: ':fearful:' },
		{ title: ':weary:',         img: s('1f629.png'), bbcode: ':weary:' },
		{ title: ':cold_sweat:',    img: s('1f630.png'), bbcode: ':cold_sweat:' },
		{ title: ':scream:',        img: s('1f631.png'), bbcode: ':scream:' },
		{ title: ':astonished:',    img: s('1f632.png'), bbcode: ':astonished:' },
		{ title: ':flushed:',       img: s('1f633.png'), bbcode: ':flushed:' },
		{ title: ':sleeping:',      img: s('1f634.png'), bbcode: ':sleeping:' },
		{ title: ':dizzy_face:',    img: s('1f635.png'), bbcode: ':dizzy_face:' },
		{ title: ':no_mouth:',      img: s('1f636.png'), bbcode: ':no_mouth:' },
		{ title: ':mask:',          img: s('1f637.png'), bbcode: ':mask:' },
		{ title: ':smile_cat:',     img: s('1f638.png'), bbcode: ':smile_cat:' },
		{ title: ':joy_cat:',       img: s('1f639.png'), bbcode: ':joy_cat:' },
		{ title: ':scream_cat:',    img: s('1f640.png'), bbcode: ':scream_cat:' },
		{ title: ':slight_frown:',  img: s('1f641.png'), bbcode: ':slight_frown:' },
		{ title: ':slight_smile:',  img: s('1f642.png'), bbcode: ':slight_smile:' },
		{ title: ':upside_down_face:', img: s('1f643.png'), bbcode: ':upside_down_face:' },
		{ title: ':rolling_eyes:',  img: s('1f644.png'), bbcode: ':rolling_eyes:' },
		{ title: ':zipper_mouth_face:', img: s('1f910.png'), bbcode: ':zipper_mouth_face:' },
		{ title: ':money_mouth_face:', img: s('1f911.png'), bbcode: ':money_mouth_face:' },
		{ title: ':face_with_thermometer:', img: s('1f912.png'), bbcode: ':face_with_thermometer:' },
		{ title: ':nerd_face:',     img: s('1f913.png'), bbcode: ':nerd_face:' },
		{ title: ':thinking_face:', img: s('1f914.png'), bbcode: ':thinking_face:' },
		{ title: ':face_with_head_bandage:', img: s('1f915.png'), bbcode: ':face_with_head_bandage:' },
		{ title: ':robot_face:',    img: s('1f916.png'), bbcode: ':robot_face:' },
		{ title: ':hugging_face:',  img: s('1f917.png'), bbcode: ':hugging_face:' },
		{ title: ':cowboy_hat_face:', img: s('1f920.png'), bbcode: ':cowboy_hat_face:' },
		{ title: ':clown_face:',    img: s('1f921.png'), bbcode: ':clown_face:' },
		{ title: ':nauseated_face:', img: s('1f922.png'), bbcode: ':nauseated_face:' },
		{ title: ':rofl:',          img: s('1f923.png'), bbcode: ':rofl:' },
		{ title: ':drooling_face:', img: s('1f924.png'), bbcode: ':drooling_face:' },
		{ title: ':lying_face:',    img: s('1f925.png'), bbcode: ':lying_face:' },
		{ title: ':face_palm:',     img: s('1f926.png'), bbcode: ':face_palm:' },
		{ title: ':sneezing_face:', img: s('1f927.png'), bbcode: ':sneezing_face:' },
		{ title: ':star_struck:',   img: s('1f929.png'), bbcode: ':star_struck:' },
		{ title: ':smiling_face_with_3_hearts:', img: s('1f970.png'), bbcode: ':smiling_face_with_3_hearts:' },
		{ title: ':relaxed:',       img: s('263a.png'),  bbcode: ':relaxed:' },
		{ title: ':frowning_face:', img: s('2639.png'),  bbcode: ':frowning_face:' }
	];
}


// ─────────────────────────────────────────────────────────────────────────────
//  Drag & Drop File Uploader
// ─────────────────────────────────────────────────────────────────────────────
(function($) {
	'use strict';

	$.fn.dragfileupload = function(options) {
		return this.each(function() {
			new FileUpload(this, options).init();
		});
	};

	function FileUpload(el, options) {
		this.$block = $(el);
		this.opt = $.extend({
			url:         false,
			success:     false,
			extraParams: false,
			fileParam:   'img',
			validation:  '\\.(jpg|png|gif|jpeg|webp|avif)$',
			t1: 'Suelta el archivo aquí',
			t2: 'o también puedes'
		}, options);
	}

	FileUpload.prototype = {
		init() {
			if (!window.FormData) { return; }
			this.$block.addClass('drag');
			this.$block.prepend('<div class="p2">' + this.opt.t2 + '</div>');
			this.$block.prepend('<div class="p">'  + this.opt.t1 + '</div>');
			this.$block.on('dragover',  function() { $(this).addClass('dragover');    return false; });
			this.$block.on('dragleave', function() { $(this).removeClass('dragover'); return false; });

			const xhr = jQuery.ajaxSettings.xhr();
			if (xhr.upload) {
				xhr.upload.addEventListener('progress', $.proxy(function(e) {
					const p = parseInt(e.loaded / e.total * 100, 10);
					this.$loader.children('span').text('Cargando: ' + p + '%');
				}, this), false);
			}

			this.$block[0].ondrop = $.proxy(function(e) {
				e.preventDefault();
				this.$block.removeClass('dragover');
				const ufile = e.dataTransfer.files[0];
				if (this.opt.validation && !ufile.name.match(new RegExp(this.opt.validation))) {
					this.error('La información ingresada no es válida');
					return false;
				}
				const fData = new FormData();
				fData.append(this.opt.fileParam, ufile);
				if (this.opt.extraParams) {
					$.each(this.opt.extraParams, (k, v) => fData.append(k, v));
				}
				this.$loader = $('<div class="loader"><img src="' + this.opt.themePrefix + '/' + this.opt.themeName + '/img/loader.gif" /><br/><span>Cargando...</span></div>');
				this.$block.html(this.$loader);

				$.ajax({
					type:        'POST',
					url:         this.opt.url,
					data:        fData,
					processData: false,
					contentType: false,
					xhr:         () => xhr,
					dataType:    'json',
					success: $.proxy(function(data) {
						if (data && data.status === 1) { this.opt.success(data); }
						else { this.error(data.msg || 'Ha ocurrido un error mientras se cargaban los archivos'); }
					}, this),
					error: $.proxy(function() { this.error('Ha ocurrido un error mientras se cargaban los archivos'); }, this)
				});
			}, this);
		},

		error(msg) {
			this.$block.find('.upl-error').remove().end()
				.append('<span class="upl-error">' + msg + '</span>')
				.addClass('wbbm-brdred');
		}
	};

})(jQuery);