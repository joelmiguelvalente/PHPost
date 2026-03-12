/**
 * GitHub Last Commit Widget
 * Muestra el último commit de una rama del repo PHPost
 */

const branches = ['php-8-migration', 'master'];
let currentBranch = branches[0];

/**
 * Inicializa el selector de ramas y carga el primer commit
 */
function initGithubWidget() {
   const $selector = $('#branchSelector');
   const $widget   = $('#lastCommit');

   if (!$selector.length || !$widget.length) return;

   // Construir opciones del selector
   branches.forEach(branch => {
      $selector.append(
         `<option value="${branch}" ${branch === currentBranch ? 'selected' : ''}>${branch}</option>`
      );
   });

   // Cambio de rama
   $selector.on('change', function () {
      currentBranch = $(this).val();
      loadCommit(currentBranch);
   });

   // Carga inicial
   loadCommit(currentBranch);
}

/**
 * Carga el último commit de una rama y lo renderiza
 */
function loadCommit(branch = 'php-8-migration') {
   const $widget = $('#lastCommit');

   $widget.html('<div class="text-xs text-gray-400 text-center py-2">Cargando...</div>');

   api(`github-commit.php`, { branch }, response => {
      const result = typeof response === 'string' ? JSON.parse(response) : response;

      if (!result || result.state === 0) {
         $widget.html('<div class="text-xs text-gray-400 text-center py-2">No se pudo cargar el último commit.</div>');
         return;
      }

      const { sha, html_url, message, author, date, verified, reason } = result.data;

      let messageFmt  = message.replace(/\n/g, '-');
      let contentHtml = '';

      messageFmt.split('--').forEach((msg, i) => {
         const verifiedLabel = verified ? 'verified' : reason;
         const badgeHtml     = i === 0
            ? `<span class="text-xs font-bold px-2 py-0.5 rounded-full ${verified ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'}">${verifiedLabel}</span>`
            : '';
         const lineClass = i === 0
            ? 'flex items-center gap-2 flex-wrap text-xs font-semibold text-gray-800 dark:text-gray-100'
            : 'text-xs text-gray-500 dark:text-gray-400';
         contentHtml += `<span class="block mb-1 ${lineClass}">${msg.trim()} ${badgeHtml}</span>`;
      });

      $widget.html(`
         <div class="mb-2">${contentHtml}</div>
         <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-400">
            <span>SHA: <a href="${html_url}" target="_blank" rel="noreferrer" class="text-blue-500 hover:underline">${sha.substring(0, 8)}...</a></span>
            <time class="italic">${timeAgo(date)}</time>
         </div>
      `);
   });
}

// Autoejecución
$(document).ready(() => initGithubWidget());