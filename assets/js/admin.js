/**
 * @param object | recibimos un objecto como parámetro
*/
function admin_send_post(objeto) {
	const { pagina, parametros } = objeto;
	const xhr = $.post(`${route.url}/${pagina}.php`, parametros, response => response)
	if(typeof objeto.done !== 'undefined') xhr.done(() => objeto.done)
	return xhr
}

const api = (endpoint, param, fn) => $.post(`${route.url}/${endpoint}.php`, param, fn);

function modal_rapido(modal) {
	const { title, body, action, btnOk } = modal;
	dialog.init({ title, body,
      buttons: {
         confirm: { text: (btnOk === '' ? 'S&iacute;' : btnOk), action: () => action },
         cancel: { text: 'No',  action: 'close' }
      }
   });
}
const noticias = nid => {
	$('#loading').fadeIn(250);
	api('admin-noticias-setInActive', { nid }, response => {
		const { status, message } = $.parseResponse(response);
		if(status === 0) {
			dialog.alert('Error', message);
			return;
		}
		if(status === 1 || status === 2) {
			let color = (status === 1) ? 'green' : 'purple';
			$(`#status_noticia_${nid} > span`).removeClass('bg-purple-100 text-purple-800 bg-green-100 text-green-800')
			.addClass(`bg-${color}-100 text-${color}-800`)
			.text((status === 1 ? 'Activa' : 'Inactiva'))
		}
		$('#loading').fadeOut(350)
	})
}

const tema = {
	usar(tid) {
		api('/tema-usar', { tid }, response => {
	      const { status, message } = $.parseResponse(response);
	      dialog.alert((status === 0 ? 'Error' : 'Bien'), message, status === 1);
			return;
		})
	},
	nuevo(next = false) {
		if(!next) {
			dialog.init({ 
        		title: 'Instalar nuevo theme',
        		body: '<label for="path" class="font-medium text-gray-700 dark:text-gray-300">Nombre del theme</label><input type="text" id="path" name="path" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Nombre del theme" />',
		      buttons: {
		         confirm: { text: 'Instalar', action: () => tema.nuevo(true) }
		      }
		   });
        	return;
      } else {
	      const input = $('#path').val();
	      if(input === '') {
	      	dialog.alert('Error', 'No puede estar vacio');
				return;
	      }
	      api('tema-nuevo', { path: input }, response => {
	      	const { status, message } = $.parseResponse(response);
	      	dialog.alert((status === 0 ? 'Error' : 'Bien'), message, status === 1);
				return;
	      });
	   }
	}
}

const medallas = {
	borrar(mid, gew = 1) {
		const title = 'Borrar medalla';
		let status = (gew === 1);
		if(status || gew === 2) {
			let body = status ? '&#191;Quiere borrar esta medalla?' : 'Si borra la medalla, los usuarios que tengan esta medalla la perder&aacute;n, &#191;seguro que quiere continuar?';
			dialog.init({ 
        		title,
        		body,
		      buttons: {
		         confirm: { text: 'S&iacute;', action: () => medallas.borrar(mid, (status ? 2 : 3)) }
		      }
		   });
	   } else {
	   	$('#loading').fadeIn(250);
	   	api('admin-medalla-borrar', { medal_id: mid }, response => {
	   		const { status, message } = $.parseResponse(response);
	   		dialog.alert((status ? 'Hecho' : 'Opps!'), message);
	   		if(status === 1) {
	   			$('#medal_id_' + mid).fadeOut()
	   		}
	   	});
		}
	},
   asignar(mid, gew) {
   	if(!gew) {
		   api('admin-medalla-asignar-form', {}, response => {
	   		const { status, message } = $.parseResponse(response);
	   		dialog.init({ 
	        		title: 'Asignar medalla',
	        		body: '<div id="AFormInputs">'+message+'</div>',
			      buttons: {
			         confirm: { text: 'Asignar', action: () => medallas.asignar(mid, true) }
			      }
		  		});
		   });
		} else {
		   $('#loading').fadeIn(250);
		   const params = {
				mid: mid, 
				m_usuario: $('#m_usuario').val(), 
				pid: $('#m_post').val(), 
				fid: $('#m_foto').val()
		   };
		   api('admin-medalla-asignar', params, response => {
	   		const { status, message } = $.parseResponse(response);
	   		dialog.alert((status ? 'Hecho' : 'Opps!'), message);
	   		if(status === 1) {
	   			$('#total_med_assig_' + mid).text(parseInt($('#total_med_assig_' + mid).text()) + 1);
         		$('#loading').fadeOut(350);
	   		}
		   })
		}
   },
	borrar_asignacion: async (aid, mid, gew) => {
      if(!gew) {
      	mydialog.show();
      	mydialog.title('Borrar Asignacion');
      	mydialog.body('&#191;Quiere continuar borrando esta asignaci&oacute;n?');
      	mydialog.buttons(true, true, 'S&iacute;', 'admin.medallas.borrar_asignacion(' + aid + ',' + mid + ', true)', true, false, true, 'No', 'close', true, true);
      	mydialog.center();
      } else {
      	$('#loading').fadeIn(250);
			var a = await admin_send_post({
				pagina: 'admin-medallas-borrar-asignacion', 
				parametros: ['aid=' + aid, 'mid=' + mid].join('&'),
				done: $('#assign_id_' + aid).fadeOut()
			})
			mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
        	mydialog.center();
        
        $('#loading').fadeOut(350);
		}
	},
}

