<?php

declare(strict_types=1);

/**
 * @package    Providers
 * @author     Miguel92
 * @copyright  2026
 */

final class CloudinaryProvider implements ImageProviderInterface
{
    // api_key en DB guarda: "cloud_name:api_key:api_secret"
    public function __construct(private readonly string $apiKey) {}

    public function upload(string $filePath): string
    {
        $parts = explode(':', $this->apiKey, 3);

        if (count($parts) !== 3) {
            throw new RuntimeException('Cloudinary: el formato de credenciales es cloud_name:api_key:api_secret');
        }

        [$cloudName, $key, $secret] = $parts;

        $imageData = file_get_contents($filePath);

        if ($imageData === false) {
            throw new RuntimeException('No se pudo leer el archivo temporal.');
        }

        $timestamp = time();
        $signature = sha1("timestamp={$timestamp}{$secret}");

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POSTFIELDS     => [
                'file'      => base64_encode($imageData),
                'api_key'   => $key,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
        ]);

        $result    = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new RuntimeException('Error cURL (Cloudinary): ' . $curlError);
        }

        $data = json_decode($result, true);

        if (empty($data['secure_url'])) {
            $msg = $data['error']['message'] ?? 'Respuesta inválida de Cloudinary.';
            throw new RuntimeException($msg);
        }

        return $data['secure_url'];
    }
}
