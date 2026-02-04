<?php
declare(strict_types=1);

require dirname(__DIR__, 1) . '/header.php'; // lo que uses para DB

$rangos = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT rango_id, r_allows FROM u_rangos'));

foreach ($rangos as $rango) {
    $raw = $rango['r_allows'];
    // Detectar si ya es JSON
    if ($raw !== '' && $raw[0] === '{') {
        continue;
    }
    $old = @unserialize($raw);
    if (!is_array($old)) {
        echo "Rango {$rango['rango_id']} inválido, se setean defaults\n";
        $old = [];
    }
    $normalized = Permissions::DEFINITIONS;
    foreach ($normalized as $key => $default) {
        if (!array_key_exists($key, $old)) {
            continue;
        }
        if (is_bool($default)) {
            $normalized[$key] = ($old[$key] === 'on' || $old[$key] === true);
        } else {
            $normalized[$key] = (int) $old[$key];
        }
    }

    $json = json_encode($normalized, JSON_THROW_ON_ERROR);

    db_exec([__FILE__, __LINE__], 'query', "UPDATE u_rangos SET r_allows = '" . $json . "' WHERE rango_id = {$rango['rango_id']}");

    echo "Rango {$rango['rango_id']} migrado\n";
}
