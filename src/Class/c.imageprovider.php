<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 *
 * Gestiona los proveedores de subida de imágenes.
 *
 * Permite listar, crear, editar, eliminar y activar proveedores
 * como Imgur, ImgBB o Cloudinary desde el panel de administración.
 */

class ImageProvider
{
    private const TABLE = 'w_image_providers';

    private const COLUMNS = 'provider_id, provider_slug, provider_name, api_key, is_active';

    // Lectura

    /**
     * Retorna un proveedor por su ID (usado para prellenar el formulario de edición).
     *
     * El ID se obtiene de $_GET['id']. Retorna null si no existe.
     */
    public function getProvider(): ?array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            return null;
        }
        return DB::fetch("SELECT " . self::COLUMNS . " FROM " . self::TABLE . " WHERE provider_id = :id", ['id' => $id]);
    }

    /**
     * Retorna todos los proveedores ordenados por ID descendente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return DB::fetchAll("SELECT " . self::COLUMNS . " FROM " . self::TABLE . " ORDER BY provider_id DESC");
    }

    /**
     * Retorna el proveedor activo actualmente, o null si ninguno está activo.
     *
     * @return array<string, mixed>|null
     */
    public function getActive(): ?array
    {
        return DB::fetch("SELECT " . self::COLUMNS . " FROM " . self::TABLE . " WHERE is_active = 1 LIMIT 1");
    }

    // Escritura

    /**
     * Activa un proveedor y desactiva el resto en una transacción.
     * Actualiza la API key al mismo tiempo.
     *
     * @throws InvalidArgumentException Si la API key está vacía.
     */
    public function activate(int $providerId, string $apiKey): bool
    {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('La API key no puede estar vacía.');
        }

        DB::begin();
        try {
            DB::query("UPDATE " . self::TABLE . " SET is_active = 0");
            DB::update(
                self::TABLE,
                [
                    'is_active'  => 1,
                    'api_key'    => trim($apiKey),
                    'updated_at' => time(),
                ],
                'provider_id = :provider_id',
                ['provider_id' => $providerId]
            );
            DB::commit();
            return true;
        } catch (Throwable) {
            DB::rollback();
            return false;
        }
    }

    /**
     * Crea un nuevo proveedor con los datos del formulario (POST).
     *
     * @return bool|int ID insertado, o false si falló.
     */
    public function newProvider(): bool|int
    {
        return DB::insert(self::TABLE, $this->getProviderData());
    }

    /**
     * Edita un proveedor existente con los datos del formulario (POST).
     *
     * @return bool|int Filas afectadas, o false si falló.
     */
    public function editProvider(): bool|int
    {
        $id = (int) ($_POST['provider_id'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        return DB::update(self::TABLE, $this->getProviderData(), 'provider_id = :id', ['id' => $id]);
    }

    /**
     * Elimina un proveedor por su ID (solo acepta POST).
     *
     * No permite eliminar el proveedor activo para evitar dejar
     * el sistema sin proveedor configurado.
     *
     * @return bool|int Filas afectadas, o false si no es POST, ID inválido o está activo.
     */
    public function delProvider(): bool|int
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        $id = (int) ($_POST['provider_id'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        // Protección: no eliminar el proveedor activo
        $isActive = (bool) DB::value("SELECT is_active FROM " . self::TABLE . " WHERE provider_id = :id", ['id' => $id]);

        if ($isActive) {
            return false;
        }
        return DB::delete(self::TABLE, 'provider_id = :id', ['id' => $id]);
    }

    // Privado

    /**
     * Construye el array de datos del proveedor desde $_POST.
     *
     * Si `is_active` es 1, desactiva los demás antes de retornar
     * para mantener la restricción de un solo proveedor activo.
     *
     * @return array<string, mixed>
     */
    private function getProviderData(): array
    {
        $name     = trim($_POST['provider_name'] ?? '');
        $isActive = (int) ($_POST['is_active'] ?? 0);

        if ($isActive === 1) {
            DB::query("UPDATE " . self::TABLE . " SET is_active = 0");
        }

        return [
            'provider_name' => $name,
            'provider_slug' => Extras::slugify($name),
            'api_key'       => trim($_POST['api_key'] ?? ''),
            'is_active'     => $isActive,
            'updated_at'    => time(),
        ];
    }
}
