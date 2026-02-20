<?php

namespace JBBCode\validators;
require_once TS_LIBS . '/JBBCode/InputValidator.php';
/**
 * Validador de fuentes para evitar malformaciones en la pagina
 *
 * @author Alan
 * @since Sep 2016
 * @update Miguel92 - 2026
 */

class FontValidator implements \JBBCode\InputValidator {

    /**
     * Retorna true si $input es alfabético
     *
     * @param $input string a validar
     */
    public function validate($input) {
        return preg_match('/^[a-zA-Z0-9\s\-_",.()]+$/',$input);
    }

}