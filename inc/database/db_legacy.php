<?php

/**
 * LEGACY DATABASE WRAPPER
 * Mantiene compatibilidad total con db_exec()
 */

function db_exec()
{
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
			'num_rows'           => $data->num_rows,
			'fetch_assoc'        => $data->fetch_assoc(),
			'fetch_array'        => $data->fetch_array(),
			'fetch_row'          => $data->fetch_row(),
			'free_result'        => $data->free(),
			'insert_id'          => $db->insertId(),
			'error'              => $db->error(),
			default              => null,
		};
	} catch (Throwable $e) {
		if (
			!$tsAjax &&
			Config::app('app.debug') &&
			($info['file'] || $info['line'] || ($info['query'] && $tsUser->is_admod))
		) {
			show_error(
				'Error en consulta SQL.',
				'db',
				$info + ['error' => $e->getMessage()]
			);
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