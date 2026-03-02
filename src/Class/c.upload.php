<?php

/**
 * @name c.upload.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once TS_UTILS . '/AvatarConfig.php';

final class tsUpload {

	private const MAX_UPLOAD_SIZE = 6 * 1024 * 1024; // 6MB
	private const MIN_UPLOAD_SIZE = 2 * 1024 * 1024; // 2MB
	private const ALLOWED_TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
	private const MAX_DIMENSION = 3000;

	/* ==========================================================
	 *  PUBLIC API
	 * ======================================================== */

	public function uploadTempImage(?array $file, ?string $url = null): array
	{
		if ($file) {
			return $this->handleFileUpload($file);
		}

		if ($url) {
			return $this->handleUrlUpload($url);
		}

		return ['error' => 'No se recibió ninguna imagen'];
	}

	/**
	 * Mantiene el nombre original, pero ahora genera
	 * todas las variantes finales
	 */
	public function cropAvatarWebp(int $userId): array
	{
		$source = TS_STORAGE . '/uploads/avatar_' . $_POST['key'] . '.' . $_POST['ext'];

		if (!is_file($source)) {
			return ['error' => 'Archivo fuente inexistente'];
		}

		$crop = $this->sanitizeCropData($_POST);
		$src  = $this->createImageResource($source);

		if (!$src) {
			return ['error' => 'Formato de imagen no soportado'];
		}

		// Canvas base 200x200 (máxima)
		$base = $this->createAvatarCanvas(200);

		imagecopyresampled($base, $src, 0, 0, $crop['x'], $crop['y'], 200, 200, $crop['w'], $crop['h']);

		$this->storeAvatarVariants($base, $userId);

		imagedestroy($src);
		imagedestroy($base);
		@unlink($source);

		return ['error' => 'success'];
	}

	/* ==========================================================
	 *  UPLOADS
	 * ======================================================== */

	private function handleFileUpload(array $file): array
	{
		if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
			return ['error' => 'Archivo inválido'];
		}


		if ($file['size'] > self::MAX_UPLOAD_SIZE || $file['size'] < self::MIN_UPLOAD_SIZE) {
			$txt = ($file['size'] > self::MAX_UPLOAD_SIZE) ? 'supera el' : 'es inferior del';
			return ['error' => "El archivo $txt peso(mb) permitido"];
		}

		$type = exif_imagetype($file['tmp_name']);
		if (!in_array($type, self::ALLOWED_TYPES, true)) {
			return ['error' => 'Formato de imagen no permitido'];
		}

		return $this->storeTempImage(
			$this->createImageResource($file['tmp_name']),
			$type
		);
	}

	private function handleUrlUpload(string $url): array
	{
		$info = @getimagesize($url);
		if (!$info || !in_array($info[2], self::ALLOWED_TYPES, true)) {
			return ['error' => 'La URL no contiene una imagen válida'];
		}

		if ($info[0] < 120 || $info[1] < 120) {
			return ['error' => 'La imagen es demasiado pequeña'];
		}

		$img = imagecreatefromstring(file_get_contents($url));
		return $this->storeTempImage($img, $info[2]);
	}

	/* ==========================================================
	 *  IMAGE HELPERS
	 * ======================================================== */

	private function storeTempImage($img, int $type): array
	{
		if (!$img) {
			return ['error' => 'No se pudo procesar la imagen'];
		}

		[$w, $h] = [imagesx($img), imagesy($img)];
		if ($w > self::MAX_DIMENSION || $h > self::MAX_DIMENSION) {
			$img = $this->resizeImage($img, self::MAX_DIMENSION);
		}

		$key  = bin2hex(random_bytes(4));
		$ext  = image_type_to_extension($type, false);
		$path = TS_STORAGE . "/uploads/avatar_{$key}.{$ext}";

		imagejpeg($img, $path, 95);
		imagedestroy($img);

		return [
			'msg' => true,
			'key' => $key,
			'ext' => $ext
		];
	}

	private function resizeImage($src, int $size)
	{
		$dst = imagecreatetruecolor($size, $size);
		imagealphablending($dst, false);
		imagesavealpha($dst, true);

		imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));

		return $dst;
	}

	private function createImageResource(string $path)
	{
		return match (exif_imagetype($path)) {
			IMAGETYPE_JPEG => imagecreatefromjpeg($path),
			IMAGETYPE_PNG  => imagecreatefrompng($path),
			IMAGETYPE_GIF  => imagecreatefromgif($path),
			default        => null,
		};
	}

	private function createAvatarCanvas(int $size)
	{
		$img = imagecreatetruecolor($size, $size);
		imagealphablending($img, false);
		imagesavealpha($img, true);

		$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
		imagefilledrectangle($img, 0, 0, $size, $size, $transparent);

		return $img;
	}

	/* ==========================================================
	 *  AVATAR STORAGE (compatible con Avatar.php)
	 * ======================================================== */

	private function storeAvatarVariants($base, int $userId): void
	{
		$dir = AvatarConfig::baseDir($userId);

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		foreach (AvatarConfig::SIZES as $size) {

			$img = ($size === 200)
				? $base
				: $this->resizeImage($base, $size);

			$prefix = ($size === 200) ? 'avatar' : 'thumb_avatar';

			foreach (AvatarConfig::FORMATS as $format) {

				if (!AvatarConfig::supportsFormat($format)) {
					continue;
				}

				$path = "{$dir}{$prefix}.{$format}";

				match ($format) {
					'webp' => imagewebp($img, $path, AvatarConfig::QUALITY['webp']),
					'avif' => imageavif($img, $path, AvatarConfig::QUALITY['avif']),
					'png'  => imagepng($img, $path, AvatarConfig::QUALITY['png']),
				};
			}

			if ($img !== $base) {
				imagedestroy($img);
			}
		}
	}

	private function sanitizeCropData(array $data): array
	{
		return [
			'x' => max(0, (int)$data['x']),
			'y' => max(0, (int)$data['y']),
			'w' => max(1, (int)$data['w']),
			'h' => max(1, (int)$data['h']),
		];
	}
}
