<?php

/**
 * Smarty modifier
 *
 * Type:     modifier
 * Name:     fecha
 * Date:     Ene 26, 2026
 *
 * Formatea una fecha Unix timestamp en distintos formatos legibles.
 * Evita el uso de strftime() y es compatible con PHP 8.1+.
 *
 * Ejemplos:
 *   {$fecha|fecha}                -> formato por defecto (relativo)
 *   {$fecha|fecha:'long'}         -> 20 de abril de 2024
 *   {$fecha|fecha:'short'}        -> 20/04/2024
 *   {$fecha|fecha:'iso'}          -> 2024-04-20
 *   {$fecha|fecha:'time'}         -> 14:32
 *   {$fecha|fecha:'php:d-m-Y'}    -> formato PHP personalizado
 *
 * @author   Miguel92
 * @version  2.0
 *
 * @param    int    $timestamp  Unix timestamp
 * @param    string $format     Alias de formato o formato PHP (php:...)
 *
 * @return   string
 */


function smarty_modifier_fecha(int $timestamp, string $format = 'default'): string {
   $date = (new DateTimeImmutable())->setTimestamp($timestamp);
   $now  = new DateTimeImmutable();

   $months = [
      1 => 'enero', 2 => 'febrero', 3 => 'marzo',
      4 => 'abril', 5 => 'mayo', 6 => 'junio',
      7 => 'julio', 8 => 'agosto', 9 => 'septiembre',
      10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
   ];

   $formats = [
      // Alias semánticos
      'default' => function () use ($date, $now, $months) {
         $diff = $now->getTimestamp() - $date->getTimestamp();
         $time = $date->format('H:i');

         if ($diff < 3600) {
            $m = max(1, intdiv($diff, 60));
            return "Hace $m minuto" . ($m === 1 ? '' : 's');
         }
         if ($diff < 86400) {
            $h = intdiv($diff, 3600);
            return "Hace $h hora" . ($h === 1 ? '' : 's');
         }
         if ($diff < 172800) {
            return "Ayer a las $time";
         }
         if ($diff < 604800) {
            $days = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
            return 'El ' . $days[(int)$date->format('w')] . ' a las ' . $time;
         }
         return 'El ' . $date->format('d') . ' de ' . $months[(int)$date->format('n')] . ' a las ' . $time;
      },
      // 20 de abril de 2024
      'long' => fn () => $date->format('d') . ' de ' . $months[(int)$date->format('n')] . ' de ' . $date->format('Y'),
      // 20/04/2024
      'short' => fn () => $date->format('d/m/Y'),
      // 2024-04-20
      'iso' => fn () => $date->format('Y-m-d'),
      // 20 abril
      'day_month' => fn () => $date->format('d') . ' ' . $months[(int)$date->format('n')],
      // Hora sola
      'time' => fn () => $date->format('H:i'),
      // Fecha completa
      'full_datetime' => fn () => $date->format('d/m/Y H:i:s'),
   ];

   if (!isset($formats[$format])) {
      throw new InvalidArgumentException("Formato de fecha no soportado: $format");
   }

   return $formats[$format]();
}