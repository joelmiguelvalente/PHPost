<?php

namespace JBBCode\validators;
require_once TS_LIBS . '/JBBCode/InputValidator.php';

/**
 * An InputValidator for CSS color values. This is a very rudimentary
 * validator. It will allow a lot of color values that are invalid. However,
 * it shouldn't allow any invalid color values that are also a security
 * concern.
 *
 * @author jbowens
 * @since May 2013
 * @update Miguel92 - 2026
 */
class CssColorValidator implements \JBBCode\InputValidator
{

    /**
     * Returns true if $input uses only valid CSS color value
     * characters.
     *
     * @param string $input  the string to validate
     * @return boolean
     */
    public function validate($input): string {
        $trimmed = trim($input);
        // Patrones comunes de colores CSS
        $patterns = [
            '/^#[a-fA-F0-9]{3,8}$/',           // Hex: #fff, #ffffff, #ff00aa77
            '/^[a-zA-Z]+$/',                   // Nombre: red, blue, etc
            '/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/', // rgb(255, 0, 0)
            '/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(?:\d+\.?\d*|\.\d+)\s*\)$/', // rgba(255, 0, 0, 0.5)
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $trimmed)) {
                return true;
            }
        }
        return false;
    }
}
