<?php

/**
 * Smarty modifier: number_abbr
 *
 * Convierte números grandes a formato abreviado.
 * Ej: 10000 => 10K, 1500000 => 1.5M
 *
 * Uso:
 *   {$number|number_abbr}
 *   {$number|number_abbr:1}
 *
 * @param int|float $number
 * @param int $decimals
 *
 * @return string
 */
function smarty_modifier_number_abbr(int|float $number, ?int $decimals = null): string {

   $units = ['', 'K', 'M', 'G', 'T', 'P'];

   if ($number < 1000) {
      return (string) $number;
   }

   $sign = $number < 0 ? '-' : '';
   $number = abs($number);

   $exp = (int) floor(log($number, 1000));
   $exp = min($exp, count($units) - 1);

   $value = $number / (1000 ** $exp);

   if ($decimals === null) {
      $decimals = ($value < 10) ? 1 : 0;
   }

   $value = round($value, $decimals);
   $value = rtrim(rtrim((string)$value, '0'), '.');

   return $sign . $value . $units[$exp];
}
