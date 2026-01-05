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
      $file     = "$filename.$type";
      if (file_exists("$basePath/$file")) { // Base
         return "{$routes['tema']['base']}/$file";
      }
      if (file_exists("$basePath/$type/$file")) { // Folder
         return "{$routes['tema'][$type]}/$file";
      }
      return null;
   };

   $files  = is_array($params['file']) ? $params['file'] : [$params['file']];
   $output = '';

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