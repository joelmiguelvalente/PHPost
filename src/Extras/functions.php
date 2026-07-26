<?php

declare(strict_types=1);

/**
 * @package    Extras
 * @author     Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) exit('No se permite el acceso directo al script');

/**
 * Mostrar error con diseño comprimido y agradable en pantalla
 */
function show_error(string $error = 'Indefinido', string $type = 'db', array $info = []): never {
	$tsUser = Container::get(tsUser::class);

	$table = '';
	if($type === 'db') {
		$extra = [];

		if (isset($info['file'])) {
			$extra[] = "<tr><td>Archivo</td><td>{$info['file']}</td></tr>";
		}
		if (isset($info['line'])) {
			$extra[] = "<tr class=\"alt\"><td>Línea</td><td>{$info['line']}</td></tr>";
		}
		if (isset($info['query']) && ($tsUser->is_admod || Config::app('debug.active'))) {
			$extra[] = "<tr><td colspan=\"2\"><kbd>{$info['query']}</kbd></td></tr>";
		}
		if (isset($info['error']) && Config::app('debug.active') || $tsUser->is_admod) {
			$extra[] = "<tr><td colspan=\"2\"><p class=\"warning\">{$info['error']}</p></td></tr>";
		}
		$table = '<table border="0"><tbody>' . implode('', $extra) . '</tbody></table>';
	}

	$title = ($type === 'db') ? "Base de datos" : $type;
	exit("<head><meta charset=\"UTF-8\" /><link rel=\"preconnect\" href=\"https://fonts.googleapis.com\"><link href=\"https://fonts.googleapis.com/css2?family=Poppins&display=swap\" rel=\"stylesheet\"><title>Error › {$title}</title><style type=\"text/css\">*,*::after,*::before{padding:0;margin:0;box-sizing: content-box;}html{background:#EEE;}html,body{width:100%;height:100vh;}body{font-family:'Poppins',sans-serif;}#error-page{border:1px solid #CCC;background:#FFF;padding:20px;min-width:650px;max-width:780px;margin:1rem auto}#error-page h1{font-size: 28px;border-bottom: 1px solid #CCC5;padding: 6px;margin-bottom: 10px;}p.warning {background: #FFEEEE;color: #D75454;border:1px solid #D7545455;text-align: center;padding: 10px;margin: 6px 0;}table{border:1px solid #dbe4ef;border-collapse:collapse;text-align:left;width:100%;}table td,table th{padding:5px;}table tbody td{padding:10px;color:#5a5a5a;background:#FDFDFD;border-bottom:1px solid #f3f3f3;font-weight:normal;}table tbody .alt td{background:#E1EEf4;color:#00557F;}table tbody td:first-child{border-left: none;width: 10%;font-weight: bold;border-right: 1px solid #DFDFDF}table tbody tr:last-child td{border-bottom:none;font-weight: normal; }td kbd {line-height:1.325rem;display:block;padding:.875rem;font-size:1rem}</style></head><body><div id=\"error-page\"><h1>{$title}</h1><p>{$error}</p>{$table}</div></body>");
}
