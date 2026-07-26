<?php

declare(strict_types=1);

/**
 * @package    PHPost/Utils
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

require_once __DIR__ . '/AvatarConfig.php';

final class Avatar
{
    private string $publicBase;

    private string $avatarApi;

    /**
     * @param string $publicBaseUrl URL pública base para avatars (ej: https://sitio.com/storage/avatar/)
     * @param string|null $avatarApi URL de la API de DiceBear (null = usa AvatarConfig::AVATAR_API)
     */
    public function __construct(string $publicBaseUrl, ?string $avatarApi = null)
    {
        $this->publicBase = rtrim($publicBaseUrl, '/') . '/';
        $this->avatarApi  = $avatarApi ?? AvatarConfig::AVATAR_API;
    }

    /**
     * Garantiza que existan todas las variantes del avatar.
     * Se usa en el registro o bootstrap del usuario.
     */
    public function ensure(int $uid, string $username, int|string $color = AvatarConfig::DEFAULT_COLOR): void
    {
        if ($this->hasAllVariants($uid)) {
            return;
        }
        $this->create($uid, $username, $color);
    }

    /**
     * Devuelve la URL pública del avatar solicitado.
     * Si no existe, genera la variante on-demand (lazy) antes de devolver fallback.
     */
    public function get(int $uid, string $variant = 'avatar', string $format = 'webp'): string
    {
        $path = $this->buildPath($uid, $variant, $format);

        if (is_file($path['fs'])) {
            return $path['url'];
        }

        // Lazy generation: intenta crear solo la variante pedida
        if ($this->generateVariant($uid, $variant, $format)) {
            return $path['url'];
        }

        // Fallback por formato
        return $this->publicBase . 'default/' . $this->variantFilename($variant, 'png');
    }

    /**
     * Verifica si existe una variante específica.
     */
    public function exists(int $uid, string $variant = 'avatar', string $format = 'webp'): bool
    {
        $path = $this->buildPath($uid, $variant, $format);
        return is_file($path['fs']);
    }

    /**
     * Elimina todas las variantes del avatar de un usuario.
     */
    public function delete(int $uid): void
    {
        $userDir = AvatarConfig::baseDir($uid);
        if (!is_dir($userDir)) {
            return;
        }

        $files = glob($userDir . '/*');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        @rmdir($userDir);
    }

    /**
     * Verifica si existen todas las variantes y formatos.
     */
    private function hasAllVariants(int $uid): bool
    {
        foreach (AvatarConfig::VARIANTS as $variant => $size) {
            foreach (AvatarConfig::getSupportedFormats() as $format) {
                $file = AvatarConfig::baseDir($uid, $this->variantFilename($variant, $format));
                if (!is_file($file)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Crea todas las variantes del avatar (eager generation).
     */
    private function create(int $uid, string $username, int|string $color): void
    {
        $userDir = AvatarConfig::baseDir($uid);
        if (!is_dir($userDir) && !mkdir($userDir, 0755, true)) {
            Logger::error('No se pudo crear directorio de avatar', ['uid' => $uid, 'dir' => $userDir]);
            return;
        }

        $baseImage = $this->downloadBase($uid, $username, $color);
        if (!$baseImage) {
            Logger::warning('Fallo al descargar avatar base, se usará fallback', ['uid' => $uid, 'username' => $username]);
            return;
        }

        foreach (AvatarConfig::VARIANTS as $variant => $size) {
            $resized = $this->resize($baseImage, $size, $size);
            foreach (AvatarConfig::getSupportedFormats() as $format) {
                $filename = $this->variantFilename($variant, $format);
                $this->save($resized, $userDir . $filename, $format);
            }
            Compat::imagedestroy($resized);
        }
        Compat::imagedestroy($baseImage);
    }

    /**
     * Genera una sola variante on-demand (lazy generation).
     */
    private function generateVariant(int $uid, string $variant, string $format): bool
    {
        if (!AvatarConfig::hasVariant($variant) || !AvatarConfig::supportsFormat($format)) {
            return false;
        }

        // Necesitamos la imagen base; si no existe, no podemos generar
        $sourcePath = $this->findSourceImage($uid);
        if (!$sourcePath) {
            return false;
        }

        $source = $this->createImageResource($sourcePath);
        if (!$source) {
            return false;
        }

        $size = AvatarConfig::getVariantSize($variant);
        $resized = $this->resize($source, $size, $size);

        $userDir = AvatarConfig::baseDir($uid);
        $filename = $this->variantFilename($variant, $format);
        $this->save($resized, $userDir . $filename, $format);

        Compat::imagedestroy($resized);
        Compat::imagedestroy($source);

        return true;
    }

    /**
     * Busca una imagen base existente para redimensionar (cualquier formato).
     */
    private function findSourceImage(int $uid): ?string
    {
        $userDir = AvatarConfig::baseDir($uid);
        if (!is_dir($userDir)) {
            return null;
        }

        // Preferir la variante 'avatar' en el mejor formato disponible
        foreach (AvatarConfig::getSupportedFormats() as $format) {
            $path = $userDir . $this->variantFilename('avatar', $format);
            if (is_file($path)) {
                return $path;
            }
        }

        // Fallback: cualquier archivo en el directorio
        $files = glob($userDir . '/*');
        return $files ? $files[0] : null;
    }

    private function normalizeHexColor(int|string $color): string
    {
        $color = ltrim((string)$color, '#');
        if (preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
            return strtolower($color);
        }
        // fallback seguro
        return AvatarConfig::DEFAULT_COLOR;
    }

    private function contrastColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        // luminancia relativa
        $luminance = (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
        // umbral razonable
        return $luminance < 128 ? 'ffffff' : '000000';
    }

    /**
     * Descarga la imagen base desde la API de DiceBear con reintentos.
     *
     * @param int         $uid      ID del usuario
     * @param string      $username Nombre de usuario
     * @param string|int  $color    Color de fondo (hex o random)
     */
    private function downloadBase(int $uid, string $username, string|int $color): ?\GdImage
    {
        $style = AvatarConfig::getStyle($uid, $username);

        $params = [
            'seed' => $username,
            'size' => 200,
        ];

        if ($style === 'initials') {
            $background = empty($color) ? 'random' : $this->normalizeHexColor($color);
            $textColor  = $background === 'random' ? 'ffffff' : $this->contrastColor($background);

            $params['backgroundColor'] = $background;
            $params['textColor']       = $textColor;
        }

        $url = sprintf(
            '%s/%s/png?%s',
            $this->avatarApi,
            $style,
            http_build_query($params)
        );

        $maxRetries = 3;
        $baseDelay  = 1000000; // 1 segundo en microsegundos

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $context = stream_context_create([
                'http' => [
                    'timeout'         => 5,
                    'follow_location' => 0,
                    'user_agent'      => 'PHPost-Avatar/1.0',
                ],
            ]);

            $data = @file_get_contents($url, false, $context);

            if ($data !== false) {
                $image = @imagecreatefromstring($data);
                if ($image) {
                    return $image;
                }
            }

            Logger::warning('Fallo al descargar avatar, reintentando', [
                'username' => $username,
                'attempt'  => $attempt + 1,
                'max'      => $maxRetries,
                'url'      => $url,
            ]);

            if ($attempt < $maxRetries - 1) {
                usleep($baseDelay * (1 << $attempt)); // backoff exponencial: 1s, 2s, 4s
            }
        }

        Logger::error('Fallo definitivo al descargar avatar', [
            'username' => $username,
            'url'      => $url,
        ]);

        return null;
    }

    /**
     * Redimensiona una imagen manteniendo transparencia.
     */
    private function resize(\GdImage $src, int $width, int $height): \GdImage
    {
        $dst = imagecreatetruecolor($width, $height);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
        return $dst;
    }

    /**
     * Guarda una imagen según el formato especificado.
     */
    private function save(\GdImage $img, string $path, string $format): void
    {
        $quality = AvatarConfig::QUALITY[$format] ?? null;

        $result = match ($format) {
            'webp' => imagewebp($img, $path, $quality),
            'avif' => imageavif($img, $path, $quality),
            'png'  => imagepng($img, $path, $quality),
            default => false,
        };

        if (!$result) {
            Logger::error('Error guardando avatar', ['path' => $path, 'format' => $format]);
        }
    }

    /**
     * Construye el nombre de archivo según la variante.
     */
    private function variantFilename(string $variant, string $format): string
    {
        if (!array_key_exists($variant, AvatarConfig::VARIANTS)) {
            throw new InvalidArgumentException("Variante de avatar inválida: {$variant}");
        }
        return $variant === 'avatar' ? "avatar.{$format}" : "thumb_avatar.{$format}";
    }

    /**
     * Construye paths filesystem y URL pública.
     */
    private function buildPath(int $uid, string $variant, string $format): array
    {
        $filename = $this->variantFilename($variant, $format);

        return [
            'fs'  => AvatarConfig::baseDir($uid, $filename),
            'url' => $this->publicBase . "user_{$uid}/{$filename}",
        ];
    }

    /**
     * Crea recurso GD desde archivo local.
     */
    private function createImageResource(string $path): ?\GdImage
    {
        return match (exif_imagetype($path)) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_GIF  => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_AVIF => imagecreatefromavif($path),
            default        => null,
        };
    }

    public function getTag(int $uid, string $alt = 'Avatar del usuario', int $size = 24): string {
        $attributes = implode(' ', [
            'alt' => "alt=\"Avatar usuario\"",
            'title' => "title=\"{$alt}\"",
            'size' => "width=\"{$size}\" height=\"{$size}\"",
            'src' => "src=\"{$this->get($uid)}\""
        ]);
        return "<img {$attributes} />";
    }

    public function getTags(array $users): array {
        $avatars = [];
        foreach($users as $key => $user) {
            $avatars[$key] = $this->getTag((int)$user);
        }
        return $avatars;
    }
}
