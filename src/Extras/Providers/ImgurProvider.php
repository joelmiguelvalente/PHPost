<?php

declare(strict_types=1);

/**
 * @package    PHPost/Extras/Providers
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class ImgurProvider implements ImageProviderInterface
{
    public function __construct(private readonly string $apiKey) {}

    public function upload(string $filePath): string
    {
        $imageData = file_get_contents($filePath);

        if ($imageData === false) {
            throw new RuntimeException('No se pudo leer el archivo temporal.');
        }

        $ch = curl_init('https://api.imgur.com/3/image.json');

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Client-ID ' . $this->apiKey],
            CURLOPT_POSTFIELDS     => ['image' => base64_encode($imageData)],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result    = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new RuntimeException('Error cURL (Imgur): ' . $curlError);
        }

        $data = json_decode($result, true);

        if (empty($data['data']['link'])) {
            $msg = $data['data']['error'] ?? 'Respuesta inválida de Imgur.';
            throw new RuntimeException($msg);
        }

        return $data['data']['link'];
    }
}
