<?php

function smarty_function_load($params, Smarty\Template $template) {
   $scope = $template->getTemplateVars() ?? [];
   $routes = $scope['tsRoutes'];

   $type = $params['type'] ?? null;
   if (!in_array($type, ['css', 'js'], true)) {
      return '';
   }

	$checkFile = function (string $filename, string $type) use ($routes) {
      $basePath = TS_THEMES . TS_TEMA;
      $assetPath = TS_ASSETS;
      $file     = "$filename.$type";
      if (file_exists("$basePath/$file")) { // Base
         return "{$routes['tema']['base']}/$file";
      }
      if (file_exists("$basePath/$type/$file")) { // Folder
         return "{$routes['tema'][$type]}/$file";
      }
      if (file_exists("$assetPath/$type/$file")) { // Folder
         return "{$routes['assets'][$type]}/$file";
      }
      return null;
   };

   $files  = is_array($params['file']) ? $params['file'] : [$params['file']];
   $output = '';

   if ($params['type'] === 'js' && in_array('cuenta', $files, true)) {
      $deps = ['croppr.min', 'upload.avatar'];
      $ordered = [];
      foreach ($files as $file) {
         if ($file === 'cuenta') {
            // insertar dependencias ANTES
            foreach ($deps as $dep) {
               if (!in_array($dep, $files, true)) {
                  $ordered[] = $dep;
               }
            }
         }
         $ordered[] = $file;
      }
      $files = $ordered;
   }
   if (in_array('agregar', $files, true)) {
      $files = [...$files, 'wysibb'];
   }


	foreach ($files as $filename) {
      if (!is_string($filename)) {
         continue;
      }

      $file = $checkFile($filename, $params['type']);
      if (!$file) {
         continue;
      }

      if ($type === 'css') {
         $output .= '<link href="' . $file . '" rel="stylesheet" type="text/css">' . PHP_EOL;
      }

      if ($type === 'js' && $filename !== 'moderacion') {
         $output .= '<script src="' . $file . '" defer></script>' . PHP_EOL;
      }
   }

   return trim($output);
}