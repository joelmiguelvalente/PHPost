<?php

/**
 * Smarty plugin: load
 *
 * Carga assets CSS o JS resolviendo rutas y dependencias según el tema activo
 * y el sistema de assets global.
 *
 * Uso:
 *   {load type="css" file="main"}
 *   {load type="js" file=["cuenta","perfil"]}
 *
 * Parámetros:
 * @param array<string,mixed> $params {
 *     @type string          $type  Tipo de asset: "css" o "js" (obligatorio)
 *     @type string|array    $file  Nombre o lista de archivos sin extensión (obligatorio)
 * }
 * @param Smarty\Template $template Instancia del template actual
 *
 * @return string HTML <link> o <script> generado
 *
 * @throws RuntimeException Si tsRoutes no está definido o es inválido
 *
 * @author Miguel92
 * @version 2.1
 */
function smarty_function_load(array $params, Smarty\Template $template): string {

   if (empty($params['type']) || empty($params['file']) || !in_array($params['type'], ['css', 'js'], true)) {
      throw new InvalidArgumentException('smarty_function_load: parámetros inválidos');
   }

   $routes = $template->getTemplateVars('tsRoutes');
   if (!is_array($routes)) {
      throw new RuntimeException('smarty_function_load: tsRoutes no está definido o es inválido');
   }

   $type = $params['type'] ?? null;
   if (!in_array($type, ['css', 'js'], true)) {
      return '';
   }

	$checkFile = function (string $name, string $type) use ($routes) {
      $file = "$name.$type";
      $locations = [
         [
            'path' => TS_THEMES . '/' . TS_TEMA,
            'url'  => $routes['tema']['base'],
         ],
         [
            'path' => TS_THEMES . '/' . TS_TEMA . "/$type",
            'url'  => $routes['tema'][$type],
         ],
         [
            'path' => TS_ASSETS . "/$type",
            'url'  => $routes['assets'][$type],
         ],
      ];

      foreach ($locations as $loc) {
         if (is_file($loc['path'] . '/' . $file)) {
            return $loc['url'] . '/' . $file;
         }
      }

      return null;
   };

   $files  = is_array($params['file']) ? $params['file'] : [$params['file']];
   $output = '';

   $dependencyMap = [
      'cuenta'  => ['croppr.min', 'upload.avatar'],
      'agregar' => ['wysibb'],
   ];

   if ($params['type'] === 'js') {
      $ordered = [];

      foreach ($files as $file) {
         if (isset($dependencyMap[$file])) {
            foreach ($dependencyMap[$file] as $dep) {
               if (!in_array($dep, $files, true) && !in_array($dep, $ordered, true)) {
                  $ordered[] = $dep;
               }
            }
         }

         $ordered[] = $file;
      }

      $files = $ordered;
   }
   
   if ($params['type'] === 'css' && in_array('agregar', $files, true)) {
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