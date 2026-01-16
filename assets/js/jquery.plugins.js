/**
 * Plugins globales que utilizará el script.
 * Los plugins: (fueron obtenidos desde https://locutus.io/php/)
 *  # Empty 
 *  # Htmlspecialchars_decode 
 *  # Number_format 
*/
empty = n => {let e,r,t;const f=[undefined,null,!1,0,"","0"];for(r=0,t=f.length;r<t;r++)if(n===f[r])return!0;if("object"==typeof n){for(e in n)if(n.hasOwnProperty(e))return!1;return!0}return!1}
htmlspecialchars_decode = (e,E) => {let T=0,_=0,t=!1;void 0===E&&(E=2),e=e.toString().replace(/&lt;/g,"<").replace(/&gt;/g,">");const c={ENT_NOQUOTES:0,ENT_HTML_QUOTE_SINGLE:1,ENT_HTML_QUOTE_DOUBLE:2,ENT_COMPAT:2,ENT_QUOTES:3,ENT_IGNORE:4};if(0===E&&(t=!0),"number"!=typeof E){for(E=[].concat(E),_=0;_<E.length;_++)0===c[E[_]]?t=!0:c[E[_]]&&(T|=c[E[_]]);E=T}return E&c.ENT_HTML_QUOTE_SINGLE&&(e=e.replace(/&#0*39;/g,"'")),t||(e=e.replace(/&quot;/g,'"')),e=e.replace(/&amp;/g,"&")}
number_format = (e,t,n,i) => {e=(e+"").replace(/[^0-9+\-Ee.]/g,"");const r=isFinite(+e)?+e:0,o=isFinite(+t)?Math.abs(t):0,a=void 0===i?",":i,d=void 0===n?".":n;let l="";return l=(o?function(e,t){if(-1===(""+e).indexOf("e"))return+(Math.round(e+"e+"+t)+"e-"+t);{const n=(""+e).split("e");let i="";return+n[1]+t>0&&(i="+"),(+(Math.round(+n[0]+"e"+i+(+n[1]+t))+"e-"+t)).toFixed(t)}}(r,o).toString():""+Math.round(r)).split("."),l[0].length>3&&(l[0]=l[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,a)),(l[1]||"").length<o&&(l[1]=l[1]||"",l[1]+=new Array(o-l[1].length+1).join("0")),l.join(d)}


/* Easing 1.3 */
jQuery.extend( jQuery.easing,{def: 'easeOutQuad',swing: function (x, t, b, c, d) {return jQuery.easing[jQuery.easing.def](x, t, b, c, d);},easeInQuad: function (x, t, b, c, d) {return c*(t/=d)*t + b;},easeOutQuad: function (x, t, b, c, d) {return -c *(t/=d)*(t-2) + b;},easeInOutQuad: function (x, t, b, c, d) {if ((t/=d/2) < 1) return c/2*t*t + b;return -c/2 * ((--t)*(t-2) - 1) + b;},easeInCubic: function (x, t, b, c, d) {return c*(t/=d)*t*t + b;},easeOutCubic: function (x, t, b, c, d) {return c*((t=t/d-1)*t*t + 1) + b;},easeInOutCubic: function (x, t, b, c, d) {if ((t/=d/2) < 1) return c/2*t*t*t + b;return c/2*((t-=2)*t*t + 2) + b;},easeInQuart: function (x, t, b, c, d) {return c*(t/=d)*t*t*t + b;},easeOutQuart: function (x, t, b, c, d) {return -c * ((t=t/d-1)*t*t*t - 1) + b;},easeInOutQuart: function (x, t, b, c, d) {if ((t/=d/2) < 1) return c/2*t*t*t*t + b;return -c/2 * ((t-=2)*t*t*t - 2) + b;},easeInQuint: function (x, t, b, c, d) {return c*(t/=d)*t*t*t*t + b;},easeOutQuint: function (x, t, b, c, d) {return c*((t=t/d-1)*t*t*t*t + 1) + b;},easeInOutQuint: function (x, t, b, c, d) {if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;return c/2*((t-=2)*t*t*t*t + 2) + b;},easeInSine: function (x, t, b, c, d) {return -c * Math.cos(t/d * (Math.PI/2)) + c + b;},easeOutSine: function (x, t, b, c, d) {return c * Math.sin(t/d * (Math.PI/2)) + b;},easeInOutSine: function (x, t, b, c, d) {return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;},easeInExpo: function (x, t, b, c, d) {return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;},easeOutExpo: function (x, t, b, c, d) {return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;},easeInOutExpo: function (x, t, b, c, d) {if (t==0) return b;if (t==d) return b+c;if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;},easeInCirc: function (x, t, b, c, d) {return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;},easeOutCirc: function (x, t, b, c, d) {return c * Math.sqrt(1 - (t=t/d-1)*t) + b;},easeInOutCirc: function (x, t, b, c, d) {if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;},easeInElastic: function (x, t, b, c, d) {var s=1.70158;var p=0;var a=c;if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;if (a < Math.abs(c)) { a=c; var s=p/4; }else var s = p/(2*Math.PI) * Math.asin (c/a);return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;},easeOutElastic: function (x, t, b, c, d) {var s=1.70158;var p=0;var a=c;if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;if (a < Math.abs(c)) { a=c; var s=p/4; }else var s = p/(2*Math.PI) * Math.asin (c/a);return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;},easeInOutElastic: function (x, t, b, c, d) {var s=1.70158;var p=0;var a=c;if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);if (a < Math.abs(c)) { a=c; var s=p/4; }else var s = p/(2*Math.PI) * Math.asin (c/a);if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;},easeInBack: function (x, t, b, c, d, s) {if (s == undefined) s = 1.70158;return c*(t/=d)*t*((s+1)*t - s) + b;},easeOutBack: function (x, t, b, c, d, s) {if (s == undefined) s = 1.70158;return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;},easeInOutBack: function (x, t, b, c, d, s) {if (s == undefined) s = 1.70158; if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;},easeInBounce: function (x, t, b, c, d) {return c - jQuery.easing.easeOutBounce (x, d-t, 0, c, d) + b;},easeOutBounce: function (x, t, b, c, d) {if ((t/=d) < (1/2.75)) {return c*(7.5625*t*t) + b;} else if (t < (2/2.75)) {return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;} else if (t < (2.5/2.75)) {return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;} else {return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;}},easeInOutBounce: function (x, t, b, c, d) {if (t < d/2) return jQuery.easing.easeInBounce (x, t*2, 0, c, d) * .5 + b;return jQuery.easing.easeOutBounce (x, t*2-d, 0, c, d) * .5 + c*.5 + b;}});

$.parseResponse = (request) => {
   const sepIndex = request.indexOf(':');
   if (sepIndex === -1) return { status: 0, message: request }; // fallback
   return { 
      status: parseInt(request.substring(0, sepIndex), 10), 
      message: request.substring(sepIndex + 1).trim()
   };
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
         confirm: { action: 'close', text: 'Aceptar' },
         cancel: { action: 'close', text: 'Cerrar' }
      }
   },
   template: `<div class="dialog-mask"><div class="dialog"></div></div>`,
   config: {},
   open() {
      if ($('.dialog-mask').length) {
         $('.dialog').empty(); // <-- CLAVE
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
      const html = `<div class="dialog-loading"><span class="spinner"></span><p>Cargando...</p></div>`;
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
   alert(title, body, buttons = null) {
      this.close();
      this.init({
         title,
         body,
         buttons: buttons || {
            confirm: { text: 'Aceptar', action: 'close' }
         }
      });
   },
   loading(title = 'Procesando') {
      this.close();
      this.init({
         title,
         loading: true,
         buttonClose: false,
         maskClose: false
      });
   }
};
// complemento de dialog
dialog.toast = function (options = {}) {
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

$(() => {
   /*
   dialog.alert(
      'Atención',
      'Operación realizada correctamente'
   );
   */
   /*dialog.init({
      buttonClose: true,
      title: 'Prueba',
      body: 'El contenido del mismo',
      buttons: {
         confirm: {
            text: 'Recargar',
            action: () => location.reload()
         },
         cancel: {
            text: 'Cerrar',
            action: 'close'
         }
      }
   });*/

   //dialog.loading('Cargando datos...');   

   /*dialog.toast({
      type: 'success',
      title: 'Guardado',
      message: 'Los cambios se guardaron correctamente',
      duration: 4000,
      position: 'top-right'
   });*/

   /*dialog.toast({
      type: 'success',
      title: 'Éxito',
      message: 'Post publicado correctamente'
   });*/

  /* dialog.toast({
      type: 'danger',
      message: 'Error al guardar los datos',
      position: 'bottom-left'
   });*/


});

/* MyDialog */
var mydialog = {
   is_show: false,
   class_aux: '',
   mask_close: true,
   close_button: false,
   show: class_aux => {
      if(this.is_show) return;
      else this.is_show = true;
      // Plantilla
      $('#mydialog').html('<div id="dialog"><div id="title"></div><div id="cuerpo"><div id="procesando"><div id="mensaje"></div></div><div id="modalBody"></div><div id="buttons"></div></div></div>');
      // Clase auxiliar
      if(class_aux==true) $('#mydialog').addClass(this.class_aux);
      else if(this.class_aux != '') {
         $('#mydialog').removeClass(this.class_aux);
         this.class_aux = '';
      }
      // Mascara
      if(this.mask_close) $('#mask').on('click', () =>  mydialog.close());
      else $('#mask').off('click');
      // Estilos adicionales para la mascara
      $('#mask').css({
         'width': $(document).width(),
         'height': $(document).height(),
         'display': 'block'
      });
      // Botón cerrar
      if(this.close_button) {
         $('#mydialog #dialog').append('<img class="close_dialog" src="'+ global_data.img +'images/close.gif" onclick="mydialog.close()" />');
      } else $('#mydialog #dialog .close_dialog').remove();
      // Dependiendo de la versión del navegador
      //status = (jQuery.browser.msie && jQuery.browser.version < 7) ? 'absolute' : 'fixed';
      $('#mydialog #dialog').css('position', 'absolute');
      $('#mydialog #dialog').fadeIn('fast');
   },
   close: function(){
      //Vuelve todos los parametros por default
      this.class_aux = '';
      this.mask_close = true;
      this.close_button = false;
      this.is_show = false;
      $('#mask').css('display', 'none');
      $('#mydialog #dialog').fadeOut('fast', () => $(this).remove());
      this.procesando_fin();
   },
   center: function(){
      if($('#mydialog #dialog').height() > $(window).height()-60) {
         $('#mydialog #dialog').css({'position':'absolute', 'top':20});
      } else {
         $('#mydialog #dialog').css('top', $(window).height()/2-$('#mydialog #dialog').height()/2);
      }
      $('#mydialog #dialog').css('left', $(window).width()/2-$('#mydialog #dialog').width()/2);
   },
   title: title => $('#mydialog #title').html(title),
   body: function(body, width, height){
      $('#mydialog #modalBody').html(body).css({minWidth: '400px'});
   },
   buttons: function(display_all, btn1_display, btn1_val, btn1_action, btn1_enabled, btn1_focus, btn2_display, btn2_val, btn2_action, btn2_enabled, btn2_focus){
      if(!display_all){
         $('#mydialog #buttons').css('display', 'none').html('');
         return;
      }
      if(btn1_action=='close') btn1_action='mydialog.close()';
      if(btn2_action=='close' || !btn2_val) btn2_action='mydialog.close()';
      if(!btn2_val){
         btn2_val = 'Cancelar';
         btn2_enabled = true;
      }
      var html = '';
      if(btn1_display)
         html += '<input type="button" class="mBtn btnOk'+(btn1_enabled?'':' disabled')+'" style="display:'+(btn1_display?'inline-block':'none')+'"'+(btn1_display?' value="'+btn1_val+'"':'')+(btn1_display?' onclick="'+btn1_action+'"':'')+(btn1_enabled?'':' disabled')+' />';
      if(btn2_display)
         html += ' <input type="button" class="mBtn btnCancel'+(btn1_enabled?'':' disabled')+'" style="display:'+(btn2_display?'inline-block':'none')+'"'+(btn2_display?' value="'+btn2_val+'"':'')+(btn2_display?' onclick="'+btn2_action+'"':'')+(btn2_enabled?'':' disabled')+' />';
      $('#mydialog #buttons').html(html).css('display', 'inline-block');
      if(btn1_focus) $('#mydialog #buttons .mBtn.btnOk').focus();
      else if(btn2_focus) $('#mydialog #buttons .mBtn.btnCancel').focus();
   },
   alert: function(title, body, reload){
      this.show();
      this.title(title);
      this.body(body);
      this.buttons(true, true, 'Aceptar', 'mydialog.close();' + (reload ? 'location.reload();' : 'close'), true, true, false);
      this.center();
   },
   error_500: function(fun_reintentar){
      setTimeout(function(){
         mydialog.procesando_fin();
         mydialog.show();
         mydialog.title('Error');
         mydialog.body('Error al intentar procesar lo solicitado');
         mydialog.buttons(true, true, 'Reintentar', 'mydialog.close();'+fun_reintentar, true, true, true, 'Cancelar', 'close', true, false);
         mydialog.center();
      }, 200);
   },
   procesando_inicio: function(value, title){
      if(!this.is_show){
         this.show();
         this.title(title);
         this.body('');
         this.buttons(false, false);
         this.center();
      }
      $('#mydialog #procesando #mensaje').html('<img src="'+global_data.img+'images/loading.gif" />');
      $('#mydialog #procesando').fadeIn('fast');
   },
   procesando_fin: () => $('#mydialog #procesando').fadeOut('fast')
};
document.onkeydown = function(e){
   key = (e==null)?event.keyCode:e.which;
   if(key == 27) mydialog.close();
};