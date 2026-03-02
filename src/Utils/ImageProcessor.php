<?php

/**
 * ImageProcessor - Procesador de imágenes seguro y modular para aplicaciones web
 * 
 * Esta clase proporciona una solución robusta para:
 * - Descarga segura de imágenes remotas
 * - Procesamiento y conversión a múltiples formatos
 * - Almacenamiento organizado con aislamiento por tipo/ID
 * - Protección contra ataques comunes (path traversal, DoS, etc.)
 * 
 * Diseñada con enfoque en:
 * - Seguridad OWASP (A01:2021, A06:2021)
 * - Principios SOLID (especialmente SRP e ISP)
 * - Inmutabilidad del objeto
 * - Configuración explícita y segura
 * 
 * @package PHPost
 * @subpackage ImageProcessing
 * @author Miguel92 - Desarrollador FullStack LAMP
 * @version 2.0.0
 * @license MIT
 * 
 * @example 
 * // Procesar imagen para un post
 * $processor = new ImageProcessor([
 *     'type' => 'posts',
 *     'id' => 123,
 *     'formats' => ['webp', 'avif', 'png'],
 *     'max_size' => 5 * 1024 * 1024 // 5MB
 * ]);
 * 
 * $localPath = $processor->process('https://example.com/image.jpg');
 * echo $processor->getPublicUrl(basename($localPath));
 * 
 * @example
 * // Procesar avatar de perfil con configuración personalizada
 * $avatarProcessor = new ImageProcessor([
 *     'type' => 'perfil',
 *     'id' => 456,
 *     'storage_path' => __DIR__ . '/../../storage/media/',
 *     'formats' => ['webp'],
 *     'quality' => 75,
 *     'temp_dir' => __DIR__ . '/../../temp/'
 * ]);
 * 
 * $avatarPath = $avatarProcessor->process('https://example.com/avatar.png');
 */
declare(strict_types=1);

class ImageProcessor
{
	/**
	 * Configuración predeterminada del procesador
	 * 
	 * @var array
	 */
	private const DEFAULT_CONFIG = [
		'type' => 'posts',               // Tipo de contenido (posts, perfil, fotos, etc.)
		'id' => 0,                       // ID del contenido asociado
		'storage_path' => '',            // Ruta base de almacenamiento (debe ser inyectada)
		'formats' => ['webp', 'avif', 'png'],   // Formatos de salida soportados
		'quality' => 80,                 // Calidad para formatos con pérdida (webp, avif)
		'max_size' => 10 * 1024 * 1024,  // Tamaño máximo de imagen (10MB)
		'allowed_mimes' => [             // MIME types permitidos
			'image/png', 
			'image/jpeg', 
			'image/gif', 
			'image/webp'
		],
		'temp_dir' => '',                // Directorio temporal personalizado
		'user_agent' => 'PHPost Image Processor/2.0', // User-Agent para descargas
		'timeout' => 15,                 // Tiempo máximo de descarga (segundos)
		'hash_length' => 16,             // Longitud del hash para nombres de carpeta
		'log_errors' => true             // Registrar errores en error_log
	];

	/**
	 * Tipos de contenido permitidos y sus rutas relativas
	 * 
	 * @var array
	 */
	private const CONTENT_TYPES = [
		'posts' => 'posts',
		'perfil' => 'perfil',
		'fotos' => 'fotos',
		'comentarios' => 'comentarios',
		'mensajes' => 'mensajes'
	];

	/**
	 * Configuración final aplicada al procesador
	 * 
	 * @var array
	 */
	private array $config;

	/**
	 * Ruta absoluta del directorio de almacenamiento
	 * 
	 * @var string
	 */
	private string $storageDir;

	/**
	 * Ruta absoluta del directorio temporal
	 * 
	 * @var string
	 */
	private string $tempDir;

	/**
	 * Ruta absoluta del directorio de destino para el contenido actual
	 * 
	 * @var string
	 */
	private string $targetDir;

	/**
	 * Constructor - Inicializa el procesador con configuración segura
	 *
	 * @param array $config Configuración personalizada:
	 *                      - type: string (tipo de contenido)
	 *                      - id: int (ID del recurso)
	 *                      - storage_path: string (ruta base de almacenamiento)
	 *                      - formats: array (formatos de salida)
	 *                      - quality: int (calidad de compresión)
	 *                      - max_size: int (tamaño máximo en bytes)
	 *                      - allowed_mimes: array (MIME types permitidos)
	 *                      - temp_dir: string (directorio temporal)
	 *                      - user_agent: string (User-Agent para descargas)
	 *                      - timeout: int (tiempo máximo de descarga)
	 *                      - hash_length: int (longitud del hash para carpetas)
	 *                      - log_errors: bool (registrar errores)
	 * 
	 * @throws \InvalidArgumentException Si la configuración es inválida
	 * @throws \RuntimeException Si los directorios no son accesibles
	 */
	public function __construct(array $config = [])
	{
		$this->validateConfig($config);
		$this->config = $this->mergeConfig($config);
		$this->setupDirectories();
	}

