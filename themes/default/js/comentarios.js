/* COMENTARIOS */
const comentario = {
   /* VARIABLES */
   cache: {},
   cargado: false,
   /* FUNCIONES */
   cargar(postid, page, autor) {
   	const $comentarios = $('#comentarios');
		$('#commentsLoads').show();
		//$.scrollTo('#comentarios-container', 250);
		$comentarios.css('opacity', 0.4)
		// COMPRVAMOS CACHE
      if(typeof comentario.cache[`c_${page}`] === 'undefined') {
         api(`comentario-ajax?page=${page}`, { postid, autor }, response => {
				comentario.cache['comments_page_' + page] = response;
				$comentarios.html(response);
				comentario.setPages(postid, page, autor);
			});
      } else {
         $comentarios.html(comentario.cache[`comments_page_${page}`]);
         $('.paginadorCom').html(comentario.cache[`page_${page}`]);
         $('#commentsLoads').hide();
         $comentarios.css('opacity', 1);
      }
   },
   setPages(postid, page, autor) {
    	const total = parseInt($('#ncomments').text());
    	api(`comentario-pages?page=${page}`, { postid, autor, total }, response => {
    		comentario.cache[`p_${page}`] = response;
   		$('.paginadorCom').html(response);
         $('#commentsLoads').hide();
			$('div#comentarios').css('opacity', 1);
         $('#loading').fadeOut(350);   
    	});
	},
   // NUEVO COMENTARIO
   nuevo(mostrar_resp, cid = 0) {
      if (cid === 0) $('#btnsComment').attr({ disabled: 'disabled' });
      const isReply = cid > 0;
      const textarea = isReply ? $(`#reply-form-${cid} .reply-write-body textarea`) : $('#body_comm');
      const comentario = isReply ? textarea.val() : textarea.bbcode();
      // VACÍO o LÍMITE
      const limit = (comentario.length > 1500);
      if (comentario === '' || limit) {
         textarea.focus();
         if (limit) dialog.alert("Tu comentario no puede ser mayor a 1500 caracteres.");
         if (!isReply) $('#btnsComment').removeAttr('disabled');
         return;
      }
      const auser = $('#auser_post').val();
      let params = $.param({ comentario, mostrar_resp, auser });
      params += queryParam('postid');
      // Agregar parent_cid si es respuesta
      if (isReply) params += `&parent_cid=${cid}`;
      if (!isReply) $('.miComentario #gif_cargando').show();
      api('comentario-agregar', params, response => {
         const { status, message } = $.parseResponse(response);
         if (status === 0) {
            if (isReply) {
                $(`#reply-form-${cid}`).find('.reply-error').remove();
                $(`#reply-form-${cid}`).append(`<div class="reply-error alert-error">${message}</div>`);
            } else {
               $('.miComentario .error').html(message).show('slow');
               $('#btnsComment').removeAttr('disabled');
            }
            return;
         }
         if (isReply) {
            // Insertar la respuesta debajo del comment-item padre
            const $parentItem = $(`.comment-item[data-comment-id="${cid}"] .comment-body`);
            // Si ya existe el contenedor de replies, agregar ahí; si no, crearlo
            let $repliesContainer = $parentItem.find(`#replies-${cid}`);
            if (!$repliesContainer.length) {
               $parentItem.append('<ul class="comment-replies"></ul>');
               $repliesContainer = $parentItem.find('.comment-replies');
            }
            $repliesContainer.append(message);
            // Limpiar y ocultar el form
            textarea.val('');
            $(`#reply-form-${cid}`).hide();
         } else {
            $("#nuevos").slideUp(1);
            $('#preview').remove();
            $('#nuevos').html(message).slideDown('slow', () => {
               $('#no-comments').hide('slow');
               $('.miComentario').html('<div class="alert-empty">Tu comentario fue agregado correctamente :)</div>');
            });
         }
         let total = parseInt($('#ncomments').text()) || 0;
         $('#ncomments').text(total + 1);
         if (!isReply) {
            $('.miComentario #gif_cargando').hide();
            $('#btnsComment').removeAttr('disabled');
         }
      });
   },
   // VOTAR COMENTARIO
   votar(btn, type) {
      const $btn = $(btn);
      const isActive = $btn.hasClass('active');
      const countEl = $btn.find('.action-count');
      const cid = parseInt($btn.data('cid')) || 0;
      let count = parseInt($btn.data('count')) || 0;
      const $parent = $btn.closest('.comment-footer');
      if ($parent.length) {
         let opposite = type === 'like' ? 'dislike' : 'like';
         const $oppdiv = $parent.find('.action-' + opposite);
         if ($oppdiv.length && $oppdiv.hasClass('active')) {
            const oppCount = parseInt($oppdiv.data('count')) || 0;
            const oppNew = Math.max(0, oppCount - 1);
            $oppdiv.data('count', oppNew);
            $oppdiv.find('.action-count').text(oppNew > 0 ? oppNew : '');
            $oppdiv.removeClass('active');
         }
      }
      //
      let params = $.param({ cid, type });
      params += queryParam('postid');
      api('comentario-votar', params, response => {
         const { status, message } = $.parseResponse(response);
         dialog.alert((status === 0 ? "Error al votar" : "Bien"), message);
         if(status === 0) return;
         if (isActive) {
            count = Math.max(0, count - 1);
            $btn.removeClass('active');
         } else {
            count += 1;
            $btn.addClass('active');
         }
         $btn.data('count', count);
         $countEl.text(count > 0 ? count : ''); 
      });
   },
   responder(formId, reply = false, cid = 0) {
      const $form = $(`#${formId}`);
      if (!$form.length) return;
      const isVisible = $form.is(':visible');
      if (reply) {
         this.nuevo(true, cid);
         return;
      }
      // Toggle: cerrar todos y abrir/cerrar el actual
      $('.reply-write').hide();
      if (!isVisible) {
         $form.show();
         $form.find('textarea').focus();
      }
   },
   // VER RESPUESTAS con "ver más"
   verRespuestas(containerId, btn) {
      const $container = $(`#${containerId}`);
      const $btn = $(btn);
      if (!$container.length) return;
      const isOpen = $container.is(':visible');
      if (isOpen) {
         $container.slideUp();
         $btn.removeClass('open').attr('aria-expanded', false);
         return;
      }
      // Primera apertura: inicializar límite de 2
      if (!$container.data('initialized')) {
         const $items = $container.children('li.comment-item');
         const total = $items.length;
         if (total > 2) {
            $items.slice(2).hide();
            const remaining = total - 2;
            $container.append(
               `<li class="replies-more">
                  <button class="btn-replies-more" onclick="comentario.verMasRespuestas('${containerId}', this)">Ver ${remaining} respuesta${remaining > 1 ? 's' : ''} más</button>
               </li>`
            );
         }
         $container.data('initialized', true);
      }

      $container.slideDown();
      $btn.addClass('open').attr('aria-expanded', true);
   },
   verMasRespuestas(containerId, btn) {
      $(`#${containerId}`).children('li.comment-item:hidden').show();
      $(btn).closest('.replies-more').remove();
   },
   // CITAR
   citar(id, nick) {
      const textarea = $('#body_comm');
      const getText = textarea.val();
      const content = $(`.comment-item[data-comment-id=${id}] .comment-body .comment-text`).data('bbcode');
      let text = (getText === '' ? '' : `${getText}\n`) + `[quote=${nick}]${content}[/quote]`;
      textarea.focus();
      textarea.val(text);

    	//textarea.val(((textarea.val()!='') ? textarea.val() + '\n' : '') + '[quote=' + nick + ']' + htmlspecialchars_decode($('#citar_comm_'+id).html(), 'ENT_NOQUOTES') + '[/quote]\n');
        /*
        var message = $.trim($('#comment-body-'+id).html());
    		$('.wysibb-texarea').execCommand('quote',{autor: nick, seltext: message});
        */
   },
   // EDITAR
   editar: function(id, step){
      switch(step){
         case 'show':
            var bbcode = htmlspecialchars_decode($('#citar_comm_'+id).html(), 'ENT_NOQUOTES');
            var html = '<textarea id="edit-comment-' + id + '" class="textarea-edit autogrow" placeholder="Escribir un comentario...">' + bbcode + '</textarea><br/><input type="button" class="mBtn btnGreen btnEdit" onclick="comentario.preview(\'' + id + '\', \'edit\')" value="Continuar &raquo;"/> <strong id="edit-error-' + id + '"></strong>';
            $('#comment-body-' + id).html(html);
            $('#edit-comment-' + id).css('max-height', '300px');
         break;
         case 'send':
            var cid = $('#edit-cid-' + id).val()
            var comment = $('#edit-comment-' + id).val();
            $('#loading').fadeIn(250); 
            $.ajax({
            	type: 'POST',
            	url: route.url + '/comentario-editar',
            	data: 'comentario=' + encodeURIComponent(comment) + '&cid=' + id,
            	success: function(h){
            		switch(h.charAt(0)){
            			case '0': //Error
                        $('#edit-error-' + id).css('color','red').html(h.substring(3));
            			break;
            			case '1': //OK
                        $('#comment-body-' + id).html($('#new-com-html').html());
                       	var bbcode = htmlspecialchars_decode($('#new-com-bbcode').html(), 'ENT_NOQUOTES');
                       	$('#citar_comm_'+id).html(bbcode) 
           				break;
            		}
                  $('#loading').fadeOut(350); 
            		mydialog.close();
            	}
            });
         break;
      }  
  	}
}

function toggleDropdown(id) {
   const menu = document.getElementById(id);
   if (!menu) return;
   const isOpen = menu.classList.contains('open');
   // Cerrar todos
   document.querySelectorAll('.comment-actions.open').forEach(m => m.classList.remove('open'));
   if (!isOpen) menu.classList.add('open');
}

function closeDropdown(id) {
   const menu = document.getElementById(id);
   if (menu) menu.classList.remove('open');
}

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(e) {
   if (!e.target.closest('.comment-more')) {
      document.querySelectorAll('.comment-actions.open').forEach(m => m.classList.remove('open'));
   }
});

$(() => {
	$('#body_comm').css({ height: 80 }).html('').wysibb({ 
		buttons: "smilebox,|,bold,italic,underline,strike,|,image,link" 
	});
});
