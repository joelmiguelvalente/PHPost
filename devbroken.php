<?php
/**
 * devbroken.php
 * ---------------------------------------------------------------
 * Página de identidad / ficha técnica autocontenida.
 * Lee el archivo de texto plano "devbroken" (mismo directorio) y
 * lo renderiza como una ficha visual.
 *
 * AISLAMIENTO INTENCIONAL:
 * Este archivo NO hace require/include de nada del proyecto
 * (sin bootstrap.php, sin Config, sin Smarty, sin DB). Es HTML+PHP
 * plano a propósito, para que pueda existir aunque el resto de la
 * app esté rota, en mantenimiento o caída.
 * ---------------------------------------------------------------
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
// Evita que buscadores indexen esta ficha interna.
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

/**
 * Parsea el formato del archivo "devbroken":
 *
 *   SECCION
 *       [CLAVE]     valor
 *
 * Una línea sin indentar en mayúsculas = nueva sección.
 * Una línea indentada con [CLAVE] valor = par dentro de la sección activa.
 *
 * @return array<string, array<string,string>>
 */
function parseDevBroken(string $path): array
{
    $sections = [];
    $current  = null;

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return $sections;
    }

    foreach ($lines as $raw) {
        if (trim($raw) === '') {
            continue;
        }

        $isHeader = $raw !== '' && !ctype_space($raw[0])
            && preg_match('/^[A-Z][A-Z0-9 ]*$/', trim($raw)) === 1;

        if ($isHeader) {
            $current = trim($raw);
            $sections[$current] = [];
            continue;
        }

        if ($current !== null && preg_match('/^\s*\[([A-Z0-9_]+)\]\s+(.*)$/', $raw, $m) === 1) {
            $sections[$current][$m[1]] = trim($m[2]);
        }
    }

    return $sections;
}

/** Convierte URLs y emails sueltos dentro de un valor en enlaces. */
function linkify(string $value): string
{
    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    if (preg_match('#^https?://#i', $value)) {
        return '<a href="' . $escaped . '" target="_blank" rel="noopener noreferrer">' . $escaped . '</a>';
    }
    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return '<a href="mailto:' . $escaped . '">' . $escaped . '</a>';
    }
    return $escaped;
}

$dataFile = __DIR__ . '/devbroken';
$sections = is_readable($dataFile) ? parseDevBroken($dataFile) : [];

$brand   = $sections['BRAND'] ?? [];
$project = $sections['PROJECT'] ?? [];
$author  = $sections['AUTHOR'] ?? [];
$status  = $sections['STATUS']['CURRENT'] ?? ($project['STATUS'] ?? 'Desconocido');

// Secciones que ya se muestran en el hero: no repetirlas en la grilla.
$hiddenFromGrid = ['BRAND', 'PROJECT', 'AUTHOR', 'STATUS'];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($brand['NAME'] ?? 'DevBroken') ?> · <?= htmlspecialchars($project['NAME'] ?? 'Ficha técnica') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Playfair+Display:ital@1&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0B0A10; --bg-alt:#0E0C15; --panel:#131019; --panel-raised:#181420;
  --border:#241F2E; --border-soft:#1B1723;
  --text:#EBE7F2; --text-dim:#9691AA; --text-faint:#5C5670;
  --accent:#8B7FFF; --accent-soft:rgba(139,127,255,.13); --accent-line:rgba(139,127,255,.4);
  --live:#38D9A9; --live-soft:rgba(56,217,169,.14);
  --font-display:'Sora','Segoe UI',sans-serif;
  --font-serif:'Playfair Display',Georgia,serif;
  --font-body:'Inter','Segoe UI',sans-serif;
  --font-mono:'JetBrains Mono',Consolas,monospace;
  --radius:12px;
}
html[data-theme="light"]{
  --bg:#F6F4FB; --bg-alt:#EFEBF7; --panel:#FFFFFF; --panel-raised:#FBFAFF;
  --border:#E2DCF0; --border-soft:#EAE5F5;
  --text:#221D30; --text-dim:#5C5670; --text-faint:#948FA6;
  --accent:#6F5CE6; --accent-soft:rgba(111,92,230,.10); --accent-line:rgba(111,92,230,.35);
  --live:#0FA37E; --live-soft:rgba(15,163,126,.10);
}
*{box-sizing:border-box;}
@media (prefers-reduced-motion: reduce){ *{ animation-duration:.001ms !important; transition-duration:.001ms !important; } }

