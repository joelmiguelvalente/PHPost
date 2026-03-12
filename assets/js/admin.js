const subDelete = ({ endpoint, param, remove }) => {
   $('#loading').fadeIn(250);
   api(endpoint, param, response => {
   	const { status, message } = $.parseResponse(response);
	   dialog.alert((status === 0 ? 'Error' : 'Bien'), message, status === 1);
	   if(status === 1 && remove !== '') {
	   	$(remove).remove()
	   }
	   $('#loading').fadeOut(350);
   });
}
const mainDelete = ({ title, body, text, fn }) => {
	dialog.init({ title, body, buttons: { confirm: {text, action: fn }} });
}

const noticias = nid => {
	$('#loading').fadeIn(250);
	api('admin-noticias-setInActive.php', { nid }, response => {
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
	usar(path) {
		api('tema-usar.php', { path }, response => {
	      const { status, message } = $.parseResponse(response);
	      dialog.alert((status === 0 ? 'Error' : 'Bien'), message, status === 1);
			return;
		})
	}
}

const medallas = {
	borrar(mid, gew = 1) {
		const title = 'Borrar medalla';
		let status = (gew === 1);
		if(status || gew === 2) {
			let body = status ? '&#191;Quiere borrar esta medalla?' : 'Si borra la medalla, los usuarios que tengan esta medalla la perder&aacute;n, &#191;seguro que quiere continuar?';
			dialog.init({ title, body,
		      buttons: {
		         confirm: { text: 'S&iacute;', action: () => medallas.borrar(mid, (status ? 2 : 3)) }
		      }
		   });
		   return;
	   }
	   subDelete({ 
	   	endpoint: 'admin-medalla-borrar.php', 
	   	param: { medal_id: mid }, 
	   	remove: `#medal_id_${mid}`
	   });
	},
   asignar(mid, gew) {
   	if(!gew) {
		   api('admin-medalla-asignar-form.php', {}, response => {
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
		   api('admin-medalla-asignar.php', params, response => {
	   		const { status, message } = $.parseResponse(response);
	   		dialog.alert((status ? 'Hecho' : 'Opps!'), message);
	   		if(status === 1) {
	   			$('#total_med_assig_' + mid).text(parseInt($('#total_med_assig_' + mid).text()) + 1);
         		$('#loading').fadeOut(350);
	   		}
		   })
		}
   },
	borrar_asignacion(aid, mid, next = false) {
		if(!next) {
			mainDelete({ 
	        	title: 'Borrar Asignacion',
	        	body: '&#191;Quiere continuar borrando esta asignaci&oacute;n?',
			   text: 'Borrar asignacion', 
			   fn: () => medallas.borrar_asignacion(aid, mid, true) 
			});
			return;
		}
		subDelete({
			endpoint: 'admin-medallas-borrar-asignacion.php',
			param: { aid, mid },
			remove: `#assign_id_${aid}`
		})
	},
}

const blacklist = {
	borrar(bid, next = false) {
		if(!next) {
			mainDelete({ 
	        	title: 'Retirar Bloqueo',
	        	body: '&#191;Quiere retirar este bloqueo?',
			   text: 'Quitar bloque', 
			   fn: () => blacklist.borrar(bid, true) 
			});
			return;
		}
		subDelete({
			endpoint: 'admin-blacklist-delete.php',
			param: { bid },
			remove: `#block_${bid}`
		})
   }
}

const badwords = {
	borrar(wid, next = false) {
		if(!next) {
			mainDelete({ 
	        	title: 'Retirar Filtro',
	        	body: '&#191;Quiere retirar este Filtro?',
			   text: 'Quitar Filtro', 
			   fn: () => badwords.borrar(wid, true) 
			});
			return;
		}
		subDelete({
			endpoint: 'admin-badwords-delete.php',
			param: { wid },
			remove: `#wid_${wid}`
		})
   }
}

const posts = {
	borrar(pid, next = false) {
		if(!next) {
			mainDelete({ 
	        	title: 'Borrar Post',
	        	body: '&#191;Quiere borrar este post permanentemente?',
			   text: 'Borrar post', 
			   fn: () => posts.borrar(pid, true) 
			});
			return;
		}
		subDelete({
			endpoint: 'posts-admin-borrar.php',
			param: { postid: pid },
			remove: `#post_${pid}`
		})
   }
}

const fotos = {
	borrar(fid, next = false) {
		if(!next) {
			mainDelete({  
	        	title: 'Borrar Foto',
	        	body: '&#191;Quiere borrar esta foto permanentemente?',
			   text: 'Borrar foto', 
			   fn: () => fotos.borrar(fid, true) 
			});
			return;
		}
		subDelete({
			endpoint: 'admin-foto-borrar.php',
			param: { foto_id: fid },
			remove: `#foto_${fid}`
		})
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
}

/** 
 * Nueva organización
*/
const admin = {
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
         accion: 'dialog.close()'
      })
   }
}

function selectAll(formId, state) {
   $('#' + formId + ' input[type="checkbox"]').prop('checked', state);
}
function confirmTruncate(table) {
	dialog.init({ 
		title: '⚠ Vaciar tabla',
		body: `¿Estás seguro que querés vaciar la tabla <strong>${table}</strong>? Esta acción no se puede deshacer.`,
	   buttons: {
	      confirm: { text: 'Sí, vaciar', action: () => doTruncate() }
	   }
	});
}
function doTruncate() {
   $('#truncate-form').submit();
}

function doDeleteBackup(filename, id) {
	api(`dbmanager-delete_backup.php`, { filename }, response => {
		const { status, message } = $.parseResponse(response);
	  	dialog.alert((status === 0 ? 'Error' : 'Bien'), message, status === 1);
	  	if(status === 1) {
	  		$(`#${id}`).remove();
	  	}
	});
}

$(document).ready(() => {

	$('#newRank span, input[type="button"]#next').on('click', function(e) {
		e.preventDefault();
		const target = $(this).data('target');
		$('#basico, #permisos').hide();
		$(`#${target}`).show();
	});

	$('form[action-type="download"]').on('submit', function() {
    	setTimeout(() => {
      	history.replaceState(null, '', window.location.href);
    	}, 100);
	});

	$('.bactions button[data-action=delete]').on('click', function() {
	   const filename = $(this).data('filename');
	   const id = $(this).data('id');
		dialog.init({ 
			title: '⚠ Eliminar backup',
			body: `¿Estás seguro que quierés eliminar el <strong>${filename}</strong>? Esta acción no se puede deshacer.`,
		   buttons: {
		      confirm: { text: 'Sí, eliminar', action: () => doDeleteBackup(filename, id) }
		   }
		});
	});

});