<?php

declare(strict_types=1);

/**
 * @package    PHPost/Logger
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 *
 * @name logs.view.php
 * @description Vista del visor de logs — integrar en sistema de vistas
 * 
 * Variables esperadas:
 *   $logFiles  → array de LogParser::getAvailableFiles()
 *   $entries   → array de entradas parseadas (LogParser::parseFile())
 *   $activeFile → array con info del archivo activo
 *   $filter     → string nivel activo ('ALL', 'ERROR', etc.)
 */

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

// ── Ejemplo de uso en tu controlador / router: ──────────────────────────────
// require_once TS_LOGGER . '/LogParser.php';
//
// $logsDir   = Config::app('paths.logs');
// $logFiles  = LogParser::getAvailableFiles($logsDir);
// $activeKey = $_GET['file'] ?? ($logFiles[0]['date'] . '_' . $logFiles[0]['channel'] ?? null);
// $filter    = strtoupper($_GET['level'] ?? 'ALL');
//
// $activeFile = null;
// $entries    = [];
// foreach ($logFiles as $f) {
//    $key = $f['date'] . '_' . $f['channel'];
//    if ($key === $activeKey) {
//       $activeFile = $f;
//       $entries    = LogParser::parseFile($f['path']);
//       break;
//    }
// }
// if ($filter !== 'ALL') {
//    $entries = array_filter($entries, fn($e) => $e['level'] === $filter);
// }
// ────────────────────────────────────────────────────────────────────────────

$levels = ['ALL', 'FATAL', 'EXCEPTION', 'ERROR', 'WARNING', 'INFO', 'DEBUG'];

$levelMeta = [
   'FATAL'     => ['color' => '#ff2d55', 'bg' => '#2a0a10', 'icon' => '💀'],
   'EXCEPTION' => ['color' => '#ff6b35', 'bg' => '#2a1200', 'icon' => '🔥'],
   'ERROR'     => ['color' => '#ff453a', 'bg' => '#25090a', 'icon' => '✖'],
   'WARNING'   => ['color' => '#ffd60a', 'bg' => '#252000', 'icon' => '⚠'],
   'INFO'      => ['color' => '#30d158', 'bg' => '#0a2010', 'icon' => 'ℹ'],
   'DEBUG'     => ['color' => '#64d2ff', 'bg' => '#001a25', 'icon' => '⚙'],
];

