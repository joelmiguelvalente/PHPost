<?php 

/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier
 * Name:     kmg
 * Date:     Abril 25, 2014
 * Purpose:  Se utiliza para verificar si un valor dado existe en un array.
 * Example:  {if $value|in_array:$array}El valor existe.{else}El valor no existe.{/if}
 * @author   Miguel92
 * @version 1.0
 * @param int
 * @return bool
*/

function smarty_modifier_in_array($array, $buscar) {
	if (!is_array($array)) {
		trigger_error('The second argument passed to the in_array modifier must be an array', E_USER_ERROR);
	}
	return in_array($buscar, $array);
}