<?php 

/**
 * Smarty modifier: in_array
 *
 * Verifica si un valor existe dentro de un array.
 *
 * Uso:
 *   {if $value|in_array:$array}
 *
 * @param mixed $needle   Valor a buscar
 * @param array $haystack Array donde buscar
 *
 * @return bool
 *
 * @author Miguel92
 * @version 2.0
 */

function smarty_modifier_in_array(mixed $needle, array $haystack): bool {
	if (!is_array($haystack)) {
	   throw new InvalidArgumentException(
	      'smarty_modifier_in_array: el segundo parámetro debe ser un array'
	   );
	}
   return in_array($needle, $haystack, true);
}