<?php

namespace JBBCode\validators;
require_once TS_LIBS . '/JBBCode/InputValidator.php';
/**
 * Un InputValidator para valores de alineación de texto válidos
 *
 * @author Kmario19
 * @since Jul 2015
 * @update Miguel92 - 2026
 */
class AlignValidator implements \JBBCode\InputValidator {

    private const VALID_VALUES = [
        'left'   => true,
        'center' => true,
        'justify' => true,
        'right'  => true,
    ];

    /**
     * Validates text alignment values.
     *
     * @param string $input The alignment value to validate
     * @return bool
     */
    public function validate(string $input): bool {
        return isset(self::VALID_VALUES[$input]);
    }

}
