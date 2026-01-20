/*
	PERFIL
*/
const perfil = (() => {
   const cache = new Map();
   const ui = {
      content: $('#perfil_content'),
      loader: $('#perfil_load'),
      globalLoader: $('#loading'),
      tabContent: type => $(`#perfil_${type}`)
   };
   const getPid = () => $('#info').attr('pid');
   const setActiveTab = obj => {
      $('#tabs_menu > li').removeClass('selected');
      $(obj).parent().addClass('selected');
   };
   const showLoader = () => {
      ui.content.children('div').fadeOut();
      ui.loader.fadeIn();
   };
   const hideLoader = () => {
      ui.loader.hide();
      ui.globalLoader.slideUp(350);
   };
   const loadTab = (type, trigger) => {
      setActiveTab(trigger);
      showLoader();
      loadContent(type);
   };
   const loadContent = (type, page = 1) => {
      const $content = ui.tabContent(type);
      if (cache.has(type)) {
         ui.loader.hide();
         $content.fadeIn();
         return;
      }
      ui.globalLoader.slideDown(250);
      $.post(`${route.url}/perfil-${type}.php?hide=true&page=${page}`, { pid: getPid() }
      ).done(response => {
	      const { status, message } = $.parseResponse(response);
	      if (status !== 1) {
	         dialog.alert('Error', message);
	         return;
	      }
	      ui.content.append(response.substring(3));
	      ui.tabContent(type).fadeIn();
	      cache.set(type, true);
	   })
      .fail(() => dialog.alert('Error', 'No se pudo cargar el contenido'))
      .always(hideLoader);
   };
   const loadFollows = (type, page = 1) => {
      $.post(`${route.url}/perfil-${type}.php?hide=true&page=${page}`, { pid: getPid() }, response => {
         const { message } = $.parseResponse(response);
         ui.tabContent(type).html(message);
      });
   };
   return {
      load_tab: loadTab,
      follows: loadFollows
   };
})();

/** ACTIVIDAD **/
const actividad = {
	total: 25,
	show: 25,
	cargar: (id, ac_do, ac_type) => {
		// ELIMINAR
		$('#last-activity-view-more').remove();
		if(ac_do === 'filtrar') actividad.total = 0;
		let params = { pid: perfil.getPid(), ac_type, do: ac_do, start: actividad.total };
		// ENVIAMOS
		$.post(`${route.url}/perfil-actividad.php`, params)
		.done(response => {
			const { status, message } = $.parseResponse(response);
			switch(status) {
				case 0: //Error
					dialog.alert('Error', message);
				break;
				case 1: //OK
					const add = (ac_do === 'more') ? 'append' : 'html';
					$('#last-activity-container')[add](message);
					// TOTALES
					const total_pubs = $('#total_acts').attr('val');
					actividad.total = actividad.total + parseInt(total_pubs);
					$('#total_acts').remove();
				break;
			}
		});
	},
	borrar: (id, obj) =>{
		// ENVIAMOS
		let params = { pid: perfil.getPid(), acid: id, do: 'borrar' };
		$.post(`${route.url}/perfil-actividad.php`, params)
		.done(response => {
			const { status, message } = $.parseResponse(response);
			switch(status) {
				case 0: //Error
					dialog.alert('Error', message);
				break;
				case 1: //OK
					$(obj).parent().parent().parent().remove();
				break;
			}
		});
	}
}

