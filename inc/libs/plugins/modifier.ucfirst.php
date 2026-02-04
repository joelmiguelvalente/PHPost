<?php 

/**
 * Smarty modifier plugin
 *
 * Type:     modifier
 * Name:     ucfirst
 * Purpose:  Convierte la primera letra del string a mayúscula (UTF-8 safe).
 * Example:  {$string|ucfirst}
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    string $string Texto de entrada
 * @return   string Texto con la primera letra en mayúscula
 */

function smarty_modifier_ucfirst(string $string): string {
   $first = mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8');
   return $first . mb_substr($string, 1, null, 'UTF-8');
}