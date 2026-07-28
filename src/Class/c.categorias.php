<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class tsCategorias {

    public function __construct(
        protected tsCore $Core
    ) {}

    public function saveOrden(): void {
        $ordenado = [];
        # Obtenemos lista con el nuevo orden
        $nuevo_orden = 1;
        foreach (explode(',', $_POST["cats"]) as $orden) {
            DB::update('p_categorias', ['c_orden' => $nuevo_orden], "cid = :id", ['id' => $orden]);
            array_push($ordenado, $nuevo_orden);
            $nuevo_orden++;
        }
    }

    public function getCat(): array {
        $cid = (int)($_GET['cid'] ?? 0);
        $data = DB::fetch("SELECT * FROM p_categorias WHERE cid = :id LIMIT 1", ['id' => $cid]);
        return $data;
    }

    public function saveCat(): bool {
        $cid = (int)($_GET['cid'] ?? 0);
        //
        $nombre = Html::escape($this->Core->parseBadWords($_POST['c_nombre']));
        $categoria = [
            "c_nombre" => $nombre,
            "c_seo" => Extras::slugify($nombre),
            "c_img" => Html::escape($this->Core->parseBadWords($_POST['c_img'])),
        ];
        # Guardamos en la tabla
        return (DB::update('p_categorias', $categoria, "cid = :id", ['id' => $cid]));
    }

    public function MoveCat(): bool {
        $new = (int)($_POST['newcid'] ?? 0);
        $old = (int)($_POST['oldcid'] ?? 0);
        return (DB::update('p_categorias', ['post_category' => $new], "post_category = :old", ['old' => $old]));
    }

    public function newCat(): bool {
        $nombre = Html::escape($this->Core->parseBadWords($_POST['c_nombre']));
        # Orden
        $orden = DB::fetch('SELECT COUNT(cid) AS total FROM p_categorias');
        $orden = (int)$orden['total'] + 1;
        # Insertamos los datos
        $insert = DB::insert('p_categorias', [
            'c_orden' => $orden,
            'c_nombre' => $nombre,
            'c_seo' => Extras::slugify($nombre),
            'c_img' => Html::escape($this->Core->parseBadWords($_POST['c_img']))
        ]);
        return ($insert);
    }

    public function delCat(): string|bool {
        $cid = (int)($_GET['cid'] ?? 0);
        $ncid = (int)($_POST['ncid'] ?? 0);
        // MOVER
        if (empty($ncid) && $ncid === 0) {
            return 'Antes de eliminar una categoría debes elegir a donde mover sus categorías.';
        }
        if(!DB::update('p_categorias', ['post_category' => $ncid], "post_category = :cid", ['cid' => $cid])) {
            return 'Lo sentimos ocurrió un error.';
        }
        return (DB::delete('p_categorias', 'cid = :cid', ['cid' => $cid]));
    }

}
