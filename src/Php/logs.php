<?php

/**
 * @name logs.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

$ctx = Controller::page('logs')->admin();
$ctx->exportLegacy();

$tsLevelMsg = $tsCore->setLevel($ctx->getLevel(), true);
if (is_array($tsLevelMsg)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", $tsLevelMsg);
   $ctx->exportLegacy();
}

if (!$tsUser->is_member) {
   header("Location: {$tsCore->settings['url']}");
}

if ($ctx->continue()) {

   require_once TS_LOGGER . '/LogParser.php';

   $logsDir  = Config::app('paths.logs');
   $logFiles = LogParser::getAvailableFiles($logsDir);
   $filter   = strtoupper($_GET['level'] ?? 'ALL');

   // Determinar archivo activo
   $activeKey  = $_GET['file'] ?? (isset($logFiles[0]) ? $logFiles[0]['date'] . '_' . $logFiles[0]['channel'] : null);
   $activeFile = null;
   $entries    = [];

   foreach ($logFiles as $f) {
      if ($f['date'] . '_' . $f['channel'] === $activeKey) {
         $activeFile             = $f;
         $activeFile['filename'] = basename($f['path']);
         $entries                = LogParser::parseFile($f['path']);
         break;
      }
   }

   // Filtrar por nivel
   if ($filter !== 'ALL') {
      $entries = array_values(array_filter($entries, fn($e) => $e['level'] === $filter));
   }

   // ── Pre-procesar logFiles para Smarty
   $activeKeyStr = $activeFile ? $activeFile['date'] . '_' . $activeFile['channel'] : '';
   foreach ($logFiles as &$f) {
      $f['key']      = $f['date'] . '_' . $f['channel'];
      $f['isActive'] = $f['key'] === $activeKeyStr;
      $f['sizeStr']  = $f['size'] < 1024 ? $f['size'] . 'B' : round($f['size'] / 1024, 1) . 'KB';
      $f['url'] = '?file=' . urlencode($f['key']) . '&level=' . urlencode($filter);
   }
   unset($f);

   // ── Metadatos de niveles
   $levelMeta = [
      'FATAL'     => ['color' => '#ff2d55', 'bg' => '#2a0a10', 'icon' => '💀'],
      'EXCEPTION' => ['color' => '#ff6b35', 'bg' => '#2a1200', 'icon' => '🔥'],
      'ERROR'     => ['color' => '#ff453a', 'bg' => '#25090a', 'icon' => '✖'],
      'WARNING'   => ['color' => '#ffd60a', 'bg' => '#252000', 'icon' => '⚠'],
      'INFO'      => ['color' => '#30d158', 'bg' => '#0a2010', 'icon' => 'ℹ'],
      'DEBUG'     => ['color' => '#64d2ff', 'bg' => '#001a25', 'icon' => '⚙'],
   ];

   // ── Pre-procesar levels para Smarty
   $levelsData = [];
   $allLevels  = ['ALL', 'FATAL', 'EXCEPTION', 'ERROR', 'WARNING', 'INFO', 'DEBUG'];

   // Total antes de filtrar (necesitamos contar sobre todas las entradas)
   $allEntries = LogParser::parseFile($activeFile['path'] ?? '');
   foreach ($allLevels as $l) {
      $meta  = $levelMeta[$l] ?? ['color' => '#888', 'bg' => '#111', 'icon' => '·'];
      $count = $l === 'ALL' ? count($allEntries) : count(array_filter($allEntries, fn($e) => $e['level'] === $l));

      $levelsData[] = [
         'name'     => $l,
         'color'    => $meta['color'],
         'bg'       => $meta['bg'],
         'icon'     => $meta['icon'],
         'isAll'    => $l === 'ALL',
         'isActive' => $filter === $l,
         'count'    => $count,
         'url'      => '?file=' . urlencode($activeKeyStr) . '&level=' . urlencode($l),
      ];
   }

   // ── Pre-procesar entries para Smarty
   foreach ($entries as $i => &$e) {
      $meta             = $levelMeta[$e['level']] ?? ['color' => '#888', 'bg' => '#1a1a1a', 'icon' => '·'];
      $e['id']          = $i;
      $e['color']       = $meta['color'];
      $e['bg']          = $meta['bg'];
      $e['icon']        = $meta['icon'];
      $e['border']      = $meta['color'] . '22';
      // Para el botón Arreglado — identifica el bloque en el archivo
      $e['rawDatetime'] = $e['datetime'];


      // Pre-parsear stack trace en líneas estructuradas
      $e['traceLines'] = [];
      if (!empty($e['trace'])) {
         foreach (array_filter(explode("\n", $e['trace'])) as $tl) {
            if (preg_match('/^(#\d+)\s+(.*?)\((\d+)\):\s*(.+)$/', trim($tl), $tm)) {
               $e['traceLines'][] = [
                  'parsed' => true,
                  'num'    => $tm[1],
                  'path'   => $tm[2],
                  'line'   => $tm[3],
                  'fn'     => $tm[4],
               ];
            } else {
               $e['traceLines'][] = [
                  'parsed' => false,
                  'raw'    => trim($tl),
               ];
            }
         }
      }
   }
   unset($e);

   // ── Datos del toolbar
   $entryCount   = count($entries);
   $entryLabel   = $entryCount !== 1 ? 'entradas' : 'entrada';
   $filterActive = $filter !== 'ALL';

   // ── Asignar a Smarty
   $smarty->assign("logFiles",    $logFiles);
   $smarty->assign("levelsData",  $levelsData);
   $smarty->assign("entries",     $entries);
   $smarty->assign("activeFile",  $activeFile);
   $smarty->assign("filter",      $filter);
   $smarty->assign("entryCount",  $entryCount);
   $smarty->assign("entryLabel",  $entryLabel);
   $smarty->assign("filterActive",$filterActive);
}

if ($tsAjax) {
   $smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}