body{
  margin:0;
  background:var(--bg);
  background-image:
    radial-gradient(ellipse 800px 460px at 88% -8%, var(--accent-soft), transparent 60%),
    radial-gradient(ellipse 620px 420px at 4% 12%, var(--live-soft), transparent 55%);
  color:var(--text);
  font-family:var(--font-body);
  line-height:1.6;
  transition:background-color .25s, color .25s;
}
a{ color:var(--accent); text-decoration:none; }
a:hover{ text-decoration:underline; }
:focus-visible{ outline:2px solid var(--accent); outline-offset:3px; border-radius:4px; }
code, .mono{ font-family:var(--font-mono); }

.wrap{ max-width:900px; margin:0 auto; padding:56px 24px 60px; }

/* ---------- top bar ---------- */
.topbar{ display:flex; justify-content:flex-end; margin-bottom:26px; }
.theme-btn{
  display:flex; align-items:center; gap:7px;
  background:var(--panel); border:1px solid var(--border);
  color:var(--text-dim); font-family:var(--font-mono); font-size:12px;
  padding:8px 13px; border-radius:20px; cursor:pointer;
}
.theme-btn:hover{ color:var(--text); border-color:var(--accent-line); }

/* ---------- hero / nameplate ---------- */
.plate{
  background:var(--panel);
  border:1px solid var(--border);
  border-radius:16px;
  padding:34px 32px;
  margin-bottom:34px;
  position:relative;
  overflow:hidden;
}
.plate::before{
  content:'';
  position:absolute; inset:0;
  background:linear-gradient(120deg, var(--accent-soft), transparent 55%);
  pointer-events:none;
}
.brand-row{ display:flex; align-items:center; gap:12px; margin-bottom:22px; position:relative; }
.brand-mark{
  width:38px; height:38px; border-radius:10px; flex:none;
  background:linear-gradient(135deg, var(--accent), #5b4fd1);
  display:flex; align-items:center; justify-content:center;
  font-family:var(--font-display); font-weight:700; color:#fff; font-size:15px;
}
.brand-name{ font-family:var(--font-display); font-weight:600; font-size:15.5px; }
.brand-focus{ font-family:var(--font-mono); font-size:11px; color:var(--text-faint); margin-top:1px; }

.status-pill{
  display:inline-flex; align-items:center; gap:7px;
  background:var(--live-soft); border:1px solid rgba(56,217,169,.35);
  color:var(--live); font-family:var(--font-mono); font-size:11px;
  padding:5px 11px; border-radius:20px; margin-bottom:16px; position:relative;
}
.status-dot{ width:7px; height:7px; border-radius:50%; background:var(--live); animation:pulse 1.8s ease-in-out infinite; }
@keyframes pulse{ 0%,100%{ opacity:1; } 50%{ opacity:.35; } }

.project-name{ font-family:var(--font-display); font-size:clamp(28px,4vw,40px); font-weight:700; margin:0 0 6px; position:relative; }
.project-slogan{ font-family:var(--font-serif); font-style:italic; font-size:18px; color:var(--text-dim); margin:0 0 22px; position:relative; }

.plate-meta{ display:flex; flex-wrap:wrap; gap:10px; position:relative; }
.meta-chip{
  font-family:var(--font-mono); font-size:11.5px;
  background:var(--panel-raised); border:1px solid var(--border-soft);
  color:var(--text-dim); padding:6px 11px; border-radius:8px;
}
.meta-chip b{ color:var(--text); font-weight:600; }

/* ---------- author strip ---------- */
.author-strip{
  display:flex; flex-wrap:wrap; gap:10px 22px;
  padding:16px 32px;
  border:1px solid var(--border);
  border-radius:12px;
  margin-bottom:40px;
  background:var(--panel);
  font-size:13px;
}
.author-strip .item{ display:flex; gap:6px; color:var(--text-dim); }
.author-strip .item b{ color:var(--text); font-weight:600; }

/* ---------- spec grid ---------- */
.grid-head{ margin-bottom:18px; }
.grid-head .eyebrow{
  font-family:var(--font-mono); font-size:11px; letter-spacing:.5px;
  color:var(--accent); text-transform:uppercase; margin-bottom:6px;
}
.grid-head h2{ font-family:var(--font-display); font-size:20px; margin:0; }

.spec-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:14px;
}
@media (max-width:680px){ .spec-grid{ grid-template-columns:1fr; } }

.spec-card{
  background:var(--panel);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:16px 18px 6px;
}
.spec-title{
  font-family:var(--font-display); font-size:13.5px; font-weight:600;
  text-transform:uppercase; letter-spacing:.4px; color:var(--text-dim);
  margin-bottom:10px;
}
.spec-row{
  display:flex; gap:10px; justify-content:space-between;
  padding:9px 0; border-top:1px solid var(--border-soft);
  font-size:12.5px;
}
.spec-row:first-of-type{ border-top:none; }
.spec-key{ font-family:var(--font-mono); color:var(--text-faint); flex:none; white-space:nowrap; }
.spec-val{ font-family:var(--font-mono); color:var(--text); text-align:right; word-break:break-word; }

