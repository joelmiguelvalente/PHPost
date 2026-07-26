<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

interface Renderable
{
    public static function show(string $slot): string;
    public static function get(int $adsId): array;
    public static function getAll(): array;
    public static function save(int $id): bool;
}

final class Ads implements Renderable
{
    /**
     * Caché de anuncios.
     *
     * @var array<string, array>
     */
    private static array $cache = [];

    /**
     * Carga todos los anuncios activos.
     */
    private static function load(): void {
        if (self::$cache !== []) {
            return;
        }
        foreach (DB::fetchAll("SELECT ads_id, nombre, titulo, codigo, activo, orden FROM w_publicidad ORDER BY orden ASC") as $ad
        ) {
            self::$cache[$ad['nombre']] = $ad;
        }
    }

    /**
     * Obtiene un anuncio por su nombre.
     */
    public static function get(int $adsId): array {
        $data = DB::fetch("SELECT * FROM w_publicidad WHERE ads_id = :ads_id LIMIT 1", [
            'ads_id' => $adsId
        ]);
        return $data;
    }

    /**
     * Obtiene todos los anuncios.
     */
    public static function getAll(): array {
        return DB::fetchAll("SELECT * FROM w_publicidad ORDER BY orden ASC");
    }

    /**
     * Guarda un anuncio.
     */
    public static function save(int $id): bool {
        $data = [
            'titulo' => trim($_POST['titulo']),
            'codigo' => trim($_POST['codigo']),
            'activo' => empty($_POST['activo']) ? 0 : 1,
            'orden'  => (int) $_POST['orden']
        ];

        if(DB::update('w_publicidad', $data, 'ads_id = :id', ['id' => $id])) {
            return true;
        }
        return false;
    }

    /**
     * Muestra un anuncio.
     */
    public static function show(string $slot): string {
        self::load();
        $ad = self::$cache[$slot] ?? null;
        if ($ad === null) {
            return '';
        }
        if (!(bool) $ad['activo']) {
            return '';
        }
        return (string) $ad['codigo'];
    }
}
