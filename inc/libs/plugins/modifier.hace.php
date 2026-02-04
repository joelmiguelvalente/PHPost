<?php

/**
 * Smarty modifier
 *
 * Type:     modifier
 * Name:     hace
 * Date:     Ene 26, 2026
 *
 * Devuelve el tiempo transcurrido desde una fecha hasta el momento actual,
 * en formato humano (ej: "Hace 3 horas", "2 días").
 *
 * Ejemplos:
 *   {$fecha|hace}          -> 3 horas
 *   {$fecha|hace:true}    -> Hace 3 horas
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    int|null $timestamp Unix timestamp
 * @param    bool     $show      Si es true, antepone "Hace"
 *
 * @return   string
 */


function smarty_modifier_hace(?int $fecha = null, bool $show = false): string {
   if (!$fecha || $fecha > time()) {
      return 'Nunca';
   }
   $diff = time() - $fecha;
   if ($diff < 60) {
      return $show ? 'Hace instantes' : 'instantes';
   }
   $units = [
      31536000 => ['año', 'años'],
      2592000  => ['mes', 'meses'],
      604800   => ['semana', 'semanas'],
      86400    => ['día', 'días'],
      3600     => ['hora', 'horas'],
      60       => ['minuto', 'minutos'],
   ];
   foreach ($units as $seconds => [$singular, $plural]) {
      if ($diff >= $seconds) {
         $value = intdiv($diff, $seconds);
         $text = $value === 1 ? $singular : $plural;
         return $show ? "Hace $value $text" : "$value $text";
      }
   }
   return 'instantes';
}