	/**
	 * Valida la configuración proporcionada
	 *
	 * @param array $config Configuración a validar
	 * @return void
	 * @throws \InvalidArgumentException Si la configuración es inválida
	 */
	private function validateConfig(array $config): void
	{
		// Validar tipo de contenido
		if (isset($config['type']) && !array_key_exists($config['type'], self::CONTENT_TYPES)) {
			throw new \InvalidArgumentException(
				"Tipo de contenido inválido: '{$config['type']}'. " .
				"Permitidos: " . implode(', ', array_keys(self::CONTENT_TYPES))
			);
		}

		// Validar ID
		if (isset($config['id']) && (!is_int($config['id']) || $config['id'] < 0)) {
			throw new \InvalidArgumentException("El ID debe ser un entero positivo");
		}

		// Validar storage_path
		if (isset($config['storage_path']) && !is_string($config['storage_path'])) {
			throw new \InvalidArgumentException("storage_path debe ser una cadena");
		}

		// Validar formats
		if (isset($config['formats']) && (!is_array($config['formats']) || empty($config['formats']))) {
			throw new \InvalidArgumentException("formats debe ser un array no vacío");
		}

		// Validar calidad
		if (isset($config['quality']) && ($config['quality'] < 0 || $config['quality'] > 100)) {
			throw new \InvalidArgumentException("quality debe estar entre 0 y 100");
		}

		// Validar tamaño máximo
		if (isset($config['max_size']) && (!is_int($config['max_size']) || $config['max_size'] <= 0)) {
			throw new \InvalidArgumentException("max_size debe ser un entero positivo");
		}

		// Validar MIME types
		if (isset($config['allowed_mimes']) && (!is_array($config['allowed_mimes']) || empty($config['allowed_mimes']))) {
			throw new \InvalidArgumentException("allowed_mimes debe ser un array no vacío");
		}

		// Validar directorio temporal
		if (isset($config['temp_dir']) && !is_string($config['temp_dir'])) {
			throw new \InvalidArgumentException("temp_dir debe ser una cadena");
		}

		// Validar user_agent
		if (isset($config['user_agent']) && !is_string($config['user_agent'])) {
			throw new \InvalidArgumentException("user_agent debe ser una cadena");
		}

		// Validar timeout
		if (isset($config['timeout']) && (!is_int($config['timeout']) || $config['timeout'] <= 0)) {
			throw new \InvalidArgumentException("timeout debe ser un entero positivo");
		}

		// Validar hash_length
		if (isset($config['hash_length']) && ($config['hash_length'] < 8 || $config['hash_length'] > 32)) {
			throw new \InvalidArgumentException("hash_length debe estar entre 8 y 32");
		}
	}

	/**
	 * Combina la configuración predeterminada con la personalizada
	 *
	 * @param array $config Configuración personalizada
	 * @return array Configuración final
	 */
	private function mergeConfig(array $config): array
	{
		$merged = array_merge(self::DEFAULT_CONFIG, $config);
		
		// Asegurar rutas absolutas
		if (empty($merged['storage_path'])) {
			$merged['storage_path'] = dirname(__DIR__, 2) . '/storage/media/';
		}
		
		if (empty($merged['temp_dir'])) {
			$merged['temp_dir'] = dirname(__DIR__, 2) . '/storage/temp/';
		}
		
		return $merged;
	}

	/**
	 * Configura los directorios necesarios
	 *
	 * @return void
	 * @throws \RuntimeException Si los directorios no son accesibles
	 */
	private function setupDirectories(): void
	{
		// Validar y crear storage_path
		$this->storageDir = rtrim($this->config['storage_path'], '/\\') . '/';
		if (!is_dir($this->storageDir) && !mkdir($this->storageDir, 0755, true) && !is_dir($this->storageDir)) {
			throw new \RuntimeException("No se pudo crear el directorio de almacenamiento: {$this->storageDir}");
		}
		
		// Validar y crear temp_dir
		$this->tempDir = rtrim($this->config['temp_dir'], '/\\') . '/';
		if (!is_dir($this->tempDir) && !mkdir($this->tempDir, 0755, true) && !is_dir($this->tempDir)) {
			$this->logError("No se pudo crear temp_dir: {$this->tempDir}. Usando sys_get_temp_dir()");
			$this->tempDir = sys_get_temp_dir() . '/';
		}
		
		// Crear directorio de destino
		$typeDir = self::CONTENT_TYPES[$this->config['type']];
		$this->targetDir = $this->storageDir . $typeDir . '/' . $this->generateTargetFolder() . '/';
		
		if (!is_dir($this->targetDir) && !mkdir($this->targetDir, 0755, true) && !is_dir($this->targetDir)) {
			throw new \RuntimeException("No se pudo crear el directorio de destino: {$this->targetDir}");
		}
	}

