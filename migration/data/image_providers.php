<?php

require_once dirname(__DIR__, 1) . '/app.php';
header('Content-Type: application/json; charset=UTF-8');

$name = "image_providers";

if (DB::exists("SHOW TABLES LIKE 'w_image_providers'")) {
    echo json_encode([
        'success' => false,
        'message' => $name . ' ya fue migrado'
    ]);
    exit;
}

$create = "CREATE TABLE w_image_providers (
    provider_id   TINYINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    provider_slug VARCHAR(32)       NOT NULL UNIQUE,
    provider_name VARCHAR(64)       NOT NULL,
    api_key       VARCHAR(255)      NOT NULL DEFAULT '',
    extra_config  JSON              NULL,
    is_active     TINYINT(1)        NOT NULL DEFAULT 0,
    PRIMARY KEY (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if (!DB::query($create)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo crear la tabla'
    ]);
    exit;
}

$providers = [
    [
        'provider_slug' => 'imgur',
        'provider_name' => 'Imgur',
        'api_key'       => 'b2fddcb704b44a5',
        'is_active'     => 1,
    ],
];

$continue = true;
$time     = time();

foreach ($providers as $provider) {
    if (!DB::insert('w_image_providers', $provider)) {
        $continue = false;
        break;
    }
}

if (!$continue) {
    echo json_encode([
        'success' => false,
        'message' => 'Hubo un error al insertar los datos'
    ]);
    exit;
}

if (DB::insert('w_migrations', ['migration' => $name, 'executed_at' => $time])) {
    echo json_encode([
        'success' => true,
        'message' => $name . ' migrado'
    ]);
    exit;
}
