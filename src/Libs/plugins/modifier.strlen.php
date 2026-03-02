<?php

/**
 * Smarty modifier plugin
 *
 * Type:     modifier
 * Name:     strlen
 * Purpose:  Devuelve la longitud de un string en UTF-8.
 * Example:  {$string|strlen}
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    string $string Texto de entrada
 * @return   int Cantidad de caracteres
 */

function smarty_modifier_strlen(string $string): int {
   return mb_strlen($string, 'UTF-8');
}