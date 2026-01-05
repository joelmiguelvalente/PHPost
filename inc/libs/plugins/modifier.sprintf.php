<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


function smarty_modifier_sprintf($string, $data1, $data2){

	$string = str_replace(['{1}', '{2}'], [$data1, $data2], $string);

	return $string;
}