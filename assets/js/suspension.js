function formatRemaining(seconds) {
   if (seconds <= 0) return 'Suspensión vencida';

   const d = Math.floor(seconds / 86400);
   seconds %= 86400;
   const h = Math.floor(seconds / 3600);
   seconds %= 3600;
   const m = Math.floor(seconds / 60);
   const s = seconds % 60;

   let out = [];
   if (d > 0) out.push(d + 'd');
   if (h > 0) out.push(h + 'h');
   if (m > 0) out.push(m + 'm');

   out.push(String(s).padStart(2, '0') + 's');

   return out.join(' ');
}

$('.countdown').each(function () {
   const $el = $(this);
   const end = parseInt($el.data('end'), 10);
   if (!end) return;

   function tick() {
      const now  = Math.floor(Date.now() / 1000);
      const diff = end - now;

      $el.text('Tiempo restante: ' + formatRemaining(diff));

      if (diff <= 0) {
         clearInterval(timer);
      }
   }

   tick(); // inmediato
   const timer = setInterval(tick, 1000); // ahora sí, cada segundo
});

function formatDateTime(ts) {
   const d = new Date(ts * 1000);

   const pad = n => String(n).padStart(2, '0');

   return (
      pad(d.getDate()) + '/' +
      pad(d.getMonth() + 1) + '/' +
      d.getFullYear() + ' ' +
      pad(d.getHours()) + ':' +
      pad(d.getMinutes()) + ':' +
      pad(d.getSeconds())
   );
}

$('.realtime-clock').each(function () {
   const $el = $(this);
   let now = parseInt($el.data('now'), 10);

   if (!now) return;

   function tick() {
      $el.text(formatDateTime(now) + ' hs');
      now++;
   }

   tick(); // primera render
   setInterval(tick, 1000);
});