/** MURO **/
const muro = {
	maxWidth: 463, // WIDTH PARA LAS FOTOS Y VIDEOS
	stream: {
		total: 0, // TOTAL DE PUBLICACIONES CARGADAS
		show: 10, // CUANTOS SE MUESTRAN POR CADA CARGA
		type: 'status', // TIPO D PUBLICACION ACTUAL
		status: 0, // PARA EVITAR CLICKS INESESARIOS
		adjunto: '', // SE HA CARGADO UN ARCHIVO ADJUNTO?
		// CARGAR EL TIPO DE PUBLICACION :
		load: (nameAction, obj) => {
			// ACTUAL
			muro.stream.type = nameAction;
			//
			const letter = (muro.stream.type === 'foto') ? 'a' : 'e';
			const text = `Haz un comentario sobre est${letter} ${muro.stream.type}...`;
			//
			let status = (nameAction !== 'status');
			$('.btnStatus')[status ? 'hide' : 'show']();
			$('.attaDesc')[status ? 'show' : 'hide']();
			if(status) {
				$('#attaDesc').attr('title', text).val(text);
			}
			//
			$('span.uiComposer .nub, span.uiComposer span').hide();
			$('span.uiComposer a').show();
			$(obj).hide().parent().find('span, i').show();
			// 
			$('#attaContent > div').hide();
			$(`#${nameAction}Frame`).show(); 
			// 
			return false;
		},
		// ADJUNTAR ARCHIVO EXTERNO : FOTO, ENLACE, VIDEO DE YOUTBE
		adjuntar: () => {
			// SI ESTA OCUPADO NO HACEMOS NADA
			if(muro.stream.status === 1) return false;
			else muro.stream.status = 1;
			// LOADER
			muro.stream.loader(true);
			// FUNCION
			const inpt = $(`input[name=i${muro.stream.type}]`);
			inpt.attr('disabled', 'true');
			const valid = muro.stream.validar(inpt);
			if(valid) {
				// ADJUNTAMOS...
				muro.stream.ajaxCheck(inpt.val(), inpt);
			} else {
				dialog.alert('Error al publicar', valid);
				// LOADER / DISABLED / STATUS
				muro.stream.loader(false);
				inpt.attr('disabled', '');
				muro.stream.status = 0;
			}
		},
		// VERIFICAR ARCHIVO
		ajaxCheck: (url, inpt) => {
			$('#loading').fadeIn(250);
			url = encodeURIComponent(url);
			$.post(`${route.url}/muro-stream.php?do=check&type=${muro.stream.type}`, { url })
			.done(response => {
				const { status, message } = $.parseResponse(response);
				if(status === 0) {
					dialog.alert('Error al publicar', message);
					inpt.attr('disabled', '');
				} else if(status === 1) {
					muro.stream.adjunto = inpt.val();
					$(`#${muro.stream.type}Frame`).html(message);
				}
				$('#loading').fadeOut(350); 
			})
			.always(() => {
				// LOADER/ STATUS
				muro.stream.loader(false);
				muro.stream.status = 0;
				$('#loading').fadeOut(350); 
			});
		},
		// VALIDAR LAS URL DE LOS ARCHIVOS ADJUNTOS
		validar: (inpt) => {
			const rawValue = inpt.val().trim();
			if (!rawValue || rawValue === inpt.attr('title')) {
				return 'Debes ingresar una dirección URL válida.';
			}
			let url;
			try {
				url = new URL(rawValue);
			} catch {
				return 'Debes ingresar una dirección URL válida.';
			}
			if (!['http:', 'https:'].includes(url.protocol)) {
				return 'Debes ingresar una dirección URL válida.';
			}
			switch (muro.stream.type) {
				case 'foto': {
					const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
					const ext = url.pathname.split('.').pop().toLowerCase();
					if (!allowedExt.includes(ext)) {
						return 'Sólo se permiten imágenes jpg, jpeg, png, gif, webp y avif.';
					}
					inpt.val(url.href); // normaliza
					break;
				}
				case 'video': {
					if (url.hostname !== 'www.youtube.com' && url.hostname !== 'youtube.com' && url.hostname !== 'youtu.be') {
						return 'Sólo se permiten videos de YouTube.';
					}
					if ((url.hostname !== 'youtu.be' && !url.searchParams.get('v')) && url.hostname !== 'youtu.be') {
						return 'La URL del video no es válida.';
					}
					break;
				}
			}
			return true;
		},
		// COMPARTIR
		compartir: () => {
			// SI ESTA OCUPADO NO HACEMOS NADA
			if(muro.stream.status === 1) return false;
			else muro.stream.status = 1;
			// LOADER
			muro.stream.loader(true);
			// 
			const error_length = 'Las publicaciones de estado y/o comentarios deben ser inferiores a 420 caracteres. Ya has ingresado %d caracteres';
			// ARCHIVOS ADJUNTOS
			if(muro.stream.type !== 'status'){
				if(muro.stream.adjunto !== ''){
					var val = $('#attaDesc').val();
					// VALIDAR
					if(val.length > 420) {
						dialog.alert('Error al publicar', error_length.replace('%d', val.length));
						// LOADER/ STATUS
						muro.stream.loader(false);
						muro.stream.status = 0;
					// ENVIAMOS PUBLICACION
					} else {
						val = (val == $('#attaDesc').attr('title')) ? '' : val;
						muro.stream.ajaxPost(val);
					}
					
				} else {
					dialog.alert('Error al publicar', 'Ingresa la <b>URL</b> en el campo de texto y a continuaci&oacute;n da clic en <b>Adjuntar</b>.');
					// LOADER/ STATUS
					muro.stream.loader(false);
					muro.stream.status = 0;
				}
			// PUBLICACION SIMPLE
			} else if(muro.stream.type == 'status'){
				var status = $('#wall');
				var val = status.val();
				var error = false;
				// VALIDAR
				if(val == '' || val == status.attr('title')) {
					status.blur(); 
					error = true;
					// LOADER/ STATUS
					muro.stream.loader(false);
					muro.stream.status = 0; 
					return false;
				}
				else if(val.length > 420) error = error_length.replace('%d', val.length);
				// ENVIAR PUBLICACION
				if(error == false){
					muro.stream.ajaxPost(val);
				} else {
					dialog.alert('Error al publicar', error);
					// LOADER/ STATUS
					muro.stream.loader(false);
					muro.stream.status = 0;
				}
			}
		},
		// POSTEAR EN EL MURO
		ajaxPost: function(data){
			$('#loading').slideDown(250); 
			$.ajax({
				type: 'POST',
				url: route.url + '/muro-stream.php?do=post&type=' + muro.stream.type,
				data: 'adj=' + muro.stream.adjunto +'&data=' + encodeURIComponent(data) + '&pid=' + $('#info').attr('pid'),
				success: function(h){
					console.log(h)
					switch(h.charAt(0)){
						case '0': //Error
							dialog.alertt('Error al publicar', h.substring(3));
							break;
						case '1': //OK
							// ESCONDEMOS SI ES EL PRIMER COMENTARIO
							if($('#wall-content .emptyData')) $('#wall-content .emptyData').hide();
							//
							$('#wall-content, #news-content').prepend($(h.substring(3)).fadeIn('slow'));
							$('#wall').val('').focus();
							muro.stream.load('status',$('#stMain'));
							break;
					}
					$('#loading').slideUp(350); 
				},
				complete: function (){
					// LOADER/ STATUS
					muro.stream.loader(false);
					muro.stream.status = 0;
					$('#loading').fadeOut(350); 
				}
			});
		},
		loadMore: function(type){
			// SI ESTA OCUPADO NO HACEMOS NADA
			if(muro.stream.status == 1) return false;
			else muro.stream.status = 1;
			// LOADER
			$('.more-pubs a').hide();
			$('.more-pubs span').css('display','block');
			// CARGAMOS
			$('#loading').fadeIn(250); 
			$.ajax({
				type: 'POST',
				url: route.url + '/muro-stream.php?do=more&type=' + type,
				data: 'pid=' + $('#info').attr('pid') + '&start=' + global_data.muro.stream.total,
				success: function(h){
					switch(h.charAt(0)){
						case '0': //Error
							dialog.alertt('Error al cargar', h.substring(3));
							break;
						case '1': //OK
							// CARGAMOS AL DOM
							$('#' + type + '-content').append(h.substring(3));
							// VALIDAMOS
							var total_pubs = $('#total_pubs').attr('val');
							total_pubs = parseInt(total_pubs);
							// 
							var msg = (type == 'news' && total_pubs < 0) ? 'Solo puedes ver las &uacute;ltimas 100 publicaciones.' : 'No hay m&aacute;s mensajes para mostrar.'; 
							if(total_pubs == 0 || total_pubs < muro.stream.show) $('.more-pubs').html(msg).css('padding','10px');
							else global_data.muro.stream.total = muro.stream.total + parseInt(total_pubs);
							// REMOVER
							$('#total_pubs').remove();
							break;
					}
					$('#loading').fadeOut(250); 
				},
				complete: function (){
					$('.more-pubs a').show();
					$('.more-pubs span').hide();
					muro.stream.status = 0;
					$('#loading').fadeOut(450); 
				}
			});
		},
		// LOADER
		loader: function(active){
			if(active == true) $('.streamLoader').show();
			else if(active == false) $('.streamLoader').hide();
		}
	},
	// LIKE
	like_this: function(id, type, obj){
		muro.stream.status = 1;
		// MANDAMOS
		$('#loading').slideDown(250); 
		$.ajax({
			type: 'POST',
			url: route.url + '/muro-likes.php',
			dataType: 'json',
			data: 'id=' + id + '&type=' + type,
			success: function(h){
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
				   dialog.alertt('Error:', h['text'].substring(3));
			   }
			   $('#loading').slideUp(350); 
			},
			complete: function (){
				// STATUS
				muro.stream.status = 0;
			}
		});
	},
	show_likes: function(id, type){
		muro.stream.status = 1;
		// MANDAMOS
		$('#loading').fadeIn(250); 
		$.ajax({
			type: 'POST',
			url: route.url + '/muro-likes.php?do=show',
			dataType: 'json',
			data: 'id=' + id + '&type=' + type,
			success: function(h){
				switch(h.status){
					case 0: //Error
						dialog.alertt('Error', h['data']);
						break;
					case 1: //OK
						var html = '<ul id="show_likes">';
						for(var i = 0; i < h.data.length; i++){
							html += '<li>'
							html += '<a href="' + route.url + '/perfil/' + h.data[i].user_name + '"><img src="' + route.url + '/files/avatar/' + h.data[i].user_id + '_50.jpg" /></a>'
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
			},
			complete: function (){
				// STATUS
				muro.stream.status = 0;
			}
		});
   
	},
	show_comment_box: function(id){
		$('#cb_' + id).slideDown()  
	},
	comentar: function(id){
		var val = $('#cf_' + id).val();
		muro.stream.status = 1;
		if(val == '' || val == $('#cf_' + id).attr('title')) {
			$('#cf_' + id).focus(); 
			// LOADER/ STATUS
			muro.stream.loader(false);
			muro.stream.status = 0; 
			return false;
		}
		//
		$('#loading').fadeIn(250); 
		$.ajax({
			type: 'POST',
			url: route.url + '/muro-stream.php?do=repost',
			data: 'data=' + encodeURIComponent(val) + '&pid=' + id,
			success: function(h){
				switch(h.charAt(0)){
					case '0': //Error
						dialog.alertt('Error:', h.substring(3));
						break;
					case '1': //OK
						$('#cl_' + id).append($(h.substring(3)).fadeIn('slow'));
						$('#cf_' + id).val('');
						break;
				}
				$('#loading').fadeOut(250); 
			},
			complete: function (){
				// STATUS
				muro.stream.status = 0;
				$('#loading').fadeOut(350); 
			}
		});
	},
	//
	more_comments: function(id, obj){
		// LOADER / STATUS
		muro.stream.status = 1;
		$(obj).parent().find('img').show();
		//
		$('#loading').fadeIn(250); 
		$.ajax({
			type: 'POST',
			url: route.url + '/muro-stream.php?do=more_comments',
			data: 'pid=' + id,
			success: function(h){
				switch(h.charAt(0)){
					case '0': //Error
						dialog.alertt('Error:', h.substring(3));
						break;
					case '1': //OK
						$('#cl_' + id).html(h.substring(3));
						break;
				}
				$('#loading').fadeOut(350); 
			},
			complete: function (){
				// STATUS
				muro.stream.status = 0;
				$('#loading').fadeOut(550); 
			}
		});
	},
	// MOSTRAR VIDEO DEL MURO
	load_atta: function(type, ID, obj){
		switch(type){
			case 'foto':
				var content = '<center><img src="' + ID + '" style="max-width:' + this.maxWidth + 'px; max-height: 380px" /><center>'; //bzox
			break;
			case 'video':
				var content = '<embed width="' + this.maxWidth + '" height="285" flashvars="width=' + this.maxWidth + '&amp;height=285" wmode="opaque" salign="tl" allowscriptaccess="never" allowfullscreen="false" scale="scale" quality="high" bgcolor="#FFFFFF" src="http://www.youtube.com/v/' + ID +'&amp;autoplay=1" type="application/x-shockwave-flash">';
			break;
		}
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
		muro.stream.status = 1;
		var snd_type = (type == 1) ? 'pub' : 'cmt';
		//
		$('#loading').slideDown(250); 
		$.ajax({
			type: 'POST',
			url: route.url + '/muro-stream.php?do=delete',
			data: 'id=' + id + '&type=' + snd_type,
			success: function(h){
				switch(h.charAt(0)){
					case '0': //Error
						dialog.alertt('Error:', h.substring(3));
						break;
					case '1': //OK
						mydialog.close();
						$('#' + snd_type + '_' + id).hide().remove();
						break;
				}
				$('#loading').slideUp(450); 
			},
			complete: function (){
				// STATUS
				muro.stream.status = 0;
				$('#loading').slideUp(350); 
			}
		});
	}
	//
}
/** READY **/
$(function(){
	// POR ESTETICA...
	setTimeout("$('#wall, #attaDesc').blur().css('height', '14px')",0);
	setTimeout("$('#attaContent input').blur().css('height', '14px')",0);
	// WALL
	$('#wall').focus(function(){
		$('.btnStatus').show();
		$('.frameForm').css('border-bottom', '1px solid #E9E9E9');
	});
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
	// RESPUESTAS
	$('.comentar').css('max-height', '200px').css('height','14px');
	//
	$('input[name=hack]').on("focus",function(){
		$(this).hide();
		$(this).parent().find('div.formulario').show();
		var pub_id = $(this).attr('pid');
		//
		$('#cf_' + pub_id).focus()
	})
	if($('#loadInfoTab').length > 0) {
		const tab = $('#loadInfoTab').data('tab');
		const target = $('#loadInfoTab').data('target');
		perfil.load_tab(tab, $('#' + target));
	}
});