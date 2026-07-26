<?php

declare(strict_types=1);

/**
 * @package    Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 *
 * @note Desarrollado con asistencia de Claude (Anthropic, solo buscador)
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

$ctx = Controller::init('buscador', 'everybody');

if ($ctx->continue()) {

   $query    = trim($_GET['query']    ?? '');
   $engine   = trim($_GET['engine']   ?? 'web');
   $category = (int)($_GET['category'] ?? 0);
   $autor    = trim($_GET['autor']    ?? '');

   $tsBuscador = Container::get(tsBuscador::class);

   if ($engine !== 'google') {
      // Conteos para los badges de pestañas (siempre, salvo búsqueda vacía)
      $smarty->assign("tsCounts", $tsBuscador->getCounts());
      // Resultados según engine activo
      $results = match ($engine) {
         'usuarios' => $tsBuscador->getUsuarios(),
         'fotos' => $tsBuscador->getFotos(),
         'muro' => $tsBuscador->getMuro(),
         'tags' => $tsBuscador->getTags(),
         default => $tsBuscador->getQuery()
      };
      $smarty->assign("tsResults",  $results);
   }

   $smarty->assign("tsQuery",    $query);
   $smarty->assign("tsEngine",   $engine);
   $smarty->assign("tsCategory", $category);
   $smarty->assign("tsAutor",    $autor);
}

Controller::render($tsAjax, $tsTitle, $tsPage);