	/**
	 * Genera el nombre de la carpeta de destino basado en ID y hash
	 *
	 * @return string Nombre de la carpeta
	 */
	private function generateTargetFolder(): string
	{
		// Mantenemos el esquema: POST_N{id}
	   $id = max(1, $this->config['id']);
	   $folderName = "POST_N{$id}";
	   $folderHash = md5($folderName);
	   // Opción 1: Solo el hash (como en tu código original)
	   return $folderHash . '/';
	   // Opción 2: Con prefijo ID para legibilidad (recomendado)
	   // return "ID{$id}_{$folderHash}/";
	}

	/**
	 * Procesa una imagen remota y la convierte a los formatos configurados
	 *
	 * @param string $imageUrl URL de la imagen remota
	 * @return string Ruta absoluta del archivo procesado (primer formato disponible)
	 * @throws \RuntimeException Si ocurre un error durante el procesamiento
	 */
	public function process(string $imageUrl): string
	{
		// Validar URL
		if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException("URL inválida: {$imageUrl}");
		}

		// Descargar imagen
		$tempFile = $this->downloadImage($imageUrl);
		
		try {
			// Validar imagen
			$this->validateImage($tempFile);
			
			// Procesar a formatos configurados
			$outputFiles = $this->convertToFormats($tempFile);
			
			// Limpiar temporal
			unlink($tempFile);
			
			// Devolver primer archivo disponible
			return reset($outputFiles);
		} catch (\Exception $e) {
			unlink($tempFile);
			throw $e;
		}
	}

	/**
	 * Descarga una imagen remota de forma segura
	 *
	 * @param string $imageUrl URL de la imagen
	 * @return string Ruta del archivo temporal descargado
	 * @throws \RuntimeException Si la descarga falla
	 */
	private function downloadImage(string $imageUrl): string
	{
		$context = stream_context_create([
			'http' => [
				'timeout' => $this->config['timeout'],
				'user_agent' => $this->config['user_agent'],
				'header' => 'Accept: image/webp,image/apng,image/*,*/*;q=0.8'
			]
		]);

		// Generar nombre de archivo temporal seguro
		$tempFile = tempnam($this->tempDir, 'img_');
		if (!$tempFile) {
			$tempFile = $this->tempDir . 'img_' . bin2hex(random_bytes(8));
		}

		// Descargar con límite de tamaño
		$contentLength = 0;
		$content = '';
		
		$fp = fopen($imageUrl, 'rb', false, $context);
		if (!$fp) {
			throw new \RuntimeException("No se pudo conectar con el servidor remoto");
		}

		while (!feof($fp)) {
			$buffer = fread($fp, 8192);
			$contentLength += strlen($buffer ?? '');
			
			// Protección contra DoS
			if ($contentLength > $this->config['max_size']) {
				fclose($fp);
				throw new \RuntimeException("Imagen excede el tamaño máximo permitido");
			}
			
			$content .= $buffer;
		}
		fclose($fp);

		// Guardar contenido
		if (file_put_contents($tempFile, $content) === false) {
			throw new \RuntimeException("No se pudo guardar la imagen temporal");
		}

		return $tempFile;
	}

	/**
	 * Valida que el archivo sea una imagen segura
	 *
	 * @param string $file Ruta del archivo a validar
	 * @return void
	 * @throws \RuntimeException Si la validación falla
	 */
	private function validateImage(string $file): void
	{
		// Validar tamaño
		$size = filesize($file);
		if ($size === false || $size <= 0) {
			$this->logError("Archivo vacío o corrupto");
		}

		// Validar MIME type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		if (!$finfo) {
			$this->logError("No se pudo inicializar finfo");
		}
		
		$mimeType = finfo_file($finfo, $file);
		finfo_close($finfo);
		
		if (!$mimeType || !in_array($mimeType, $this->config['allowed_mimes'])) {
			$this->logError("Tipo de imagen no permitido: {$mimeType}");
		}

		// Bloquear imágenes con cabeceras malformadas
		if ($mimeType === 'image/gif') {
			$fh = fopen($file, 'rb');
			if ($fh) {
				$header = fread($fh, 6);
				fclose($fh);
				if ($header !== 'GIF87a' && $header !== 'GIF89a') {
					$this->logError("Cabecera GIF inválida");
				}
			}
		}

		// Validar dimensiones (protección contra imágenes gigantes)
		$dimensions = @getimagesize($file);
		if (!$dimensions || $dimensions[0] <= 0 || $dimensions[1] <= 0) {
			$this->logError("Dimensiones de imagen inválidas");
		}
		
		$maxPixels = 10000 * 10000; // 100MP
		if ($dimensions[0] * $dimensions[1] > $maxPixels) {
			$this->logError("Resolución de imagen excesiva");
		}
	}

	/**
	 * Convierte la imagen a los formatos configurados
	 *
	 * @param string $source Ruta del archivo fuente
	 * @return array Rutas de los archivos generados
	 * @throws \RuntimeException Si la conversión falla
	 */
	private function convertToFormats(string $source): array
	{
		$outputFiles = [];
		$fileName = pathinfo($source, PATHINFO_FILENAME);
		$fileHash = hash_file('sha256', $source);
		if ($fileHash === false) {
			$this->logError("No se pudo calcular hash del archivo");
		}
		
		// Verificar si ya existe (usando el hash como nombre base)
		$exists = false;
		foreach ($this->config['formats'] as $format) {
			$potentialPath = $this->targetDir . $fileHash . '.' . $format;
			if (file_exists($potentialPath)) {
				$exists = true;
				$outputFiles[] = $potentialPath;
			}
		}
		
		if ($exists) {
			return $outputFiles; // Reutilizar archivos existentes
		}
		
		$fileName = $fileHash;
		
		foreach ($this->config['formats'] as $format) {
			$destination = $this->targetDir . $fileName . '.' . $format;
			
			try {
				switch ($format) {
					case 'webp':
						$this->convertToWebp($source, $destination);
						$outputFiles[] = $destination;
						break;
						
					case 'avif':
						if ($this->isAvifSupported()) {
							$this->convertToAvif($source, $destination);
							$outputFiles[] = $destination;
						}
						break;
						
					case 'png':
						$this->convertToPng($source, $destination);
						$outputFiles[] = $destination;
						break;
				}
			} catch (\Exception $e) {
				$this->logError("Error convirtiendo a {$format}: {$e->getMessage()}");
			}
		}
		
		if (empty($outputFiles)) {
			throw new \RuntimeException("No se pudo generar ningún formato válido");
		}
		
		return $outputFiles;
	}

	/**
	 * Convierte a WebP
	 *
	 * @param string $source Ruta del archivo fuente
	 * @param string $destination Ruta de destino
	 * @return void
	 * @throws \RuntimeException Si la conversión falla
	 */
	private function convertToWebp(string $source, string $destination): void
	{
	    $image = $this->createImageFromSource($source);
	    
	    // Convertir a truecolor con manejo seguro de transparencia
	    if (!imageistruecolor($image)) {
	        $width = imagesx($image);
	        $height = imagesy($image);
	        $temp = imagecreatetruecolor($width, $height);
	        
	        // Configurar transparencia correcta
	        imagealphablending($temp, false);
	        imagesavealpha($temp, true);
	        
	        // Fondo 100% transparente
	        $transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);
	        imagefill($temp, 0, 0, $transparent);
	        
	        // Copiar respetando transparencia
	        imagecopy($temp, $image, 0, 0, 0, 0, $width, $height);
	        imagedestroy($image);
	        $image = $temp;
	    }

	    // Generar WebP con calidad configurada
	    if (!imagewebp($image, $destination, $this->config['quality'])) {
	        imagedestroy($image);
	        throw new \RuntimeException("Conversión WebP fallida");
	    }
	    
	    imagedestroy($image);
	    
	    // Validación RIFF CORRECTA (crítica para producción)
	    $header = file_get_contents($destination, false, null, 0, 12);
	    if ($header === false || strlen($header) < 12) {
	        unlink($destination);
	        throw new \RuntimeException("WebP corrupto: encabezado incompleto");
	    }
	    
	    if (substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WEBP') {
	        unlink($destination);
	        throw new \RuntimeException("WebP corrupto: formato incorrecto");
	    }
	}

	/**
	 * Convierte paleta a truecolor con soporte de transparencia
	 */
	private function paletteToTrueColor($image)
	{
		if (function_exists('imagepalettetotruecolor')) {
			imagepalettetotruecolor($image);
			return $image;
		}
		
		$width = imagesx($image);
		$height = imagesy($image);
		$temp = imagecreatetruecolor($width, $height);
		
		imagealphablending($temp, false);
		imagesavealpha($temp, true);
		$transparent = imagecolorallocatealpha($temp, 0, 0, 0, 127);
		imagefill($temp, 0, 0, $transparent);
		
		imagecopy($temp, $image, 0, 0, 0, 0, $width, $height);
		imagedestroy($image);
		
		return $temp;
	}

	/**
	 * Verifica si el WebP está visualmente corrupto (fondo blanco)
	 */
	private function isWebpCorrupted(string $file): bool
	{
		// Analizar primer píxel (debe ser transparente si venía de GIF)
		$checker = imagecreatefromwebp($file);
		if (!$checker) return true;
		
		$firstPixel = imagecolorat($checker, 0, 0);
		imagedestroy($checker);
		
		// Si alpha > 0 → no es transparente (corrupto para GIFs)
		return ($firstPixel >> 24) > 0;
	}

	/**
	 * Convierte a AVIF (si está soportado)
	 *
	 * @param string $source Ruta del archivo fuente
	 * @param string $destination Ruta de destino
	 * @return void
	 * @throws \RuntimeException Si la conversión falla
	 */
	private function convertToAvif(string $source, string $destination): void
	{
		$image = $this->createImageFromSource($source);
		if (!@imageavif($image, $destination, $this->config['quality'])) {
			imagedestroy($image);
			throw new \RuntimeException("Error al convertir a AVIF");
		}
		imagedestroy($image);
	}

	/**
	 * Convierte a PNG
	 *
	 * @param string $source Ruta del archivo fuente
	 * @param string $destination Ruta de destino
	 * @return void
	 * @throws \RuntimeException Si la conversión falla
	 */
	private function convertToPng(string $source, string $destination): void
	{
		$image = $this->createImageFromSource($source);
		if (!imagepng($image, $destination, 8)) {
			imagedestroy($image);
			throw new \RuntimeException("Error al convertir a PNG");
		}
		imagedestroy($image);
	}

	/**
	 * Crea un recurso de imagen desde una fuente
	 *
	 * @param string $source Ruta del archivo fuente
	 * @return resource Recurso de imagen
	 * @throws \RuntimeException Si la creación falla
	 */
	private function createImageFromSource(string $source)
	{
		$image = @imagecreatefromstring(file_get_contents($source));
		if (!$image) {
			throw new \RuntimeException("No se pudo crear el recurso de imagen");
		}
		return $image;
	}

	/**
	 * Verifica si AVIF está soportado
	 *
	 * @return bool True si AVIF está soportado
	 */
	private function isAvifSupported(): bool
	{
		return function_exists('imageavif') && defined('IMG_AVIF') && (imagetypes() & IMG_AVIF);
	}

	/**
	 * Obtiene la URL pública de una imagen procesada
	 *
	 * @param string $fileName Nombre del archivo (sin ruta)
	 * @return string URL pública
	 */
	public function getPublicUrl(string $fileName): string
	{
		$type = self::CONTENT_TYPES[$this->config['type']];
		return "/storage/media/{$type}/" . basename($this->targetDir) . '/' . $fileName;
	}

	/**
	 * Obtiene el nombre base de una imagen procesada (sin extensión)
	 *
	 * @param string $fileName Nombre completo con extensión (ej: "abc123.webp")
	 * @return string Nombre base (ej: "abc123")
	 */
	public function getBaseName(string $fileName): string
	{
		return pathinfo($fileName, PATHINFO_FILENAME);
	}

	/**
	 * Obtiene la ruta absoluta de una imagen procesada
	 *
	 * @param string $fileName Nombre del archivo (sin ruta)
	 * @return string Ruta absoluta
	 */
	public function getAbsolutePath(string $fileName): string
	{
		return $this->targetDir . $fileName;
	}

	/**
	 * Obtiene la configuración actual del procesador
	 *
	 * @return array Configuración sin valores sensibles
	 */
	public function getConfig(): array
	{
		return [
			'type' => $this->config['type'],
			'id' => $this->config['id'],
			'formats' => $this->config['formats'],
			'quality' => $this->config['quality'],
			'max_size' => $this->config['max_size'],
			'content_types' => self::CONTENT_TYPES
		];
	}

	/**
	 * Registra un error en los logs
	 *
	 * @param string $message Mensaje de error
	 * @return void
	 */
	private function logError(string $message): void
	{
		if ($this->config['log_errors']) {
			Logger::error("[ImageProcessor]", [
				'message' => $message
			], 'ImageProcessor');
		}
	}
}