// Desarrollado con asistencia de Claude (Anthropic)
$(document).ready(() => {

   // ── Pestañas de motor de búsqueda ──────────────────────────────────────────
   $('span[role=button][data-select]').on('click', function () {
      const engine = $(this).data('select');

      // Visual: activa la pestaña clickeada
      $('span[role=button][data-select]').removeClass('active');
      $(this).addClass('active');

      // Actualiza el input hidden del form
      $('input[name=engine]').val(engine.toLowerCase());

      // Auto-submit si ya hay una búsqueda activa
      const query = $('input[name=query]').val().trim();
      const autor = $('input[name=autor]').val().trim();
      if (query.length > 0 || autor.length > 0) {
         $('form[name=buscador]').trigger('submit');
      }
   });

   // ── Highlight del término buscado en resultados ────────────────────────────
   const queryParam = new URLSearchParams(window.location.search).get('query') || '';
   if (queryParam.length > 1) {
      highlightTerms(queryParam);
   }

   function highlightTerms(term) {
      const safeterm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const regex    = new RegExp(`(${safeterm})`, 'gi');

      // Solo resaltar en títulos y cuerpos de resultado, no en toda la página
      const targets = document.querySelectorAll(
         '.result-title, .muro-body, .foto-title, .foto-desc, .user-name'
      );

      targets.forEach(el => {
         if (el.children.length === 0) {
            el.innerHTML = el.textContent.replace(
               regex,
               '<mark class="search-highlight">$1</mark>'
            );
         }
      });
   }

   // ── Mostrar/ocultar filtro de categoría solo cuando aplica ─────────────────
   function syncCategoryVisibility() {
      const engine = $('input[name=engine]').val();
      const $catGroup = $('#categoria').closest('.group');
      // La categoría solo aplica a posts
      if (engine === 'web' || engine === 'tags') {
         $catGroup.show();
      } else {
         $catGroup.hide();
      }
   }

   // Al cambiar de pestaña, actualizar visibilidad
   $('span[role=button][data-select]').on('click', function () {
      syncCategoryVisibility();
   });

   // Al cargar la página
   syncCategoryVisibility();

});
