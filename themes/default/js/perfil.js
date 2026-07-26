const perfil = (() => {
   const cache = new Map();
   const getPid = () => $('#info').attr('pid');
   const loadTab = (type, trigger) => {
      $('#tabs_menu .tab-item').removeClass('active');
      $(trigger).addClass('active');
      $('#perfil_content').children('div').fadeOut();
      $('#perfil_load').fadeIn();
      loadContent(type);
   };
   const loadContent = (type, page = 1) => {
      const $content = $(`#perfil_${type}`);
      if (cache.has(type)) {
         $('#perfil_load').hide();
         $content.fadeIn();
         return;
      }
      $('#loading').slideDown(250);
      api(`perfil-${type}?hide=true&page=${page}`, { pid: getPid() }, response => {
	      const { status, message } = $.parseResponse(response);
	      if (status !== 1) {
	         dialog.alert('Error', message);
	         return;
	      }
	      $('#perfil_content').append(response.substring(3));
	      $(`#perfil_${type}`).fadeIn();
	      cache.set(type, true);
	      $('#perfil_load').hide();
	      $('#loading').slideUp(350);
	   },{
	   	error: ({ xhr, status, error }) => {
		   	dialog.alert('Error', 'No se pudo cargar el contenido');
		   }
		});
   };
   const loadFollows = (type, page = 1) => {
      api(`perfil-${type}?hide=true&page=${page}`, { pid: getPid() }, response => {
         const { message } = $.parseResponse(response);
         $(`#perfil_${type}`).html(message);
      });
   };
   return {
      load_tab: loadTab,
      follows: loadFollows,
      pid: getPid()
   };
})();

/** ACTIVIDAD **/
const actividad = {
	total: 25,
	show: 25,
	cargar(id, ac_do, ac_type) {
		// ELIMINAR
		$('#last-activity-view-more').remove();
		if(ac_do === 'filtrar') actividad.total = 0;
		let params = { pid: perfil.pid, ac_type, do: ac_do, start: actividad.total };
		// ENVIAMOS
		api(`perfil-actividad`, params, response => {
			const { status, message } = $.parseResponse(response);
			if(status === 0) {
				dialog.alert('Error', message);
				return;
			}
			const add = (ac_do === 'more') ? 'append' : 'html';
			$('#last-activity-container')[add](message);
			// TOTALES
			const total_pubs = $('#total_acts').attr('val');
			actividad.total = actividad.total + parseInt(total_pubs);
			$('#total_acts').remove();
		
		});
	},
	borrar(id, obj) {
		// ENVIAMOS
		let params = { pid: perfil.pid, acid: id, do: 'borrar' };
		api(`perfil-actividad`, params, response => {
			const { status, message } = $.parseResponse(response);
			if(status === 0) {
				dialog.alert('Error', message);
				return;
			}
			$(obj).parent().parent().parent().remove();
		});
	}
}

const settings = {
	maxWidth: 463,
	type: 'status',
	continue: false,
	adjunto: '',
	placeholder: {
		foto: route.url + '/files/images/imagen_123.png',
		enlace: route.url + '/blog/15/ejemplo.html',
		video: 'https://www.youtube.com/watch?v=BHdqa4gImqU'
	},
	inpfile: '',
	extensiones: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'],
	busy(loader = true) {
		if(settings.continue) return false;
		else settings.continue = true;
		if(loader) muro.stream.loader(true);
	},
	breaked(title = '', content = '') {
		if(title !== '' && content !== '') {
			dialog.alert(title, content);
		}
		// LOADER / DISABLED / STATUS
		muro.stream.loader(false);
		settings.continue = false;
	}
}

