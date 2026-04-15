<?php

/**
 * @name src/Extras/Providers/ImgBBProvider.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

final class ImgBBProvider implements ImageProviderInterface
{
    public function __construct(private readonly string $apiKey) {}

    public function upload(string $filePath): string
    {
        $imageData = file_get_contents($filePath);

        if ($imageData === false) {
            throw new RuntimeException('No se pudo leer el archivo temporal.');
        }

        $ch = curl_init('https://api.imgbb.com/1/upload?key=' . urlencode($this->apiKey));

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => ['image' => base64_encode($imageData)],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result    = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new RuntimeException('Error cURL (ImgBB): ' . $curlError);
        }

        $data = json_decode($result, true);

        if (empty($data['success']) || empty($data['data']['url'])) {
            $msg = $data['error']['message'] ?? 'Respuesta inválida de ImgBB.';
            throw new RuntimeException($msg);
        }

        return $data['data']['url'];
    }
}
