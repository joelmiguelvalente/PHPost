<?php

/**
 * Smarty modifier plugin
 *
 * Type:     modifier
 * Name:     html_decode
 * Purpose:  Decodifica entidades HTML a caracteres reales (UTF-8).
 * Example:  {$string|html_decode}
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    string $string Texto con entidades HTML
 * @return   string Texto decodificado
 */

function smarty_modifier_html_decode(string $string = ''): string {
	return html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}