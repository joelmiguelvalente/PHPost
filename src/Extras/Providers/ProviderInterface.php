<?php

declare(strict_types=1);

/**
 * @package    PHPost/Extras/Providers
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

interface ImageProviderInterface
{
    /**
     * Sube una imagen y retorna la URL pública.
     *
     * @throws RuntimeException Si la subida falla.
     */
    public function upload(string $filePath): string;
}
