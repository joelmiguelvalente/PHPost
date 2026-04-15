<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logs — PHPost</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet"/>
{load file=['logs'] type="css"}
<script>
const route = {
   url:'{$tsConfig.url}'
}
</script>
{load file=['jquery.min','jquery.plugins'] type="js"}
</head>
<body>

<div class="log-shell">

   <!-- Header -->
   <header class="log-header">
      <div class="dot"></div>
      <h1>PHPost · Logs</h1>
      {if $activeFile}
         <span style="font-family:var(--mono);font-size:11px;color:var(--muted);margin-left:8px;">
            {$activeFile.label}
         </span>
      {/if}
      <div class="header-right">{$smarty.now|fecha:'full_datetime'}</div>
   </header>

   <!-- Sidebar -->
   <aside class="log-sidebar">

      <!-- Archivos de log -->
      <div class="sidebar-section">
         <div class="sidebar-label">Archivos</div>
         {if empty($logFiles)}
            <p style="font-size:11px;color:var(--muted);padding:0 4px;">Sin archivos</p>
         {else}
            {foreach $logFiles as $f}
               <a href="{$f.url}" class="file-item{if $f.isActive} active{/if}">
                  {$f.label}
                  <span class="file-size">{$f.sizeStr}</span>
               </a>
            {/foreach}
         {/if}
      </div>

      <!-- Filtro por nivel -->
      <div class="sidebar-section">
         <div class="sidebar-label">Nivel</div>
         <div class="level-filter">
            {foreach $levelsData as $l}
               <a href="{$l.url}" class="level-btn{if $l.isActive} active{/if}">
                  {if $l.isAll}
                     <span style="font-size:12px">&#9672;</span>
                  {else}
                     <span class="level-dot" style="background:{$l.color}"></span>
                  {/if}
                  {$l.name}
                  <span class="level-badge">{$l.count}</span>
               </a>
            {/foreach}
         </div>
      </div>

   </aside>

   <!-- Main -->
   <main class="log-main">

      {if empty($entries)}
         <div class="empty-state">
            <div class="icon">&#128205;</div>
            <p>No hay entradas{if $filterActive} con nivel <strong>{$filter}</strong>{/if}</p>
         </div>
      {else}

         <div class="log-toolbar">
            <span class="log-count">
               <span>{$entryCount}</span> {$entryLabel}
               {if $filterActive} &middot; filtrado por <strong style="color:var(--text)">{$filter}</strong>{/if}
            </span>
         </div>

         <div class="entry-list">
         {foreach $entries as $e}
            <div class="entry" id="entry-{$e.id}">

               <div class="entry-head" onclick="toggleEntry({$e.id})">

                  <span class="entry-level"
                        style="color:{$e.color};background:{$e.bg};border:1px solid {$e.border}">
                     {$e.icon} {$e.level}
                  </span>

                  <span class="entry-message" title="{$e.message|escape}">
                     {$e.message|escape}
                  </span>

                  <div class="entry-meta">
                     <span class="entry-datetime">{$e.datetime|escape}</span>
                     <button class="btn-fixed"
                             onclick="event.stopPropagation(); markFixed({$e.id}, this)"
                             data-file="{$activeFile.filename|escape}"
                             data-datetime="{$e.rawDatetime|escape}"
                             title="Marcar como arreglado">
                        &#10003; Arreglado
                     </button>
                     <span class="entry-chevron">&#9658;</span>
                  </div>

               </div>

               <div class="entry-detail">
                  <div class="detail-grid">

                     {if $e.file}
                     <div class="detail-field">
                        <div class="detail-key">Archivo</div>
                        <div class="detail-val">{$e.file|escape}</div>
                     </div>
                     {/if}

                     {if $e.line}
                     <div class="detail-field">
                        <div class="detail-key">Linea</div>
                        <div class="detail-val">{$e.line}</div>
                     </div>
                     {/if}

                     {if $e.severity}
                     <div class="detail-field">
                        <div class="detail-key">Severity</div>
                        <div class="detail-val">{$e.severity|escape}</div>
                     </div>
                     {/if}

                     {foreach $e.extra as $k => $v}
                     <div class="detail-field">
                        <div class="detail-key">{$k|escape}</div>
                        <div class="detail-val">{$v|escape}</div>
                     </div>
                     {/foreach}

                  </div>

                  {if $e.traceLines}
                  <div class="trace-block">
                     <div class="trace-label">Stack Trace</div>
                     {foreach $e.traceLines as $tl}
                        {if $tl.parsed}
                           <div class="trace-line">
                              <span class="tl-num">{$tl.num}</span>
                              <span class="tl-path">{$tl.path|escape}({$tl.line}):</span>
                              <span class="tl-fn"> {$tl.fn|escape}</span>
                           </div>
                        {else}
                           <div class="trace-line">{$tl.raw|escape}</div>
                        {/if}
                     {/foreach}
                  </div>
                  {/if}

                  {if $e.snippet}
                  <div class="snippet-block">
                     <div class="snippet-label">
                        <span>&#128196;</span>
                        {$e.file|escape}
                     </div>
                     <div class="snippet-lines">
                        {foreach $e.snippet as $sl}
                           <div class="snippet-row{if $sl.isError} is-error{/if}">
                              <span class="sn-num">{$sl.number}</span>
                              <span class="sn-code">{$sl.code|escape}</span>
                           </div>
                        {/foreach}
                     </div>
                  </div>
                  {/if}

               </div>
            </div>
         {/foreach}
         </div>

      {/if}
   </main>

</div>

<script>
function toggleEntry(i) {
   const el = document.getElementById('entry-' + i);
   el.classList.toggle('open');
}

function markFixed(id, btn) {
   const file     = btn.dataset.file;
   const datetime = btn.dataset.datetime;
   const entry    = document.getElementById('entry-' + id);

   btn.disabled    = true;
   btn.textContent = '...';

   api('logs-borrar', { file, datetime }, response => {
      let result;
      try {
         result = typeof response === 'string' ? JSON.parse(response) : response;
      } catch(e) {
         result = $.parseResponse(response);
      }

      if (result.status) {
         entry.style.transition = 'opacity .3s, transform .3s';
         entry.style.opacity    = '0';
         entry.style.transform  = 'translateX(10px)';
         setTimeout(() => entry.remove(), 320);
      } else {
         btn.disabled    = false;
         btn.textContent = '✓ Arreglado';
         dialog.toast({ type: 'danger', message: result.message, position: 'bottom-left' });
      }
   });
}
</script>

</body>
</html>