$totalByLevel = [];
foreach ($levels as $l) {
   if ($l === 'ALL') continue;
   $totalByLevel[$l] = count(array_filter($entries ?? [], fn($e) => $e['level'] === $l));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logs — PHPost</title>
<style>
   @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Syne:wght@400;600;700;800&display=swap');

   *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

   :root {
      --bg:        #0d0d0f;
      --surface:   #141416;
      --border:    #232328;
      --text:      #e2e2e8;
      --muted:     #555560;
      --accent:    #7c6af7;
      --mono:      'JetBrains Mono', monospace;
      --sans:      'Syne', sans-serif;
   }

   html, body { height: 100%; background: var(--bg); color: var(--text); font-family: var(--sans); }

   /* ── Layout ── */
   .log-shell {
      display: grid;
      grid-template-columns: 260px 1fr;
      grid-template-rows: 56px 1fr;
      height: 100vh;
      overflow: hidden;
   }

   /* ── Header ── */
   .log-header {
      grid-column: 1 / -1;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 24px;
      border-bottom: 1px solid var(--border);
      background: var(--surface);
   }
   .log-header h1 {
      font-size: 15px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--text);
   }
   .log-header .dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: var(--accent);
      box-shadow: 0 0 8px var(--accent);
      animation: pulse 2s infinite;
   }
   @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

   .header-right { margin-left: auto; font-family: var(--mono); font-size: 11px; color: var(--muted); }

   /* ── Sidebar ── */
   .log-sidebar {
      border-right: 1px solid var(--border);
      background: var(--surface);
      overflow-y: auto;
      padding: 16px 0;
   }
   .sidebar-section { padding: 0 16px; margin-bottom: 24px; }
   .sidebar-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 8px;
   }

   /* Archivos */
   .file-item {
      display: block;
      padding: 9px 12px;
      border-radius: 8px;
      font-family: var(--mono);
      font-size: 11px;
      color: var(--muted);
      text-decoration: none;
      transition: background .15s, color .15s;
      margin-bottom: 2px;
   }
   .file-item:hover { background: rgba(255,255,255,.05); color: var(--text); }
   .file-item.active { background: rgba(124,106,247,.15); color: var(--accent); }
   .file-item .file-size { font-size: 10px; opacity: .5; float: right; margin-top: 1px; }

   /* Filtros de nivel */
   .level-filter {
      display: flex;
      flex-direction: column;
      gap: 2px;
   }
   .level-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 7px 12px;
      border-radius: 8px;
      font-family: var(--mono);
      font-size: 11px;
      color: var(--muted);
      text-decoration: none;
      transition: background .15s, color .15s;
      cursor: pointer;
   }
   .level-btn:hover { background: rgba(255,255,255,.04); color: var(--text); }
   .level-btn.active { background: rgba(255,255,255,.07); color: var(--text); }
   .level-badge {
      margin-left: auto;
      font-size: 10px;
      padding: 1px 6px;
      border-radius: 999px;
      background: rgba(255,255,255,.06);
   }
   .level-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

   /* ── Main ── */
   .log-main {
      overflow-y: auto;
      padding: 20px 28px;
   }

   /* Toolbar */
   .log-toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
   }
   .log-count {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--muted);
   }
   .log-count span { color: var(--text); font-weight: 600; }

   /* Entries */
   .entry-list { display: flex; flex-direction: column; gap: 8px; }

   .entry {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: var(--surface);
      overflow: hidden;
      transition: border-color .2s;
   }
   .entry:hover { border-color: #3a3a42; }

   .entry-head {
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      cursor: pointer;
      user-select: none;
   }

   .entry-level {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-family: var(--mono);
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .08em;
      padding: 3px 9px;
      border-radius: 6px;
      white-space: nowrap;
   }

   .entry-message {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--text);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
   }

   .entry-meta {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-shrink: 0;
   }
   .entry-datetime {
      font-family: var(--mono);
      font-size: 10px;
      color: var(--muted);
      white-space: nowrap;
   }
   .entry-chevron {
      color: var(--muted);
      font-size: 12px;
      transition: transform .2s;
   }
   .entry.open .entry-chevron { transform: rotate(90deg); }

   /* Detail panel */
   .entry-detail {
      display: none;
      border-top: 1px solid var(--border);
      padding: 16px;
      background: rgba(0,0,0,.25);
   }
   .entry.open .entry-detail { display: block; }

   .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 10px;
      margin-bottom: 12px;
   }
   .detail-field {
      background: rgba(255,255,255,.03);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 10px 12px;
   }
   .detail-key {
      font-family: var(--mono);
      font-size: 9px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 4px;
   }
   .detail-val {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--text);
      word-break: break-all;
   }

   /* Stack trace */
   .trace-block {
      background: #0a0a0c;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 14px 16px;
      margin-top: 4px;
   }
   .trace-label {
      font-family: var(--mono);
      font-size: 9px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 10px;
   }
   .trace-line {
      font-family: var(--mono);
      font-size: 11px;
      line-height: 1.8;
      color: #888895;
      padding: 2px 0;
      border-bottom: 1px solid rgba(255,255,255,.03);
   }
   .trace-line:last-child { border-bottom: none; }
   .trace-line .tl-num  { color: var(--accent); margin-right: 10px; opacity: .6; }
   .trace-line .tl-path { color: #aaaabd; }
   .trace-line .tl-fn   { color: #e2e2e8; }

   /* Empty state */
   .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 60vh;
      gap: 12px;
      color: var(--muted);
   }
   .empty-state .icon { font-size: 48px; opacity: .3; }
   .empty-state p { font-size: 13px; }

   /* Scrollbar */
   ::-webkit-scrollbar { width: 6px; }
   ::-webkit-scrollbar-track { background: transparent; }
   ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
</style>
</head>
<body>

<div class="log-shell">

   <!-- Header -->
   <header class="log-header">
      <div class="dot"></div>
      <h1>PHPost · Logs</h1>
      <?php if ($activeFile ?? null): ?>
         <span style="font-family:var(--mono);font-size:11px;color:var(--muted);margin-left:8px;">
            <?= htmlspecialchars($activeFile['label']) ?>
         </span>
      <?php endif; ?>
      <div class="header-right"><?= date('d.m.Y H:i') ?></div>
   </header>

   <!-- Sidebar -->
   <aside class="log-sidebar">

      <!-- Archivos de log -->
      <div class="sidebar-section">
         <div class="sidebar-label">Archivos</div>
         <?php if (empty($logFiles ?? [])): ?>
            <p style="font-size:11px;color:var(--muted);padding:0 4px;">Sin archivos</p>
         <?php else: ?>
            <?php foreach ($logFiles as $f):
               $key     = $f['date'] . '_' . $f['channel'];
               $isActive = isset($activeFile) && ($activeFile['date'] . '_' . $activeFile['channel']) === $key;
               $sizeStr  = $f['size'] < 1024 ? $f['size'].'B' : round($f['size']/1024,1).'KB';
            ?>
               <a href="?file=<?= urlencode($key) ?>&level=<?= urlencode($filter ?? 'ALL') ?>"
                  class="file-item <?= $isActive ? 'active' : '' ?>">
                  <?= htmlspecialchars($f['label']) ?>
                  <span class="file-size"><?= $sizeStr ?></span>
               </a>
            <?php endforeach; ?>
         <?php endif; ?>
      </div>

      <!-- Filtro por nivel -->
      <div class="sidebar-section">
         <div class="sidebar-label">Nivel</div>
         <div class="level-filter">
            <?php foreach ($levels as $l):
               $meta    = $levelMeta[$l] ?? ['color' => '#888', 'bg' => '#111', 'icon' => '·'];
               $isActive = ($filter ?? 'ALL') === $l;
               $count    = $l === 'ALL' ? count($entries ?? []) : ($totalByLevel[$l] ?? 0);
               $fileParam = isset($activeFile) ? $activeFile['date'].'_'.$activeFile['channel'] : '';
            ?>
               <a href="?file=<?= urlencode($fileParam) ?>&level=<?= urlencode($l) ?>"
                  class="level-btn <?= $isActive ? 'active' : '' ?>">
                  <?php if ($l !== 'ALL'): ?>
                     <span class="level-dot" style="background:<?= $meta['color'] ?>"></span>
                  <?php else: ?>
                     <span style="font-size:12px">◈</span>
                  <?php endif; ?>
                  <?= $l ?>
                  <span class="level-badge"><?= $count ?></span>
               </a>
            <?php endforeach; ?>
         </div>
      </div>

   </aside>

   <!-- Main -->
   <main class="log-main">

      <?php if (empty($entries ?? [])): ?>
         <div class="empty-state">
            <div class="icon">📭</div>
            <p>No hay entradas<?= ($filter ?? 'ALL') !== 'ALL' ? ' con nivel <strong>'.$filter.'</strong>' : '' ?></p>
         </div>
      <?php else: ?>

         <div class="log-toolbar">
            <span class="log-count">
               <span><?= count($entries) ?></span> entrada<?= count($entries) !== 1 ? 's' : '' ?>
               <?= ($filter ?? 'ALL') !== 'ALL' ? ' · filtrado por <strong style="color:var(--text)">'.$filter.'</strong>' : '' ?>
            </span>
         </div>

         <div class="entry-list">
         <?php foreach ($entries as $i => $e):
            $meta = $levelMeta[$e['level']] ?? ['color' => '#888', 'bg' => '#1a1a1a', 'icon' => '·'];
         ?>
            <div class="entry" id="entry-<?= $i ?>">

               <div class="entry-head" onclick="toggleEntry(<?= $i ?>)">
                  <!-- Nivel -->
                  <span class="entry-level"
                        style="color:<?= $meta['color'] ?>;background:<?= $meta['bg'] ?>;border:1px solid <?= $meta['color'] ?>22">
                     <?= $meta['icon'] ?> <?= htmlspecialchars($e['level']) ?>
                  </span>

                  <!-- Mensaje -->
                  <span class="entry-message" title="<?= htmlspecialchars($e['message']) ?>">
                     <?= htmlspecialchars($e['message']) ?>
                  </span>

                  <!-- Meta -->
                  <div class="entry-meta">
                     <span class="entry-datetime"><?= htmlspecialchars($e['datetime']) ?></span>
                     <span class="entry-chevron">▶</span>
                  </div>
               </div>

               <!-- Detalle expandible -->
               <div class="entry-detail">
                  <div class="detail-grid">

                     <?php if ($e['file']): ?>
                     <div class="detail-field">
                        <div class="detail-key">Archivo</div>
                        <div class="detail-val"><?= htmlspecialchars($e['file']) ?></div>
                     </div>
                     <?php endif; ?>

                     <?php if ($e['line']): ?>
                     <div class="detail-field">
                        <div class="detail-key">Línea</div>
                        <div class="detail-val"><?= (int)$e['line'] ?></div>
                     </div>
                     <?php endif; ?>

                     <?php if ($e['severity']): ?>
                     <div class="detail-field">
                        <div class="detail-key">Severity</div>
                        <div class="detail-val"><?= htmlspecialchars($e['severity']) ?></div>
                     </div>
                     <?php endif; ?>

                     <?php foreach ($e['extra'] as $k => $v): ?>
                     <div class="detail-field">
                        <div class="detail-key"><?= htmlspecialchars($k) ?></div>
                        <div class="detail-val"><?= htmlspecialchars((string)$v) ?></div>
                     </div>
                     <?php endforeach; ?>

                  </div>

                  <!-- Stack trace -->
                  <?php if ($e['trace']): ?>
                  <div class="trace-block">
                     <div class="trace-label">Stack Trace</div>
                     <?php
                     $traceLines = array_filter(explode("\n", $e['trace']));
                     foreach ($traceLines as $tl):
                        // Parsear líneas tipo: #0 /path/to/file.php(12): Class->method()
                        if (preg_match('/^(#\d+)\s+(.*?)\((\d+)\):\s*(.+)$/', trim($tl), $tm)):
                     ?>
                        <div class="trace-line">
                           <span class="tl-num"><?= htmlspecialchars($tm[1]) ?></span>
                           <span class="tl-path"><?= htmlspecialchars($tm[2]) ?>(<?= $tm[3] ?>):</span>
                           <span class="tl-fn"> <?= htmlspecialchars($tm[4]) ?></span>
                        </div>
                     <?php else: ?>
                        <div class="trace-line"><?= htmlspecialchars(trim($tl)) ?></div>
                     <?php endif; endforeach; ?>
                  </div>
                  <?php endif; ?>
               </div>

            </div>
         <?php endforeach; ?>
         </div>

      <?php endif; ?>
   </main>

</div>

<script>
function toggleEntry(i) {
   const el = document.getElementById('entry-' + i);
   el.classList.toggle('open');
}
</script>

</body>
</html>
