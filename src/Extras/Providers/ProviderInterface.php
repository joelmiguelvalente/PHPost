<?php

/**
 * @name src/Extras/Providers/ProviderInterface.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

interface ImageProviderInterface
{
    /**
     * Sube una imagen y retorna la URL pública.
     *
     * @throws RuntimeException Si la subida falla.
     */
    public function upload(string $filePath): string;
}
