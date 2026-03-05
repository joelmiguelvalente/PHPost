const showMessage = (message) => {
	const $mensajeElement = $('.post-metadata .mensajes').addClass((status === 0 ? 'error' : 'ok')).html(message).slideDown();
	// Hacer scroll hasta el elemento de mensaje después de mostrarlo
   setTimeout(() => {
      $mensajeElement[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
   }, 100);
}
const borrarComentario = (comid, autor, postid, next) => {
	if(!next)  {
		let body = (next === 0) ? '&iquest;Seguro que deseas borrar este post?' : 'Te pregunto de nuevo... &iquest;Seguro que deseas borrar este post?';
		dialog.ease('Borrar Comentarios', '&#191;Quiere eliminar este comentario?', 'Si, borrar', borrarComentario(comid, autor, postid, next))
		return;
	}
	dialog.loading('Espere por favor...', 'Borrando Post');
	let params = `comid=${comid}&autor=${autor}${queryParam('postid')}`;
	api('comentario-borrar.php', params, response => {
		const { status, message } = $.parseResponse(response);
		if(status === 0) {
			dialog.alert('Error', message);
		} else if(stauts === 1) {
			$('#ncomments').text(parseInt($('#ncomments').text()) - 1);
			$('#div_cmnt_' + comid).slideUp('normal', () => $(this).remove());
		}
	}, {
	   error: ({ xhr, status, error }) => {
	   	dialog.reintentar(borrarComentario(comid, autor, postid, true));
	   }
	});
}

/* Ocultar Comentario */
const ocultarComentario = (comid, autor, postid) => {
	let params = `comid=${comid}&autor=${autor}${queryParam('postid')}`;
	api('comentario-ocultar.php', params, response => {
   	const { status, message } = $.parseResponse(response);
   	if(status === 0) {
   		dialog.alert('Error', message);
   	} else if (status >= 1) {
   		$('#comentario_' +comid).css('opacity', (status === 1 ? 1 : .5));
			$('#pp_' +comid).css('opacity', (status === 1 ? .5 : 1));
   	}
	}, {
	   error: ({ xhr, status, error }) => {
	   	dialog.reintentar(ocultarComentario(comid, autor, postid, true));
	   }
	});
}

const borrarPost = (next = 0) => {
	if(next <= 1)  {
		let body = (next === 0) ? '&iquest;Seguro que deseas borrar este post?' : 'Te pregunto de nuevo... &iquest;Seguro que deseas borrar este post?';
		dialog.ease('Borrar Post', body, 'Si, borrar', borrarPost(next + 1))
		return;
	}
	dialog.loading('Espere por favor...', 'Borrando Post');
	api('posts-borrar.php', queryParam('postid', true), response => {
		const { status, message } = $.parseResponse(response);
		const title = status === 1 ? 'Post borrado...' : 'Error';
		dialog.alert(title, message);
	}, {
	   error: ({ xhr, status, error }) => {
	   	dialog.reintentar(borrarPost(2));
	   }
	});
}

/* Votar post */
let isVoted = false;
const showVoteForce = force_hide => {
	if(isVoted) return;
	let state = (!force_hide && darPuntos.css('display') === 'none');
	$('.post-metadata .dar_puntos')[(state ? 'show' : 'hide')]();
}
const votarPost = (puntos = 0) => {
	if(isVoted) return;
   if(puntos < 1) {
		dialog.alert('Error', 'Debe introducir n&uacute;meros');
      return false;
   }
	isVoted = true;
   api('posts-votar.php', 'puntos=' + puntos + queryParam('postid'), response => {
		const { status, message } = $.parseResponse(response);
   	showVoteForce(true);
   	$('.dar-puntos').slideUp();
   	showMessage(message);
		if(status === 1) {
			const $puntos = $('#puntosPost');
			let number = $puntos.html().replace(".", "");
			$puntos.html(number_format(parseInt(number) + parseInt(puntos), 0, ',', '.'));
			return;
		}
	}, {
	   error: ({ xhr, status, error }) => {
			isVoted = false;
	   	dialog.reintentar(votarPost(puntos));
	   }
	});
}
/* Agregar post a favoritos */
var isFavorite = false;
const addFavorite = () =>{
	if(isFavorite) return;
	if(!queryParam('userkey')) {
		dialog.alert('Login', 'Tienes que estar logueado para realizar esta operaci&oacute;n');
		return;
	}
	isFavorite = true;
   api('favoritos-agregar.php', queryParam('postid', true), response => {
		const { status, message } = $.parseResponse(response);
   	showMessage(message);
		if(status === 1) {
			const $puntos = $('.favoritos_post');
			let number = $puntos.html().replace(".", "");
			$puntos.html(number_format(parseInt(number) + 1, 0, ',', '.'));
			return;
		}
	}, {
	   error: ({ xhr, status, error }) => {
			isFavorite = false;
	   	dialog.reintentar(addFavorite());
	   }
	});
}

/* BBCode */
const spoiler = obj => $(obj).toggleClass('show').parent().next().slideToggle();

function toggleState($btn, action, postId) {
   const currentState = $btn.data('currentState') === 1;
   const newText = (action === 'sticky') ? (currentState ? 'Poner Sticky' : 'Quitar Sticky') : (!currentState ? 'Abrir Post' : 'Cerrar Post');
   $btn.html(newText);
   $btn.data('currentState', currentState ? 0 : 1);
   moderacion.reboot(postId, 'posts', action, false);
}

const followUserPost = (obj, follow = 'Usuario') => {
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
	   fn: (follow === 'Usuario' ? notifica.userInPostHandle : notifica.inPostHandle),
	   obj: obj
	});
}

$(() => {
	// Seguir o dejar de seguir usuarios
	$('#followUser').on('click', function() {
		followUserPost($(this));
	});

	$('#followPost').on('click', function() {
		followUserPost($('#followPost span'), 'Post');
	});

	$('#postFavorito').on('click', function() {
		const $this = $(this);
		const action = $this.data('action');
		const reload = $this.data('reload');
		if (action) {
			// Usuario logueado - ejecuta la acción
			if(action === 'addFavorite') {
				addFavorite();
			} else {
				console.warn('Acción desconocida:', action);
			}
		} else if (reload) {
			// Usuario no logueado - redirige al login con redirect
			window.location.href = reload;
		}
	});

   $('.action-btn').on('click', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const action = $btn.data('action');
      const postId = $btn.data('postId');
      switch (action) {
         case 'sticky':
         case 'openclosed':
            toggleState($btn, action, postId);
         break;
         case 'delete':
            const isOwnPost = $btn.closest('[data-is-author]').data('isAuthor');
            if (isOwnPost) {
               borrar_post();
            } else {
               moderacion.posts.borrar(postId, 'posts', null);
            }
         break;
         case 'ocultar':
            moderacion.posts.ocultar(postId);
         break;
         case 'denuncia':
         	const postTitle = $btn.data('title');
         	const postUsername = $btn.data('username');
            denuncia.nueva('post', postId, postTitle, postUsername);
         break;
     		default:
         break;
      }
      // Toggle UI para "Ocultar"
      const targetId = $btn.data('toggleTarget');
      if (targetId) {
         const $target = $('#' + targetId);
         $target.slideToggle();
         if ($btn.hasClass('des_approve')) {
            $btn.fadeOut();
         }
      }
   });
   $('.dar-puntos > .puntuar > input[type=button]').on('click', () => 
   	votarPost(parseInt($('input#points').val()))
   );
});