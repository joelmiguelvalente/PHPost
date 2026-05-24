<?php

declare(strict_types=1);

/**
 * @package    PHPost/Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 *
 * @note Desarrollado con asistencia de Claude (Anthropic)
 */


require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

$ctx = Controller::page('buscador')->everybody();
$ctx->exportLegacy();

$tsLevelMsg = $tsCore->setLevel($ctx->getLevel(), true);
if (is_array($tsLevelMsg)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", $tsLevelMsg);
   $ctx->exportLegacy();
}

if ($ctx->continue()) {

   $query    = trim($_GET['query']    ?? '');
   $engine   = trim($_GET['engine']   ?? 'web');
   $category = (int)($_GET['category'] ?? 0);
   $autor    = trim($_GET['autor']    ?? '');

   require_once TS_CLASS . "/c.buscador.php";
   $tsBuscador = new tsBuscador($tsCore, $tsUser);

   if ($engine !== 'google') {

      // Conteos para los badges de pestañas (siempre, salvo búsqueda vacía)
      $smarty->assign("tsCounts", $tsBuscador->getCounts());

      // Resultados según engine activo
      switch ($engine) {
         case 'usuarios':
            $smarty->assign("tsResults", $tsBuscador->getUsuarios());
            break;
         case 'fotos':
            $smarty->assign("tsResults", $tsBuscador->getFotos());
            break;
         case 'muro':
            $smarty->assign("tsResults", $tsBuscador->getMuro());
            break;
         case 'tags':
            $smarty->assign("tsResults", $tsBuscador->getTags());
            break;
         case 'web':
         default:
            $smarty->assign("tsResults", $tsBuscador->getQuery());
            break;
      }
   }

   $smarty->assign("tsQuery",    $query);
   $smarty->assign("tsEngine",   $engine);
   $smarty->assign("tsCategory", $category);
   $smarty->assign("tsAutor",    $autor);
}

if ($tsAjax) {
   $smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