/** MURO **/
const muro = {
	stream: {
		total: 0,
		show: 10,
		load(action) {
			settings.type = action;
			let typeAct = (action !== 'status');
			let form = '';
			const group = $(".option-publish");
			if (typeAct) {
				let placeholder = settings.placeholder[action];
				form = `<input type="text" name="input${action}" placeholder="${placeholder}"><span data-type="adjuntar" onclick="muro.stream.adjuntar()" role="button">Adjuntar</span><img src="${route.assets}/images/loader.gif" style="display:none;"/>`;
				group.removeClass('none').addClass('flex');
			} else {
				settings.type = 'status';
				group.removeClass('flex').addClass('none');
			}
			group.html(typeAct ? form : '');
			return false;
		},
		adjuntar() {
			settings.busy();
			// FUNCION
			const inputContent = $(`input[name=input${settings.type}]`);
			const validando = muro.stream.validar(inputContent);
			if(!validando) {
				settings.breaked('Error al publicar', validando);
				return;
			}
			muro.stream.ajaxCheck(inputContent.val(), inputContent);
		},
		adjuntando(status = true) {
			$('.option-publish > span')[status ? 'hide' : 'show']();
			$('.option-publish > img')[status ? 'show' : 'hide']();
		},
		// VALIDAR LAS URL DE LOS ARCHIVOS ADJUNTOS
		validar(inputContent) {
			const rawValue = inputContent.val().trim();
			muro.stream.adjuntando();
			let url;
			if (!rawValue || rawValue === inputContent.attr('title')) {
				muro.stream.adjuntando(false);
				return 'Debes ingresar una dirección URL válida.';
			}
			try {
				url = new URL(rawValue);
			} catch {
				muro.stream.adjuntando(false);
				return 'Debes ingresar una dirección URL válida.';
			}
			if (!['http:', 'https:'].includes(url.protocol)) {
				muro.stream.adjuntando(false);
				return 'Debes ingresar una dirección URL válida.';
			}
			if(settings.type === 'fotos') {
				const ext = url.pathname.split('.').pop().toLowerCase();
				if (!settings.extensiones.includes(ext)) {
					muro.stream.adjuntando(false);
					return 'Sólo se permiten imágenes.';
				}
				inputContent.val(url.href); 
				return false;
			}
			if(settings.type === 'video' && youtubeId(url.href) === false) {
				muro.stream.adjuntando(false);
				return 'Al parecer la url del video no es v&aacute;lida. Recuerda que solo puedes compartir videos de YouTube.'
			}
			return true;
		},
		// VERIFICAR ARCHIVO
		ajaxCheck(url, inputContent) {
			api(`muro-stream?do=check&type=${settings.type}`, { url }, response => {
				const { status, message } = $.parseResponse(response);
				if(status === 0) {
					muro.stream.adjuntando(false);
					dialog.alert('Error al publicar', message);
					inputContent.attr('disabled', '');
					return;
				} 
				muro.stream.adjuntando(false);
				settings.adjunto = inputContent.val();
				$(`.option-publish`).removeClass('flex').addClass('none').html('');
				$('.publish-box #result').html(message);
			}).always(() => settings.breaked());
		},
		// COMPARTIR
		compartir() {
			settings.busy();
			let next = true;
			// 
			const err = 'Las publicaciones de estado y/o comentarios deben ser inferiores a 420 caracteres. Ya has ingresado %d caracteres';
			// ARCHIVOS ADJUNTOS
			const boxText = $('#publishText');
			let textWall = boxText.val();
			let countTextWall = textWall.length;
			// Verificamos que no exceda los 420 caracteres
			if(countTextWall >= 420) {
				next = false;
				settings.breaked('No puedes continuar...', err.replace('%d', countTextWall));
				return;
			} 
			if(settings.type !== 'status' && muro.stream.adjunto === '') {
				settings.breaked('Error al publicar', 'Ingresa la <strong>URL</strong> en el campo de texto y a continuaci&oacute;n da clic en <strong>Adjuntar</strong>.');
				return;
			}
			// VALIDAR
			if(textWall === boxText.attr('placeholder')) {
				boxText.blur(); 
				next = false;
				settings.breaked();
				return;
			} 
			// ENVIAR PUBLICACION NORMAL O CON ADJUNTO
			if(next) {
				muro.stream.ajaxPost(textWall);
			}
		},
		// POSTEAR EN EL MURO
		ajaxPost(data) {
			const params = { adj: settings.adjunto, pid: perfil.pid, data };
			api(`muro-stream?do=post&type=${settings.type}`, params, response => {
				const { status, message } = $.parseResponse(response);
				if(status === 0) {
					dialog.alert('Error al publicar', message);
					return;
				}
				// ESCONDEMOS SI ES EL PRIMER COMENTARIO
				if($('#wall-content .alert-empty')) $('#wall-content .alert-empty').hide();
				$('#wall-content, #news-content').prepend($(message).fadeIn('slow'));
				// Reiniciamos
				$('#publishText').val('').focus();
				$(`.option-publish`).removeClass('flex').addClass('none').html('');
				$('.publish-box #result').html('');
				muro.stream.load('status', $('#stMain'));
			}).always(() => settings.breaked());
		},
		loadMore(type) {
			settings.busy(false);
			// LOADER
			$('.more-pubs button').hide();
			$('.more-pubs img').show();
			// CARGAMOS
			let { muro: { stream } } = global_data
			const params = { start: stream.total, pid: perfil.pid };
			api(`muro-stream?do=more&type=${type}`, params, response => {
				const { status, message } = $.parseResponse(response);
				if(status === 0) {
					dialog.alert('Error al cargar', message);
					return;
				}
				$('#' + type + '-content').append(message);
				let totalPublicaciones = $('#total_pubs').attr('val');
				publicaciones = parseInt(totalPublicaciones);
				const mensaje = (type === 'news' && publicaciones < 0) ? 'Solo puedes ver las &uacute;ltimas 100 publicaciones.' : 'No hay m&aacute;s mensajes para mostrar.'; 
				if(publicaciones === 0 || publicaciones < muro.stream.show) {
					$('.more-pubs').html(mensaje);
				} else {
					global_data.muro.stream.total = muro.stream.total + parseInt(publicaciones);
				}
				// REMOVER
				$('#total_pubs').remove();
			}).always(() => {
				$('.more-pubs button').show();
				$('.more-pubs img').hide();
				settings.continue = false;
			});
		},
		// LOADER
		loader(active) {
			$('.streamLoader')[active ? 'show' : 'hide']();
		}
	},
	// LIKE
	like_this: function(id, type, obj){
		settings.continue = 1;
		// MANDAMOS
		$('#loading').slideDown(250); 
		api('muro-likes', { id, type }, h => {
			if(h['status'] == 'ok'){
				// I LIKE / NO
				$(obj).text(h['link']);
				//
				if(type == 'pub'){
					$('#lk_' + id).html(h['text']);
					if(h['text'] != '') {
						$('#lk_' + id).parent().parent().show();
						$('#cb_' + id).show();
					} else 
						$('#lk_' + id).parent().parent().hide();
				} else {
					$('#lk_cm_'+id).text(h['text']);
					//
					if(h['text'] == '') 
						$('#lk_cm_'+id).parent().hide();
					else 
						$('#lk_cm_'+id).parent().show();
						
				}
			} else {
				dialog.alert('Error:', h['text'].substring(3));
			}
			$('#loading').slideUp(350); 
		}, { dataType: 'json' });
	},
	show_likes: function(id, type){
		settings.continue = 1;
		// MANDAMOS
		$('#loading').fadeIn(250); 
		api('muro-likes?do=show', { id, type }, h => {
			switch(h.status){
				case 0: //Error
					dialog.alert('Error', h['data']);
					break;
				case 1: //OK
					var html = '<ul id="show_likes">';
					for(var i = 0; i < h.data.length; i++){
						html += '<li>'
						html += '<a href="' + route.url + '/@' + h.data[i].user_name + '"><img src="' + route.url + '/storage/avatar/user_' + h.data[i].user_id + '/thumb_avatar.png" /></a>'
						html += '<div class="name"><a href="' + route.url + '/perfil/' + h.data[i].user_name + '">' + h.data[i].user_name + '</a></div>' 
						html += '</li>'; 
					}
					html += '</ul>';
					// MOSTRAMOS
					mydialog.show(true);
					mydialog.title('Personas a las que les gusta');
					mydialog.body(html);
					mydialog.buttons(true, true, 'Cerrar', 'close', true, true);
					mydialog.center();
					break;
			}
			$('#loading').fadeOut(350); 
		}, { dataType: 'json' });
   
	},
	show_comment_box: function(id){
		$('#cb_' + id).slideDown()  
	},
	comentar: function(id){
		var val = $('#cf_' + id).val();
		settings.continue = 1;
		if(val == '' || val == $('#cf_' + id).attr('title')) {
			$('#cf_' + id).focus(); 
			// LOADER/ STATUS
			muro.stream.loader(false);
			settings.continue = false; 
			return false;
		}
		//
		$('#loading').fadeIn(250); 
		api('muro-stream?do=repost', { data: val, pid: id }, h => {
			switch(h.charAt(0)){
				case '0': //Error
					dialog.alert('Error:', h.substring(3));
					break;
				case '1': //OK
					$('#cl_' + id).append($(h.substring(3)).fadeIn('slow'));
					$('#cf_' + id).val('');
					break;
			}
			$('#loading').fadeOut(250); 
		});
	},
	//
	more_comments: function(id, obj){
		// LOADER / STATUS
		settings.continue = 1;
		$(obj).parent().find('img').show();
		//
		$('#loading').fadeIn(250); 
		api('muro-stream?do=more_comments', { pid: id }, h => {
			switch(h.charAt(0)){
				case '0': //Error
					dialog.alert('Error:', h.substring(3));
					break;
				case '1': //OK
					$('#cl_' + id).html(h.substring(3));
					break;
			}
			$('#loading').fadeOut(350); 
		});
	},
	// MOSTRAR VIDEO DEL MURO
	loadAtta(type, ID, obj) {
		let content = '';
		switch(type){
			case 'foto':
				content = `<span class="uiPhoto block"><img src="${ID}" class="rounded ratio 1x1" style="max-width:${settings.maxWidth}px!important;" /></span>`;
			break;
			case 'video':
				content = `<lite-youtube loading="lazy" class="ratio ratio-4x3 rounded" videoid="{$p.adj_url}" style="background-image: url('https://i.ytimg.com/vi/${ID}/maxresdefault.jpg');"></lite-youtube>`;
			break;
		}
		console.log(type, ID, obj)
		// CARGAMOS
		$(obj).parent().html(content);
	},
	// ELIMINAR PUBLICACION / COMENTARIO
	del_pub: function(id, type){
		var txt_type = (type == 1) ? 'publicaci&oacute;n' : 'comentario';
		var txt_aux = (type == 1) ? 'esta ' : 'este ';
		//
		mydialog.mask_close = false;
		mydialog.show(true);
		mydialog.title('Eliminar ' + txt_type);
		mydialog.body('¿Seguro que quieres eliminar ' + txt_aux + txt_type);
		mydialog.buttons(true, true, 'Eliminar ' + txt_type, 'muro.eliminar(' + id + ', ' + type + ')', true, true, true, 'Cancelar', 'close', true, false);
		mydialog.center();
	},
	// ELIMINAR PUBLICACION / COMENTARIO
	eliminar: function(id, type){
		// LOADER / STATUS
		settings.continue = 1;
		var snd_type = (type == 1) ? 'pub' : 'cmt';
		//
		$('#loading').slideDown(250); 
		api('muro-stream?do=delete', { id, type: snd_type }, h => {
			switch(h.charAt(0)){
				case '0': //Error
					dialog.alert('Error:', h.substring(3));
					break;
				case '1': //OK
					mydialog.close();
					$('#' + snd_type + '_' + id).hide().remove();
					break;
			}
			$('#loading').slideUp(450); 
		});
	}
	//
}
const followUserPost = (obj) => {
}
/** READY **/
$(() => {

	// ENVIAR PUBLICACION
	$('textarea[name=add_wall_comment]').on("keypress",function(k){
		if(k.which == 13){
			var pub_id = $(this).attr('pid');
			muro.comentar(pub_id);
			return false;
		}
	});
	// ADJUNTAR
	$('.adj').click(function(){
		var aid = $(this).attr('aid');
	})
	//
	$('input[name=hack]').on("focus",function(){
		$(this).hide();
		$(this).parent().find('div.formulario').show();
		var pub_id = $(this).attr('pid');
		//
		$('#cf_' + pub_id).focus()
	})

	$('#tabs_menu > .tab-item, .box-content .item').on('click', function() {
		const target = $(this);
		const tab = target.data('tab');
		perfil.load_tab(tab, target);
	});

	$('.action-btn').on('click', function() {
      const $btn = $(this);
      const action = $btn.data('action');
      const uid = $btn.data('id');
      const currentText = $btn.text().trim();
      
      switch (action) {
         case 'bloquear':
         	let newAction = (currentText === 'Bloquear');
            bloquear(uid, newAction, 'perfil');
            $btn.text(newAction ? 'Desbloquear' : 'Bloquear').attr({
            	'data-block': !newAction,
            }).toggleClass('btn-danger btn-success', 'btn-'+(action ? 'success' : 'danger'))
         break;
      	case 'denuncia':
      		const nick = $btn.data('nick');
      		denuncia.nueva('usuario', uid, '', nick);
      	break;
      	case 'ban':
      		moderacion.usuarios.action(uid, 'ban', true);
      	break;
      }
   });

	// Seguir o dejar de seguir usuarios
	$('#followUser').on('click', function() {
		const obj = $(this);
		const currentId = obj.data('id');
		const actions = obj.data('action').split('_');
		let isFollow = (parseInt(obj.data('follow')) === 1);
		const nextText = isFollow ? 'Seguir ' + follow : 'Dejar de seguir';
		// Actualiza el valor en memoria y en el DOM
		obj.data('follow', isFollow ? 0 : 1).attr({ 
			'data-follow': isFollow ? 0 : 1, 
			'title': nextText 
		}).toggleClass('follow unfollow', (isFollow ? 'unfollow' : 'follow'));
		notifica.follow({
		   action: actions[0],
		   type: actions[1],
		   id: currentId,
		   fn: notifica.userInPostHandle,
		   obj: obj
		});
	});

	$('.btnAction').on('click', function() {
		const action = $(this).data('action');
		let argument = $(this).data('argument') ?? '';
		muro.stream[action](argument);
	});
	$('.mvm > span[role=button]').on('click', function() {
		const $this  = $(this);
		const action = $this.data('action');
		let type 	 = $this.data('type') ?? '';
		let adjunto  = $this.data('adjunto') ?? '';
		console.log(action, type, adjunto, $this)
		muro[action](type, adjunto, $this);
	});
});
