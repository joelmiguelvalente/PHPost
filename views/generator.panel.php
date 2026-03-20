<?php

/**
 * @name generator.panel.php
 * @description Panel web del generador de secciones (admin)
 * @author PHPost Team
 * @copyright 2026
 *
 * Requiere: src/Utils/Generator.php
 */

declare(strict_types=1);

define('TS_HEADER', true);

$root = dirname(__DIR__, 1);

// ─── Seguridad: solo admin ────────────────────────────────────────────────────
require_once $root . '/header.php';
//
if ($tsUser->is_admod !== 1) { 
   header('Location: ../'); 
   exit; 
}
require_once TS_UTILS . '/Generator.php';

// ─── Detectar themes disponibles ─────────────────────────────────────────────
$themesDir    = TS_THEMES;
$availThemes  = [];
if (is_dir($themesDir)) {
   foreach (scandir($themesDir) as $entry) {
      if ($entry[0] !== '.' && is_dir("$themesDir/$entry")) {
         $availThemes[] = $entry;
      }
   }
}

// ─── Procesar formulario ──────────────────────────────────────────────────────
$result  = null;
$posted  = $_SERVER['REQUEST_METHOD'] === 'POST';
$error   = '';

if ($posted) {
   $name  = trim($_POST['name']  ?? '');
   $theme = trim($_POST['theme'] ?? 'default');
   $options = [
      'api' => !empty($_POST['api']),
      'css' => !empty($_POST['css']),
      'js'  => !empty($_POST['js']),
   ];

   if (!PHPostGenerator::validateName($name)) {
      $error = 'El nombre no es válido. Solo letras minúsculas, números y guión bajo. Mínimo 2 caracteres.';
   } else {
      $generator = new PHPostGenerator($name, $options, $root, $theme);
      $result    = $generator->run();
   }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generador de Secciones — PHPost</title>
<style>
   *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

   body {
      font-family: system-ui, -apple-system, sans-serif;
      background: #0f1117;
      color: #e2e8f0;
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 2rem 1rem;
   }

   .card {
      background: #1a1d27;
      border: 1px solid #2d3148;
      border-radius: 12px;
      width: 100%;
      max-width: 560px;
      overflow: hidden;
   }

   .card-header {
      padding: 1.5rem 2rem;
      border-bottom: 1px solid #2d3148;
      display: flex;
      align-items: center;
      gap: .75rem;
   }

   .card-header h1 {
      font-size: 1.1rem;
      font-weight: 600;
      color: #f1f5f9;
   }

   .card-header span {
      font-size: .75rem;
      background: #2d3148;
      color: #94a3b8;
      padding: .2rem .6rem;
      border-radius: 20px;
   }

   .card-body { padding: 1.75rem 2rem; }

   .field { margin-bottom: 1.25rem; }

   label {
      display: block;
      font-size: .8rem;
      font-weight: 500;
      color: #94a3b8;
      margin-bottom: .4rem;
      text-transform: uppercase;
      letter-spacing: .05em;
   }

   input[type="text"], select {
      width: 100%;
      background: #0f1117;
      border: 1px solid #2d3148;
      border-radius: 8px;
      color: #f1f5f9;
      padding: .65rem 1rem;
      font-size: .95rem;
      transition: border-color .2s;
      outline: none;
   }

   input[type="text"]:focus, select:focus {
      border-color: #6366f1;
   }

   input[type="text"]::placeholder { color: #4b5563; }

   .hint {
      font-size: .75rem;
      color: #64748b;
      margin-top: .3rem;
   }

   /* Checkboxes */
   .checks {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: .75rem;
      margin-top: .5rem;
   }

   .check-item {
      background: #0f1117;
      border: 1px solid #2d3148;
      border-radius: 8px;
      padding: .75rem 1rem;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      display: flex;
      align-items: center;
      gap: .5rem;
   }

   .check-item:has(input:checked) {
      border-color: #6366f1;
      background: #1e1f3a;
   }

   .check-item input { accent-color: #6366f1; cursor: pointer; }

   .check-item .label-text {
      font-size: .85rem;
      color: #cbd5e1;
      font-weight: 500;
   }

   .check-item .badge {
      margin-left: auto;
      font-size: .65rem;
      color: #64748b;
      background: #1e293b;
      padding: .1rem .4rem;
      border-radius: 4px;
   }

   /* Required files */
   .required-files {
      background: #0f1117;
      border: 1px solid #2d3148;
      border-radius: 8px;
      padding: .75rem 1rem;
      margin-bottom: 1.25rem;
   }

   .required-files p {
      font-size: .75rem;
      color: #64748b;
      margin-bottom: .5rem;
      text-transform: uppercase;
      letter-spacing: .05em;
   }

   .required-files ul {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: .25rem;
   }

   .required-files li {
      font-size: .8rem;
      color: #94a3b8;
      display: flex;
      align-items: center;
      gap: .5rem;
   }

   .required-files li::before { content: '✔'; color: #22c55e; font-size: .7rem; }

   /* Submit */
   .btn {
      width: 100%;
      background: #6366f1;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: .75rem;
      font-size: .95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s;
      margin-top: .5rem;
   }

   .btn:hover { background: #4f46e5; }

   /* Error */
   .alert {
      border-radius: 8px;
      padding: .75rem 1rem;
      font-size: .875rem;
      margin-bottom: 1.25rem;
   }

   .alert-error { background: #2d1515; border: 1px solid #7f1d1d; color: #fca5a5; }

   /* Resultado */
   .result { margin-top: 1.5rem; }

   .result-title {
      font-size: .8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-bottom: .75rem;
   }

   .result-list { list-style: none; display: flex; flex-direction: column; gap: .4rem; }

   .result-list li {
      font-size: .8rem;
      font-family: 'Courier New', monospace;
      padding: .4rem .75rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: .5rem;
   }

   .li-created { background: #052e16; color: #86efac; }
   .li-skipped { background: #1c1917; color: #d6d3d1; }
   .li-error   { background: #2d1515; color: #fca5a5; }
</style>
</head>
<body>
<div class="card">
   <div class="card-header">
      <h1>⚙ Generador de Secciones</h1>
      <span>PHPost</span>
   </div>
   <div class="card-body">

      <?php if ($error): ?>
         <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">

         <div class="field">
            <label>Nombre de la sección</label>
            <input type="text"
                   name="name"
                   placeholder="fotos"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                   autocomplete="off"
                   required />
            <p class="hint">Solo minúsculas, números y guión bajo. Ej: <strong>fotos</strong>, <strong>galeria</strong>, <strong>top_posts</strong></p>
         </div>

         <div class="field">
            <label>Theme</label>
            <select name="theme">
               <?php foreach ($availThemes as $t): ?>
                  <option value="<?= htmlspecialchars($t) ?>"
                     <?= (($_POST['theme'] ?? 'default') === $t ? 'selected' : '') ?>>
                     <?= htmlspecialchars($t) ?>
                  </option>
               <?php endforeach; ?>
               <?php if (empty($availThemes)): ?>
                  <option value="default">default</option>
               <?php endif; ?>
            </select>
         </div>

         <div class="required-files">
            <p>Archivos obligatorios</p>
            <ul>
               <li>src/Php/{nombre}.php</li>
               <li>src/Class/c.{nombre}.php</li>
               <li>themes/{theme}/templates/t.{nombre}.tpl</li>
            </ul>
         </div>

         <div class="field">
            <label>Archivos opcionales</label>
            <div class="checks">
               <label class="check-item">
                  <input type="checkbox" name="api" value="1" <?= !empty($_POST['api']) ? 'checked' : '' ?> />
                  <span class="label-text">API</span>
                  <span class="badge">.php</span>
               </label>
               <label class="check-item">
                  <input type="checkbox" name="css" value="1" <?= !empty($_POST['css']) ? 'checked' : '' ?> />
                  <span class="label-text">CSS</span>
                  <span class="badge">.css</span>
               </label>
               <label class="check-item">
                  <input type="checkbox" name="js" value="1" <?= !empty($_POST['js']) ? 'checked' : '' ?> />
                  <span class="label-text">JS</span>
                  <span class="badge">.js</span>
               </label>
            </div>
         </div>

         <button type="submit" class="btn">Generar sección</button>
      </form>

      <?php if ($result): ?>
         <div class="result">
            <?php if ($result['created']): ?>
               <p class="result-title" style="color:#86efac">✔ Archivos creados</p>
               <ul class="result-list">
                  <?php foreach ($result['created'] as $f): ?>
                     <li class="li-created"><?= htmlspecialchars($f) ?></li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>

            <?php if ($result['skipped']): ?>
               <p class="result-title" style="color:#d6d3d1;margin-top:1rem">⚠ Ya existían (omitidos)</p>
               <ul class="result-list">
                  <?php foreach ($result['skipped'] as $f): ?>
                     <li class="li-skipped"><?= htmlspecialchars($f) ?></li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>

            <?php if ($result['errors']): ?>
               <p class="result-title" style="color:#fca5a5;margin-top:1rem">✘ Errores</p>
               <ul class="result-list">
                  <?php foreach ($result['errors'] as $e): ?>
                     <li class="li-error"><?= htmlspecialchars($e) ?></li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>
         </div>
      <?php endif; ?>

   </div>
</div>
</body>
</html>