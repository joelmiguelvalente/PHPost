<?php

/**
 * @name db_legacy.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

/**
 * LEGACY DATABASE WRAPPER
 * Mantiene compatibilidad total con db_exec()
 */

function db_exec() {
	global $tsUser, $tsAjax;

	[$info, $type, $data] = array_pad(func_get_args(), 3, null);
	$db = Database::instance();

	if (is_array($info)) {
		if (!$tsUser->is_admod && Config::app('app.debug')) {
			$info[0] = explode('\\', $info[0]);
		}

		$info = [
			'file'  => ($tsUser->is_admod || Config::app('app.debug')) ? $info[0] : end($info[0]),
			'line'  => $info[1] ?? null,
			'query' => $data,
		];
	} else {
		$data = $type;
		$type = $info;
		$info = ['query' => $data];
	}

	try {
		return match ($type) {
			'query'              => $db->rawQuery($data),
			'real_escape_string' => $db->escape((string) $data),
			'num_rows'           => $db->numRows($data),
			'fetch_assoc'        => $db->fetch($data),
			'fetch_array'        => $db->fetchAll($data),
			'fetch_row'          => $db->fetchRow($data),
			'free_result'        => $data->free(),
			'insert_id'          => $db->insertId(),
			'error'              => $db->lastError('error'),
			'errno' 					=> $db->lastError('errno'),
			default              => null,
		};
	} catch (Throwable $e) {
		if (
			!$tsAjax &&
			Config::app('app.debug') &&
			($info['file'] || $info['line'] || ($info['query'] && $tsUser->is_admod))
		) {
			show_error('Error en consulta SQL.', 'db', $info + ['error' => $e->getMessage()]);
		}

		return false;
	}
}


function result_array(mysqli_result $result): array {
	$rows = [];
	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}
	return $rows;
}