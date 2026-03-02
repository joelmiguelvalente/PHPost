<?php

/**
 * Smarty modifier plugin
 *
 * Type:     modifier
 * Name:     chop
 * Purpose:  Elimina el último carácter de un string (UTF-8 safe).
 * Example:  {$string|chop}
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    string $string Texto de entrada
 * @return   string Texto sin el último carácter
 */

function smarty_modifier_chop(string $string = '', $chars = -1): string {
	return mb_substr($string, 0, $chars, 'UTF-8');
}