<?php

/**
 * Smarty modifier plugin
 *
 * Type:     modifier
 * Name:     format
 * Purpose:  Formatea un string usando sprintf / vsprintf.
 * Example:  {"Hola %s, tenés %d mensajes"|format:$user:$count}
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    string $string Cadena de formato
 * @param    mixed  ...$args Valores a interpolar
 * @return   string Cadena formateada
 */

function smarty_modifier_format(string $string, ...$args): string {
   return vsprintf($string, $args);
}