<?php

/**
 * Soporta múltiples formas
 * 
 * Modos de uso:
 * 1. Solo imagen: [image]url[/image]
 * 2. Con caption: [image=Caption]url[/image] o [image="Caption with spaces"]url[/image]
 * 3. Con dimensiones: [image width=200 height=500]url[/image]
 * 4. Con caption y dimensiones: [image=Caption width=200 height=500]url[/image] o [image="Caption with spaces" width=200 height=500]url[/image]
 * 5. Con otros atributos: [image alt="Texto alternativo" title="Título"]url[/image]
*/

require_once TS_UTILS . '/ImageProcessor.php';

class ImageDefinition extends JBBCode\CodeDefinition {

   private $Processor;
   private $postId;
   private $route;

   public function __construct(bool $withOption, string $route, int $postId = 0) {
      $this->parseContent = false;
      $this->useOption = $withOption;
      $this->setTagName('image');
      $this->nestLimit = -1;
      $this->postId = $postId;
      $this->route = $route;
      // Pasamos TS_STORAGE . '/media/' como base
      $this->Processor = new ImageProcessor([
         'storage_path' => TS_STORAGE . '/media/',
         'type' => 'posts',
         'id' => (int)$postId,
      ]);
   }

   public function asHtml(JBBCode\ElementNode $el) {
      $storageBase = $this->route . '/storage';
      $src = trim($el->getAsText());

      // Si la URL es remota, intentamos procesarla
      if ($this->postId && filter_var($src, FILTER_VALIDATE_URL) && !str_contains(parse_url($src, PHP_URL_HOST), $_SERVER['HTTP_HOST'])) {
         try {
            $localPath = $this->Processor->process($src);
         } catch (\Exception $e) {
            Logger::warning("JBBCode Image: " . $e->getMessage(), [
               'ImageProcessor' => 'Imagen no procesada',
               'From' => 'ImageDefinition'
            ]);
            return "<img src='{$src}' alt='Imagen no procesada' class='bbcode-image'>";
         }
         // Convertimos la ruta absoluta local en una URL relativa para la web
         $fileName = basename($localPath);
         $src = $this->route . $this->Processor->getPublicUrl($fileName);
      }
      $option = $el->getAttribute();
      $attrs = [
         'src' => htmlspecialchars($src, ENT_QUOTES),
         'loading' => 'lazy',
         'decoding' => 'async'
      ];

      $defaultAllow = ['width','height','alt','title'];

      if (is_array($option)) {
         // Procesar el array resultante del parsing
         $processedOptions = [];
         
         foreach ($option as $key => $value) {
             // Verificar si la clave contiene atributos key=value dentro de ella
             if (preg_match('/^(.+?)\s+(\w+=.+)/', $key, $matches)) {
                 // Extraer el caption y procesar los atributos embebidos
                 $caption = $matches[1];
                 $embeddedAttrs = $matches[2];
                 
                 // Agregar el caption
                 $attrs['alt'] = htmlspecialchars($caption, ENT_QUOTES);
                 
                 // Procesar los atributos embebidos (como "width=500")
                 preg_match_all('/(\w+)=(".*?"|\S+)/', $embeddedAttrs, $attrMatches);
                 foreach ($attrMatches[1] as $i => $attr) {
                     $val = trim($attrMatches[2][$i], '"');
                     if (in_array($attr, $defaultAllow)) {
                         $attrs[$attr] = htmlspecialchars($val, ENT_QUOTES);
                     }
                 }
                 
                 // El valor del último par clave-valor también podría contener atributos
                 if (preg_match_all('/(\w+)=(".*?"|\S+)/', $value, $valMatches)) {
                     foreach ($valMatches[1] as $i => $attr) {
                         $val = trim($valMatches[2][$i], '"');
                         if (in_array($attr, $defaultAllow)) {
                             $attrs[$attr] = htmlspecialchars($val, ENT_QUOTES);
                         }
                     }
                 }
             } else {
                 // Si es un atributo simple como 'width' => '500'
                 if (in_array($key, $defaultAllow)) {
                     $attrs[$key] = htmlspecialchars($value, ENT_QUOTES);
                 } else {
                     // Si no es un atributo permitido, probablemente es el caption
                     $attrs['alt'] = htmlspecialchars($value, ENT_QUOTES);
                 }
             }
         }
      } elseif (is_string($option)) {
          // Mantener el procesamiento original para strings
          $option = trim($option);
          
          // Buscamos si hay atributos key=value en la cadena
          $hasAttributes = preg_match_all('/(\w+)=(".*?"|\S+)/', $option, $attrMatches);
          
          if ($hasAttributes) {
              // Extraemos la parte del caption (todo antes de los atributos)
              $attrPositions = [];
              foreach ($attrMatches[0] as $attrMatch) {
                  $pos = strpos($option, $attrMatch);
                  $attrPositions[] = $pos;
              }
              
              if (!empty($attrPositions)) {
                  $firstAttrPos = min($attrPositions);
                  
                  // El caption es todo antes del primer atributo
                  $caption = trim(substr($option, 0, $firstAttrPos));
                  
                  // Agregar caption como alt si existe
                  if (!empty($caption)) {
                      $attrs['alt'] = htmlspecialchars($caption, ENT_QUOTES);
                  }
                  
                  // Procesar los atributos
                  foreach ($attrMatches[1] as $i => $attr) {
                      $value = trim($attrMatches[2][$i], '"');
                      if (in_array($attr, $defaultAllow)) {
                          $attrs[$attr] = htmlspecialchars($value, ENT_QUOTES);
                      }
                  }
              }
          } else {
              // Si no hay atributos key=value, toda la cadena es el caption
              if (!empty($option)) {
                  $attrs['alt'] = htmlspecialchars($option, ENT_QUOTES);
              }
          }
      }

      // Generar las rutas para los diferentes formatos
      $pathInfo = pathinfo($src);
      $basePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
      $originalExtension = $pathInfo['extension'] ?? '';
      
      // Formatos alternativos
      $webpSrc = $basePath . '.webp';
      $avifSrc = $basePath . '.avif';
      $pngSrc = $basePath . '.png';

      // Verificar si existen los archivos alternativos antes de incluirlos
      $webpExists = file_exists(str_replace($storageBase, TS_STORAGE, $webpSrc));
      $avifExists = file_exists(str_replace($storageBase, TS_STORAGE, $avifSrc));
      $pngExists = file_exists(str_replace($storageBase, TS_STORAGE, $pngSrc));

      $figure_attr = [];
      $image_attr = [];
      foreach ($attrs as $attr => $value) {
         if(in_array($attr, ['width','height'], true)) {
            $figure_attr[] = "{$attr}:{$value}px";
         } else {
            if ($attr !== 'src') {
               $image_attr[] = "$attr=\"$value\"";
            }
         }
      }
      $fig = implode(';', $figure_attr);
      $attribute = implode(' ', $image_attr);

      if ($avifExists || $webpExists || $pngExists) {
         // Generar el HTML con picture para soportar múltiples formatos
         $picture_html = "<figure data-by=\"PHPost\" class=\"bbc-figure\" style=\"$fig\">";
         $picture_html .= "<picture>";
         
         if ($avifExists) {
            $picture_html .= "<source srcset=\"" . htmlspecialchars($avifSrc, ENT_QUOTES) . "\" type=\"image/avif\" />";
         }
         if ($webpExists) {
            $picture_html .= "<source srcset=\"" . htmlspecialchars($webpSrc, ENT_QUOTES) . "\" type=\"image/webp\" />";
         }
         // Imagen original como fallback
         $picture_html .= "<img " . $attribute . " src=\"" . htmlspecialchars($pngSrc, ENT_QUOTES) . "\" />";
         $picture_html .= "</picture>";
         $picture_html .= "</figure>";
         return $picture_html;
      } else {
         // Si no hay formatos alternativos, usar el HTML original
         $html = "<figure data-by=\"PHPost\" class=\"bbc-figure\" style=\"$fig\"><img $attribute src=\"" . htmlspecialchars($src, ENT_QUOTES) . "\" /></figure>";
         return $html;
      }
   }
}