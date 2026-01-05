<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     fecha<br>
 * Date:     Feb 24, 2010
 * Purpose:  catenate a value to a variable
 * Input:    string to catenate
 * Example:  {$var|fecha}
 * @link http://smarty.php.net/manual/en/language.modifier.cat.php cat
 *          (Smarty online manual)
 * @author   Ivan Molina Pavana
 * @version 1.0
 * @param string
 * @return string
 */

function smarty_modifier_fecha($fecha, $format = false){
	$meses = [
		1 => 'enero',
		2 => 'febrero',
		3 => 'marzo',
		4 => 'abril',
		5 => 'mayo',
		6 => 'junio',
		7 => 'julio',
		8 => 'agosto',
		9 => 'septiembre',
		10 => 'octubre',
		11 => 'noviembre',
		12 => 'diciembre'
	];
	$_dias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
	// FORMATO?
	if($format != false){
		// VARS
		$dia = date("d",$fecha);
		$mes = date("m",$fecha);
		$mes_int = date("n",$fecha);
      $ano_anterior = date("Y", strtotime("-1 year", $fecha));
		$ano = (date("Y", $fecha) === date('Y')) ? '' : $ano_anterior;
		// PARSE
		switch($format){
			// 20 de Abril de 2024
			case 'd_Ms_a':
				$e_ano = date("Y",time());
				$ano = ($e_ano == $ano) ? '' : (empty($ano) ? '' : " de $ano");
				$return = "El $dia de {$meses[$mes_int]}$ano";
			break;
		}
		// REGRESAMOS
		return $return;
	} else {
		$ahora = time();
		$tiempo = $ahora - $fecha;

		$horaminutos = date("H:i", $fecha);
		// Calculate the number of days, taking into account leap years
		$dias = round($tiempo / 86400) - (int)(($tiempo % 86400) / 86400) * (gmdate('L', $fecha) ? 1 : 0);
		// HOY
		if ($dias <= 0) {
		   // HACE MENOS DE 1 HORA
		   if (round($tiempo / 3600) <= 0) {
		      // HACE MENOS DE 1 MINUTO
		      if (round($tiempo / 60) <= 0) {
		         if ($tiempo <= 60) $hace = "Hace unos segundos";
		         // HACE X MINUTOS
		      } else {
		         $can = round($tiempo / 60);
		         $hace = "Hace $can minuto" . ($can <= 1 ? '' : 's');
		      }
		      // HACE X HORAS
		   } else {
		      $can = round($tiempo / 3600);
		      $hace = "Hace $can hora" . ($can <= 1 ? "" : "s");
		   }
		   // MENOS DE 7 DIAS
		} elseif ($dias <= 7) {
		   // AYER
		   if ($dias < 2) {
		      $hace = 'Ayer a las ' . $horaminutos;
		      // HACE MENOS DE 5 DIAS
		   } else {
		      $hace = 'El ' . $_dias[date("w", $fecha)] . ' a las ' . $horaminutos;
		   }
		   // HACE MAS DE UNA SEMANA
		} else {
		   $hace = "El " . date("d", $fecha) . " de " . $meses[date("n", $fecha)] . " a las $horaminutos";
		}

		return $hace;
	}
}