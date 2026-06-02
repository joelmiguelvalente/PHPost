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

    /** @var array Lista de atributos HTML permitidos */
    private const ALLOWED_ATTRIBUTES = ['width', 'height', 'alt', 'title'];

    public function __construct(bool $withOption, string $route, int $postId = 0) {
        $this->parseContent = false;
        $this->useOption = $withOption;
        $this->setTagName('image');
        $this->nestLimit = -1;
        $this->postId = $postId;
        $this->route = $route;
        $this->Processor = new ImageProcessor([
            'storage_path' => TS_STORAGE . '/media/',
            'type' => 'posts',
            'id' => (int)$postId,
        ]);
    }

    public function asHtml(JBBCode\ElementNode $el): string {
        $src = $this->getImageSource($el);
        $processedSrc = $this->processRemoteImage($src);

        if ($processedSrc === null) {
            return $this->getFallbackHtml($src);
        }

        $attributes = $this->parseAttributes($el);
        $attributes['src'] = htmlspecialchars($processedSrc, ENT_QUOTES);

        $pictureHtml = $this->buildPictureHtml($processedSrc, $attributes);

        return $this->wrapInFigure($pictureHtml, $attributes);
    }

    /**
     * Obtiene la URL de la imagen del contenido del elemento
     */
    private function getImageSource(JBBCode\ElementNode $el): string {
        return trim($el->getAsText());
    }

    /**
     * Procesa imágenes remotas y devuelve la ruta local o null si falla
     */
    private function processRemoteImage(string $src): ?string {
        // Si no hay postId o no es URL remota, devolver la original
        if (!$this->postId || !filter_var($src, FILTER_VALIDATE_URL)) {
            return $src;
        }

        // Verificar si es externo al dominio actual
        $host = parse_url($src, PHP_URL_HOST);
        if (str_contains($host, $_SERVER['HTTP_HOST'])) {
            return $src;
        }

        try {
            $localPath = $this->Processor->process($src);
            $fileName = basename($localPath);
            return $this->route . $this->Processor->getPublicUrl($fileName);
        } catch (\Exception $e) {
            Logger::warning("JBBCode Image: " . $e->getMessage(), [
                'ImageProcessor' => 'Imagen no procesada',
                'From' => 'ImageDefinition'
            ]);
            return null;
        }
    }

    /**
     * HTML de fallback cuando la imagen no puede procesarse
     */
    private function getFallbackHtml(string $src): string {
        $safeSrc = htmlspecialchars($src, ENT_QUOTES);
        return "<img src='{$safeSrc}' alt='Imagen no procesada' class='bbcode-image'>";
    }

    /**
     * Parsea los atributos del elemento BBCode
     */
    private function parseAttributes(JBBCode\ElementNode $el): array {
        $option = $el->getAttribute();
        $attrs = [
            'loading' => 'lazy',
            'decoding' => 'async'
        ];

        if (is_array($option)) {
            $attrs = array_merge($attrs, $this->parseArrayAttributes($option));
        } elseif (is_string($option)) {
            $attrs = array_merge($attrs, $this->parseStringAttributes($option));
        }

        return $attrs;
    }

    /**
     * Parsea atributos cuando el option es un array
     */
    private function parseArrayAttributes(array $option): array {
        $attrs = [];

        foreach ($option as $key => $value) {
            // Verificar si la clave contiene atributos key=value embebidos
            if (preg_match('/^(.+?)\s+(\w+=.+)/', $key, $matches)) {
                $caption = $matches[1];
                $attrs['alt'] = htmlspecialchars($caption, ENT_QUOTES);

                // Procesar atributos embebidos
                $embeddedAttrs = $this->extractKeyValuePairs($matches[2]);
                $attrs = array_merge($attrs, $this->filterAllowedAttributes($embeddedAttrs));

                // También procesar el valor por si contiene atributos
                $valueAttrs = $this->extractKeyValuePairs($value);
                $attrs = array_merge($attrs, $this->filterAllowedAttributes($valueAttrs));
            } else {
                // Atributo simple como 'width' => '500'
                if (in_array($key, self::ALLOWED_ATTRIBUTES)) {
                    $attrs[$key] = htmlspecialchars($value, ENT_QUOTES);
                } else {
                    // Si no es atributo permitido, es el caption
                    $attrs['alt'] = htmlspecialchars($value, ENT_QUOTES);
                }
            }
        }

        return $attrs;
    }

    /**
     * Parsea atributos cuando el option es un string
     */
    private function parseStringAttributes(string $option): array {
        $attrs = [];
        $option = trim($option);

        $hasAttributes = preg_match_all('/(\w+)=(".*?"|\S+)/', $option, $attrMatches);

        if ($hasAttributes) {
            // Encontrar la posición del primer atributo
            $attrPositions = [];
            foreach ($attrMatches[0] as $attrMatch) {
                $pos = strpos($option, $attrMatch);
                if ($pos !== false) {
                    $attrPositions[] = $pos;
                }
            }

            if (!empty($attrPositions)) {
                $firstAttrPos = min($attrPositions);
                $caption = trim(substr($option, 0, $firstAttrPos));

                if (!empty($caption)) {
                    $attrs['alt'] = htmlspecialchars($caption, ENT_QUOTES);
                }
            }

            // Procesar los atributos
            $pairs = $this->extractKeyValuePairsFromMatches($attrMatches);
            $attrs = array_merge($attrs, $this->filterAllowedAttributes($pairs));
        } else {
            // Sin atributos, toda la cadena es el caption
            if (!empty($option)) {
                $attrs['alt'] = htmlspecialchars($option, ENT_QUOTES);
            }
        }

        return $attrs;
    }

    /**
     * Extrae pares clave=valor de una cadena
     */
    private function extractKeyValuePairs(string $string): array {
        $pairs = [];
        preg_match_all('/(\w+)=(".*?"|\S+)/', $string, $matches);
        return $this->extractKeyValuePairsFromMatches($matches);
    }

    /**
     * Convierte los matches de preg_match_all en un array clave => valor
     */
    private function extractKeyValuePairsFromMatches(array $matches): array {
        $pairs = [];
        foreach ($matches[1] as $i => $key) {
            $value = trim($matches[2][$i], '"');
            $pairs[$key] = $value;
        }
        return $pairs;
    }

    /**
     * Filtra solo los atributos permitidos
     */
    private function filterAllowedAttributes(array $attrs): array {
        $filtered = [];
        foreach ($attrs as $key => $value) {
            if (in_array($key, self::ALLOWED_ATTRIBUTES)) {
                $filtered[$key] = htmlspecialchars($value, ENT_QUOTES);
            }
        }
        return $filtered;
    }

    /**
     * Construye el HTML con picture para múltiples formatos de imagen
     */
    private function buildPictureHtml(string $src, array $attrs): string {
        $storageBase = $this->route . '/storage';
        $pathInfo = pathinfo($src);
        $basePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'];

        $webpSrc = $basePath . '.webp';
        $avifSrc = $basePath . '.avif';
        $pngSrc = $basePath . '.png';

        // Verificar si existen los archivos alternativos
        $webpExists = file_exists(str_replace($storageBase, TS_STORAGE, $webpSrc));
        $avifExists = file_exists(str_replace($storageBase, TS_STORAGE, $avifSrc));
        $pngExists = file_exists(str_replace($storageBase, TS_STORAGE, $pngSrc));

        $imgAttr = $this->buildImageAttributes($attrs);

        if (!$avifExists && !$webpExists && !$pngExists) {
            return "<img " . $imgAttr . " src=\"" . htmlspecialchars($src, ENT_QUOTES) . "\" />";
        }

        $picture = "<picture>";

        if ($avifExists) {
            $picture .= "<source srcset=\"" . htmlspecialchars($avifSrc, ENT_QUOTES) . "\" type=\"image/avif\" />";
        }
        if ($webpExists) {
            $picture .= "<source srcset=\"" . htmlspecialchars($webpSrc, ENT_QUOTES) . "\" type=\"image/webp\" />";
        }

        // Imagen PNG como fallback
        $picture .= "<img " . $imgAttr . " src=\"" . htmlspecialchars($pngSrc, ENT_QUOTES) . "\" />";
        $picture .= "</picture>";

        return $picture;
    }

    /**
     * Construye los atributos de la imagen HTML
     */
    private function buildImageAttributes(array $attrs): string {
        $parts = [];

        foreach ($attrs as $attr => $value) {
            if ($attr !== 'src') {
                $parts[] = "{$attr}=\"{$value}\"";
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Construye el HTML del figure con estilos
     */
    private function buildFigureStyles(array $attrs): string {
        $styles = [];

        if (isset($attrs['width'])) {
            $styles[] = "width:{$attrs['width']}px";
        }
        if (isset($attrs['height'])) {
            $styles[] = "height:{$attrs['height']}px";
        }

        return implode(';', $styles);
    }

    /**
     * Envuelve el contenido en un figure
     */
    private function wrapInFigure(string $content, array $attrs): string {
        $style = $this->buildFigureStyles($attrs);
        $styleAttr = !empty($style) ? "style=\"{$style}\"" : '';

        return "<figure data-by=\"PHPost\" class=\"bbc-figure\" {$styleAttr}>{$content}</figure>";
    }
}