/* ---------- footer ---------- */
footer{
  margin-top:48px; padding-top:24px;
  border-top:1px solid var(--border);
  color:var(--text-faint); font-size:11.5px;
  display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;
}
footer code{ color:var(--text-dim); }

.empty-state{
  border:1px dashed var(--border); border-radius:12px;
  padding:26px; text-align:center; color:var(--text-faint);
  font-family:var(--font-mono); font-size:12.5px;
}
</style>
</head>
<body>
<div class="wrap">

  <div class="topbar">
    <button class="theme-btn" id="themeBtn" type="button" aria-label="Cambiar tema claro/oscuro">
      <span id="themeIcon">🌙</span> <span id="themeLabel">Oscuro</span>
    </button>
  </div>

  <?php if (empty($sections)): ?>
    <div class="empty-state">No se pudo leer el archivo <code>devbroken</code> en <code><?= htmlspecialchars($dataFile) ?></code>.</div>
  <?php else: ?>

    <div class="plate">
      <div class="brand-row">
        <div class="brand-mark"><?= htmlspecialchars(mb_substr($brand['NAME'] ?? 'DB', 0, 2)) ?></div>
        <div>
          <div class="brand-name"><?= htmlspecialchars($brand['NAME'] ?? '') ?></div>
          <div class="brand-focus"><?= htmlspecialchars($brand['TYPE'] ?? '') ?> · <?= htmlspecialchars($brand['FOCUS'] ?? '') ?></div>
        </div>
      </div>

      <div class="status-pill"><span class="status-dot"></span><?= htmlspecialchars($status) ?></div>

      <h1 class="project-name"><?= htmlspecialchars($project['NAME'] ?? '') ?></h1>
      <?php if (!empty($project['SLOGAN'])): ?>
        <p class="project-slogan">"<?= htmlspecialchars($project['SLOGAN']) ?>"</p>
      <?php endif; ?>

      <div class="plate-meta">
        <?php foreach (['VERSION' => 'v', 'CATEGORY' => '', 'LICENSE' => '', 'CREATED' => 'desde ', 'UPDATED' => 'act. '] as $key => $prefix): ?>
          <?php if (!empty($project[$key])): ?>
            <span class="meta-chip"><b><?= htmlspecialchars($prefix) ?><?= htmlspecialchars($project[$key]) ?></b></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!empty($author)): ?>
    <div class="author-strip">
      <?php foreach ($author as $k => $v): ?>
        <span class="item"><b><?= htmlspecialchars(ucfirst(strtolower($k))) ?>:</b> <?= linkify($v) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid-head">
      <div class="eyebrow">Ficha técnica</div>
      <h2>Stack, arquitectura y estado del proyecto</h2>
    </div>

    <div class="spec-grid">
      <?php foreach ($sections as $secName => $fields): ?>
        <?php if (in_array($secName, $hiddenFromGrid, true) || empty($fields)) continue; ?>
        <div class="spec-card">
          <div class="spec-title"><?= htmlspecialchars($secName) ?></div>
          <?php foreach ($fields as $k => $v): ?>
            <div class="spec-row">
              <span class="spec-key">[<?= htmlspecialchars($k) ?>]</span>
              <span class="spec-val"><?= linkify($v) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <footer>
      <span>© <?= date('Y') ?> <?= htmlspecialchars($brand['NAME'] ?? '') ?> · <code>devbroken</code> — página aislada, sin dependencias del proyecto.</span>
      <span>Generado desde <code>/devbroken</code></span>
    </footer>

  <?php endif; ?>
</div>

<script>
(function(){
  "use strict";
  var root = document.documentElement;
  var btn = document.getElementById('themeBtn');
  var icon = document.getElementById('themeIcon');
  var label = document.getElementById('themeLabel');

  function apply(theme){
    root.setAttribute('data-theme', theme);
    icon.textContent = theme === 'dark' ? '🌙' : '☀️';
    label.textContent = theme === 'dark' ? 'Oscuro' : 'Claro';
  }

  var saved = null;
  try { saved = localStorage.getItem('devbroken-theme'); } catch (e) {}
  apply(saved === 'light' ? 'light' : 'dark');

  btn.addEventListener('click', function(){
    var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    apply(next);
    try { localStorage.setItem('devbroken-theme', next); } catch (e) {}
  });
})();
</script>
</body>
</html>
