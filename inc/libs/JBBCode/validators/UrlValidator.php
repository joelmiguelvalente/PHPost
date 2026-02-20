<?php

namespace JBBCode\validators;
require_once TS_LIBS . '/JBBCode/InputValidator.php';
/**
 * An InputValidator for urls. This can be used to make [url] bbcodes secure.
 *
 * @author jbowens
 * @since May 2013
 * @update Miguel92 - 2026
 */
class UrlValidator implements \JBBCode\InputValidator
{

    /**
     * Returns true iff $input is a valid url.
     *
     * @param string $input  the string to validate
     * @return boolean
     */
    public function validate($input): bool {
        $url = filter_var($input, FILTER_VALIDATE_URL);
        if (!$url) return false;

        // Permitir solo HTTP/HTTPS
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array(strtolower($scheme), ['http', 'https']);
    }
}