/** 
 * Nueva organización
*/
const admin = {
	// Afiliados
	afs: {
	   borrar: async (aid, gew) => {
         if(!gew) {
         	modal_rapido({
         		titulo: 'Borrar Afiliado',
         		contenido: '&#191;Quiere borrar este afiliado?',
         		accion: 'admin.afs.borrar(' + aid + ', 1)'
         	})
         } else {
         	$('#loading').fadeIn(250);
         	var a = await admin_send_post({
         		pagina: 'afiliado-borrar',
         		parametros: 'afid=' + aid
         	})
         	mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
         	if(a.charAt(0) == '1') $('#few_' + aid).fadeOut().remove()
         	mydialog.center();
         	$('#loading').fadeOut(350);
         }
      },
      accion: async aid => {
    		$('#loading').fadeIn(250);
    		var h = await admin_send_post({
    			pagina: 'afiliado-setactive',
    			parametros: 'aid=' + aid
    		})
    		if(h.charAt(0) === '0') mydialog.alert('Error', h.substring(3))
			var change = (h.charAt(0) === '1') ? ['green', 'Activo'] : ['purple', 'Inactivo'];
    		$('#status_afiliado_' + aid).html('<font color="'+change[0]+'">'+change[1]+'</font>');
    		$('#loading').fadeOut(250);
  		}
	},
	// Bloqueos
	blacklist: {
	   borrar: async (id, gew) => {
         if(!gew) {
         	modal_rapido({
         		titulo: 'Retirar Bloqueo',
         		contenido: '&#191;Quiere retirar este bloqueo?',
         		accion: 'admin.blacklist.borrar(' + id + ', true)'
         	})
         } else {
         	$('#loading').fadeIn(250);
         	var a = await admin_send_post({
         		pagina: 'admin-blacklist-delete',
         		parametros: 'bid=' + id
         	})
         	mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
         	mydialog.center();
         	if(a.charAt(0) === '1') $('#block_' + id).fadeOut(); 
         	$('#loading').fadeOut(350);
         }
      } 
   },
   // Censuras 
   badwords: {
	   borrar: async (wid, gew) => {
	   	if(!gew) {
         	modal_rapido({
         		titulo: 'Retirar Filtro',
         		contenido: '&#191;Quiere retirar este filtro?',
         		accion: 'admin.badwords.borrar(' + wid + ', true)'
         	})
	   	} else {
	   		$('#loading').fadeIn(250);
        		var a = await admin_send_post({
        			pagina: 'admin-badwords-delete',
        			parametros: 'wid=' + wid
        		})
        		mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
        		mydialog.center();
        		if(a.charAt(0) === '1') $('#wid_' + wid).fadeOut(); 
        		$('#loading').fadeOut(350);
        	}
      }
   },
   // Posts
   posts: {
	   borrar: async (pid, gew) => {
         if(!gew){
         	modal_rapido({
         		titulo: 'Borrar Post',
         		contenido: '&#191;Quiere borrar este post permanentemente?',
         		accion: 'admin.posts.borrar(' + pid + ', 1)'
         	})
        	} else {
        		$('#loading').fadeIn(250);
        		var a = await admin_send_post({
        			pagina: 'posts-admin-borrar',
        			parametros: 'postid=' + pid
        		})
          	mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
			   mydialog.center();
			   if(a.charAt(0) == '1') $('#post_' + pid).fadeOut(); 
			   $('#loading').fadeOut(350);
			}
		}
	},
	// Fotos
	fotos : {
	   borrar: async (fid, gew) => {
         if(!gew) {
         	modal_rapido({
         		titulo: 'Borrar Foto',
         		contenido: '&#191;Quiere borrar esta foto permanentemente?',
         		accion: 'admin.fotos.borrar(' + fid + ', 1)'
         	})
         } else {
         	$('#loading').fadeIn(250);
        		var a = await admin_send_post({
        			pagina: 'admin-foto-borrar',
        			parametros: 'foto_id=' + fid
        		})
          	mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
			   mydialog.center();
			   if(a.charAt(0) == '1') $('#foto_' + fid).fadeOut(); 
			   $('#loading').fadeOut(350);
			}
		},
		setOpenClosed: async fid => {
			$('#loading').fadeIn(250);
         var h = await admin_send_post({
         	pagina: 'admin-foto-setOpenClosed', 
         	parametros: 'fid=' + fid
         })
			if(h.charAt(0) === '0') mydialog.alert('Error', h.substring(3))
			var change = (h.charAt(0) === '1') ? ['red', 'Cerrados'] : ['green', 'Abiertos'];
			$('#comments_foto_' + fid).html('<font color="'+change[0]+'">'+change[1]+'</font>');
			$('#loading').fadeOut(350);
		},
   	setShowHide:async fid => {
         $('#loading').fadeIn(250);
         var h = await admin_send_post({
         	pagina: 'admin-foto-setShowHide', 
         	parametros: 'fid=' + fid
         })
			if(h.charAt(0) === '0') mydialog.alert('Error', h.substring(3))
			var change = (h.charAt(0) === '1') ? ['purple', 'Oculta'] : ['green', 'Visible'];
			$('#status_foto_' + fid).html('<font color="'+change[0]+'">'+change[1]+'</font>');
			$('#loading').fadeOut(350);
		}
	},
   // Usuarios
   users: {
      setInActive: async uid => {
         $('#loading').fadeIn(250);
         var h = await admin_send_post({
            pagina: 'admin-users-InActivo',
            parametros: 'uid=' + uid
         })
         if(h.charAt(0) === '0') mydialog.alert('Error', h.substring(3)); 
         var change = (h.charAt(0) === '1') ? ['green', 'Activo'] : ['purple', 'Inactivo'];
         $('#status_user_' + fid).html('<font color="'+change[0]+'">'+change[1]+'</font>');
      }
   },
	// Sesiones
   sesiones: {
      borrar: async (sid, gew) => {
         if(!gew){
            modal_rapido({
               titulo: 'Cerrar sesi&oacute;n',
               contenido: '&#191;Quiere cerrar la sesi&oacute;n de este usuario/visitante? Se borrar&aacute; la sesi&oacute;n',
               accion: "admin.sesiones.borrar('" + sid + "', true)"
            })
         } else {
            $('#loading').fadeIn(250);
            var a = await admin_send_post({
               pagina: 'admin-sesiones-borrar',
               parametros: 'session_id=' + sid
            })
            mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
            mydialog.center();
            if(a.charAt(0) == '1') $('#sesion_' + sid).fadeOut(); 
            $('#loading').fadeOut(350);
         }
      }
   },
	// Nicks
   nicks: {
      accion: async (nid, accion, gew) =>{
         if(!gew){
            modal_rapido({
               titulo: (accion == 'aprobar') ? 'Aprobar Cambio' : 'Denegar Cambio',
               contenido: (accion == 'aprobar') ? '&#191;Quiere aprobar el cambio?' : '&#191;Quiere denegar el cambio?',
               accion: "admin.nicks.accion(" + nid + ",'" + accion + "' ,true)"
            })
         } else {
            $('#loading').fadeIn(250);
            var a = await admin_send_post({
               pagina: 'admin-nicks-change',
               parametros: 'nid=' + nid + '&accion=' + accion
            })
            mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
            mydialog.center();
            if(a.charAt(0) == '1') $('#nick_' + nid).fadeOut(); 
            $('#loading').fadeOut(350);
         }
      }
   }
}

/* AFILIADOS */
var ad_afiliado = {
   cache: {},
   detalles: async aid => {
      modal_rapido({
         titulo: 'Detalles del afiliado',
         contenido: await admin_send_post({
            pagina:'afiliado-detalles',
            parametros:'ref=' + aid
         }),
         texto: 'Aceptar',
         accion: 'mydialog.close()'
      })
   }
}