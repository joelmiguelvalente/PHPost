<?php
/**
 * Smarty modifier
 *
 * Type:     modifier
 * Name:     tiempo_restante
 * Date:     Feb 18, 2026
 *
 * Calcula el tiempo restante a partir de un Unix timestamp futuro
 * y lo devuelve en un formato humano compacto (incluye segundos).
 *
 * Ejemplos:
 *   {$timestamp|tiempo_restante}  -> 1d 4h 12m 08s
 *   {$timestamp|tiempo_restante}  -> Suspensión vencida
 *
 * @author   Miguel92
 * @version  1.1
 *
 * @param    int  $timestamp  Unix timestamp de finalización
 *
 * @return   string
 */

function smarty_modifier_tiempo_restante(int $timestamp): string
{
    $now  = time();
    $diff = $timestamp - $now;

    if ($diff <= 0) {
        return 'Suspensión vencida';
    }

    $dias     = intdiv($diff, 86400);
    $diff    %= 86400;
    $horas    = intdiv($diff, 3600);
    $diff    %= 3600;
    $minutos  = intdiv($diff, 60);
    $segundos = $diff % 60;

    $out = [];

    if ($dias > 0) {
        $out[] = $dias . 'd';
    }
    if ($horas > 0) {
        $out[] = $horas . 'h';
    }
    if ($minutos > 0) {
        $out[] = $minutos . 'm';
    }

    $out[] = str_pad((string)$segundos, 2, '0', STR_PAD_LEFT) . 's';

    return implode(' ', $out);
}
