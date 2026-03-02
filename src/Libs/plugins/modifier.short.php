<?php

// El nombre de la función para un modificador debe ser smarty_modifier_NOMBRE
function smarty_modifier_short($fecha, $modo = 'completo', $formato_fecha = 'j M Y') {

	$timestamp = is_numeric($fecha) ? (int)$fecha : strtotime($fecha);
	if (!$timestamp) {
		return '';
	}

	$ahora = time();
	$diferencia = $ahora - $timestamp;

	if ($diferencia < 0) {
		return 'en el futuro';
	}

	$segundos = $diferencia;
	$minutos = floor($segundos / 60);
	$horas = floor($horas = floor($minutos / 60));
	$dias = floor($horas / 24);

	// Modo 'corto' o 'max_short'
	if ($modo === 'corto' || $modo === 'max_short') {
		if ($dias >= 730) { // > 2 años
			return date('M Y', $timestamp);
		} elseif ($dias >= 365) { // 1 a 2 años
			return date('M Y', $timestamp);
		} elseif ($dias >= 31) { // Más de un mes
			return date('j M', $timestamp);
		} else {
			if ($dias > 0) {
				return $dias . ' d';
			}
			if ($horas > 0) {
				return $horas . ' h';
			}
			if ($minutos > 0) {
				return $minutos . ' min';
			}
			return '1 min';
		}
	}

	// --- Lógica existente para otros modos ---
	if ($modo === 'relativo_corto') {
		if ($dias > 0) {
			if ($dias == 1) return 'ayer';
			if ($dias < 7) return $dias . ' días';
			if ($dias < 8) return 'la semana pasada';
			if ($dias < 14) return 'hace 2 semanas';
			if ($dias < 21) return 'hace 3 semanas';
			if ($dias < 31) return 'este mes';
			if ($dias < 61) return 'el mes pasado';
			if ($dias < 365) {
				$meses = floor($dias / 30);
				return 'hace ' . $meses . ' mes' . ($meses > 1 ? 'es' : '');
			}
		} else {
			if ($horas > 0) return $horas . 'h';
			if ($minutos > 0) return $minutos . 'm';
			return 'ahora';
		}
	}

	if ($modo === 'relativo_largo') {
		if ($dias > 0) {
			if ($dias == 1) return 'Ayer';
			if ($dias < 7) return 'Hace ' . $dias . ' días';
			if ($dias < 31) {
				$semanas = floor($dias / 7);
				return 'Hace ' . $semanas . ' semana' . ($semanas != 1 ? 's' : '');
			}
			if ($dias < 365) {
				$meses = floor($dias / 30);
				return 'Hace ' . $meses . ' mes' . ($meses != 1 ? 'es' : '');
			}
			$anos = floor($dias / 365);
			return 'Hace ' . $anos . ' año' . ($anos != 1 ? 's' : '');
		}
		if ($horas > 0) return 'Hace ' . $horas . ' hora' . ($horas != 1 ? 's' : '');
		if ($minutos > 0) return 'Hace ' . $minutos . ' minuto' . ($minutos != 1 ? 's' : '');
		return 'Justo ahora';
	}

	// 'completo' por defecto
	return date($formato_fecha, $timestamp);
}