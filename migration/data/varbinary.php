<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Migration
 * @author     Miguel92
*/

require_once dirname(__DIR__, 1) . '/app.php';

header('Content-Type: application/json; charset=UTF-8');

$name = "varbinary";
$time = time();
$continue = true;
$list = [
    ['table' => `f_comentarios`, 'column' => `c_ip`],
    ['table' => `f_fotos`, 'column' => `f_ip`],
    ['table' => `p_comentarios`, 'column' => `c_ip`],
    ['table' => `p_posts`, 'column' => `post_ip`],
    ['table' => `u_miembros`, 'column' => `user_last_ip`],
    ['table' => `u_login_attempts`, 'column' => `ip`],
    ['table' => `u_nicks`, 'column' => `ip`],
    ['table' => `u_muro`, 'column' => `p_ip`],
    ['table' => `u_muro_comentarios`, 'column' => `c_ip`],
    ['table' => `u_respuestas`, 'column' => `mr_ip`],
    ['table' => `u_sessions`, 'column' => `session_ip`],
    ['table' => `u_suspension`, 'column' => `susp_ip`],
    ['table' => `w_contacts`, 'column' => `ip`],
    ['table' => `w_activate`, 'column' => `ip`],
    ['table' => `w_medallas_assign`, 'column' => `medal_ip`],
    ['table' => `w_historial`, 'column' => `mod_ip`],
    ['table' => `w_visitas`, 'column' => `ip`]
];

foreach($list as $items) {
    $sentencia = "ALTER TABLE `{$items['table']}` CHANGE `{$items['column']}` `{$items['column']}` VARBINARY(16) DEFAULT ''";
    $continue = DB::query($sentencia) ? true : false;
}

if ($continue) {
    // Ya existe, lo que significa que NO hemos migrado aún
    if(DB::insert('w_migrations', [ 'migration' => $name,  'executed_at' => $time ])) {
        echo json_encode([
            'success' => true,
            'message' => $name . ' migrado'
        ]);
    }
    exit;
} else {
    // Ya migrado
    echo json_encode(['success' => false, 'message' => $name . ' ya fue migrado']);
    exit;
}
