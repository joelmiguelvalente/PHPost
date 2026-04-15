<?php

/**
 * @name src/Extras/upload.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/header.php';
require_once __DIR__ . '/Providers/ProviderInterface.php';
require_once __DIR__ . '/Providers/CloudinaryProvider.php';
require_once __DIR__ . '/Providers/ImgurProvider.php';
require_once __DIR__ . '/Providers/ImgBBProvider.php';

const ALLOWED_MIME_TYPES = [
    'image/jpeg', 'image/png', 'image/gif', 'image/bmp',
    'image/webp', 'image/avif', 'image/tiff', 'image/heic',
];

/**
 * Mapa slug → clase del proveedor.
 * Para agregar uno nuevo: solo añadir la entrada acá y crear su Provider.
 */
const PROVIDER_MAP = [
    'imgur' => ImgurProvider::class,
    'imgbb' => ImgBBProvider::class,
    'cloudinary' => CloudinaryProvider::class,
];

function validateUploadedFile(array $file): void
{
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_MIME_TYPES, strict: true)) {
        throw new RuntimeException("Tipo de imagen no permitido: {$mimeType}");
    }
}

/**
 * Lee de la DB el proveedor activo e instancia su clase.
 *
 * @throws RuntimeException Si no hay proveedor activo o el slug es desconocido.
 */
function resolveProvider(): ImageProviderInterface
{
    $row = DB::fetch(
        "SELECT provider_slug, api_key FROM w_image_providers WHERE is_active = 1 LIMIT 1"
    );

    if (!$row) {
        throw new RuntimeException('No hay ningún proveedor de imágenes activo.');
    }

    $slug  = $row['provider_slug'];
    $class = PROVIDER_MAP[$slug] ?? null;

    if ($class === null) {
        throw new RuntimeException("Proveedor desconocido: {$slug}");
    }

    return new $class($row['api_key']);
}

function jsonResponse(int $status, string $msg, string $imageUrl = ''): never
{
    header('Content-Type: application/json');

    $response = ['status' => $status, 'msg' => $msg];

    if ($imageUrl !== '') {
        $response['image_link'] = $imageUrl;
        $response['thumb_link'] = $imageUrl;
    }

    echo json_encode($response);
    exit;
}

// -------------------------------------------------------
// Entrada
// -------------------------------------------------------
var_dump($_FILES);
if (!isset($_FILES['img']['tmp_name'])) {
    jsonResponse(0, 'Empty');
}

try {
    validateUploadedFile($_FILES['img']);

    $provider = resolveProvider();
    $imgUrl   = $provider->upload($_FILES['img']['tmp_name']);
    $idArea   = htmlspecialchars((string) ($_POST['id_area'] ?? ''), ENT_QUOTES, 'UTF-8');
    $isIframe = !empty($_POST['id_frame']);

    if ($isIframe) {
        echo sprintf(
            '<html><body>OK<script>window.parent.$("#%s").insertImage("%s","%s").closeModal().updateUI();</script></body></html>',
            $idArea, $imgUrl, $imgUrl
        );
    } else {
        jsonResponse(1, 'OK', $imgUrl);
    }

} catch (RuntimeException $e) {
    jsonResponse(0, $e->getMessage());
}
