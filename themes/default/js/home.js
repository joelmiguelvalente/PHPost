/**
 * Actualizar comentarios
*/
function actualizar_comentarios() {
	const $lista = $('#ult_comm')
   $('#loading').fadeIn(250);
   $lista.html('<div class="alert-empty">Esperando...</div>')
   $.get(`${route.url}/posts-last-comentarios`, response => {
   	$lista.html(response);
   })
   .fail(() => {
   	$('#ult_comm, #ult_comm > ol:first').slideDown({duration: 1000, easing: 'easeOutBounce'});
   	$('#loading').fadeOut(350);
   });
	$('#loading').fadeOut(350);
}
/**
 * Tabs de los tops
*/

$(() => {

	const $form = $('form');
	const $query = $form.find('#query');
	const $engine = $form.find('input[name="engine"]');
	const $barItems = $('.bar-options .bar-item');
	// Delegando eventos. :D
	// No cambiar a funcion flecha `this` no funcionaria
	$('.bar-options').on('click', '.bar-item', function () {
		const $item = $(this);
		const searchType = $item.data('search');
		const isMetaSearch = ['autor', 'tags'].includes(searchType);

		$barItems.removeClass('active');
		$item.addClass('active');

		$query.attr({
			placeholder: `Buscar ${isMetaSearch ? 'por' : 'en'} ${searchType}...`,
			name: isMetaSearch ? searchType : 'query'
		});

		$engine.val(isMetaSearch ? 'web' : searchType);
	});

	$('#sh_options').on('click', () => $('#search_box .category').toggle());

	$('.box-filter').on('click', 'span', function () {
		const $btn = $(this);
		const boxId = $btn.data('box');
		const filterId = $btn.attr('id');
		const $box = $(`#${boxId}`);

		// Activar botón
		$box.find('.box-filter span').removeClass('active');
		$btn.addClass('active');

		// Mostrar contenido
		$box.find('.filter-list.tops').fadeOut();
		$box.find(`#filter${filterId}`).fadeIn();
	});


});
