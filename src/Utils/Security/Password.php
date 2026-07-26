<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Security
 * @author     Miguel92
 * @copyright  2026
 */

/**
 * Manejador de contraseñas seguro
 *
 * Proporciona métodos para hash, verificación y validación
 * de contraseñas utilizando las configuraciones de seguridad
 * definidas en Config.Application.php
 */
class Password
{
    /** @var string Algoritmo de hash actual */
    private string $algorithm;

    /** @var array Opciones del algoritmo */
    private array $options;

    /** @var int Longitud mínima de contraseña */
    private int $minLength;

    /** @var int Longitud máxima de contraseña */
    private int $maxLength;

    /** @var bool Requerir mayúsculas y minúsculas */
    private bool $requireMixedCase;

    /** @var bool Requerir números */
    private bool $requireNumbers;

    /** @var bool Requerir caracteres especiales */
    private bool $requireSpecialChars;

    /** @var int Número de contraseñas anteriores a recordar */
    private int $history;

    /** @var int Días de expiración de contraseña */
    private int $expiryDays;

    /** @var array|null Cache de contraseñas anteriores */
    private ?array $passwordHistory = null;

    /**
     * Constructor - Carga configuración de seguridad
     */
    public function __construct()
    {
        try {
            $this->loadConfiguration();
        } catch (\Throwable $e) {
            // Fallback a valores seguros por defecto
            $this->algorithm = PASSWORD_ARGON2ID;
            $this->options = [
                'memory_cost' => 1 << 17,
                'time_cost' => 4,
                'threads' => 2,
            ];
            $this->minLength = 8;
            $this->maxLength = 72;
            $this->requireMixedCase = true;
            $this->requireNumbers = true;
            $this->requireSpecialChars = false;
            $this->history = 5;
            $this->expiryDays = 90;
        }
    }

    /**
     * Carga la configuración desde Config.Application.php
     */
    private function loadConfiguration(): void
    {
        $config = Config::app('security.password');

        // Configuración del algoritmo
        $this->algorithm = $config['algorithm'] ?? PASSWORD_ARGON2ID;

        // Opciones del algoritmo con valores por defecto seguros
        $this->options = $config['options'] ?? [
            'memory_cost' => 1 << 17,
            'time_cost' => 4,
            'threads' => 2,
        ];

        // Validar opciones según el algoritmo
        $this->validateOptions();

        // Políticas de contraseña
        $this->minLength = $config['min_length'] ?? 8;
        $this->maxLength = $config['max_length'] ?? 72;
        $this->requireMixedCase = $config['require_mixed_case'] ?? true;
        $this->requireNumbers = $config['require_numbers'] ?? true;
        $this->requireSpecialChars = $config['require_special_chars'] ?? false;
        $this->history = $config['history'] ?? 5;
        $this->expiryDays = $config['expiry_days'] ?? 90;
    }

    /**
     * Valida y ajusta las opciones según el algoritmo
     */
    private function validateOptions(): void
    {
        if ($this->algorithm === PASSWORD_ARGON2ID) {
            // Valores mínimos para Argon2id
            $this->options['memory_cost'] = max(1 << 10, $this->options['memory_cost'] ?? 1 << 17);
            $this->options['time_cost'] = max(1, $this->options['time_cost'] ?? 4);
            $this->options['threads'] = max(1, $this->options['threads'] ?? 2);

            // Limitar valores máximos razonables
            $this->options['memory_cost'] = min(1 << 20, $this->options['memory_cost']);
            $this->options['time_cost'] = min(10, $this->options['time_cost']);
            $this->options['threads'] = min(8, $this->options['threads']);
        } elseif ($this->algorithm === PASSWORD_BCRYPT) {
            // Para Bcrypt, solo usamos 'cost'
            $cost = $this->options['cost'] ?? 12;
            $cost = max(4, min(31, $cost));
            $this->options = ['cost' => $cost];
        }
    }

    /**
     * Genera un hash seguro de la contraseña
     *
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     * @return string Username
     * @throws \InvalidArgumentException Si la contraseña no cumple los requisitos
     */
    public function create(string $password, string $username = ''): string
    {
        // Validar fortaleza antes de hashear
        if (!$this->isStrong($password)) {
            throw new \InvalidArgumentException('La contraseña no cumple con los requisitos mínimos de seguridad');
        }

        if ($password === $username) {
            throw new \InvalidArgumentException('La contraseña no debe ser igual al nickname');
        }

        $hash = password_hash($password, $this->algorithm, $this->options);

        if ($hash === false) {
            throw new \RuntimeException('Error al generar el hash de la contraseña');
        }

        return $hash;
    }

    /**
     * Verifica una contraseña contra su hash
     *
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool True si coincide
     */
    public function verify(string $password, string $hash): bool
    {
        if (empty($password) || empty($hash)) {
            return false;
        }

        // Verificar si el hash es válido primero
        if ($this->isValidHash($hash) === false) {
            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * Verifica si el hash es válido
     * Soporta todos los algoritmos compatibles con PHP
	 */
	public function isValidHash(string $hash): bool
	{
	    if (empty($hash)) {
	        return false;
	    }

	    // Normalizar el hash (eliminar espacios en blanco)
	    $hash = trim($hash);

	    // Verificar formato según el algoritmo
	    $isValidFormat = false;

	    // Argon2id (PHP 7.2+)
	    if (strpos($hash, '$argon2id$') === 0) {
	        $isValidFormat = true;
	        $minLength = 80;
	    // Argon2i (PHP 7.2+)
	    } elseif (strpos($hash, '$argon2i$') === 0) {
	        $isValidFormat = true;
	        $minLength = 80;
	    // Argon2d (PHP 7.2+)
	    } elseif (strpos($hash, '$argon2d$') === 0) {
	        $isValidFormat = true;
	        $minLength = 80;
	    // Bcrypt (PHP 5.5+)
	    } elseif (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0 || strpos($hash, '$2x$') === 0) {
	        $isValidFormat = true;
	        $minLength = 60;
	    }

	    if (!$isValidFormat) {
	        return false;
	    }

	    // Verificar longitud mínima
	    if (strlen($hash) < $minLength) {
	        return false;
	    }

	    // Usar password_get_info para verificar si es un hash válido
	    $info = password_get_info($hash);

	    // password_get_info devuelve:
	    // algo: 0 = desconocido, 1 = Bcrypt, 2 = Argon2i, 3 = Argon2id
	    return $info['algo'] !== 0 && $info['algo'] !== null;
	}

    /**
     * Comprueba si un hash necesita ser actualizado
     *
     * @param string $hash Hash actual
     * @return bool True si necesita rehash
     */
    public function needsRehash(string $hash): bool
    {
        if (empty($hash)) {
            return true;
        }

        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }

    /**
     * Verifica si dos contraseñas coinciden (seguro contra timing attacks)
     *
     * @param string $password1 Primera contraseña
     * @param string $password2 Segunda contraseña
     * @return bool True si son iguales
     */
    public function equals(string $password1, string $password2): bool
    {
        if (empty($password1) || empty($password2)) {
            return false;
        }

        return hash_equals($password1, $password2);
    }

    /**
     * Verifica si la contraseña cumple con los requisitos mínimos
     *
     * @param string $password Contraseña a validar
     * @return bool True si cumple los requisitos
     */
    public function isStrong(string $password): bool
    {
        // Validar longitud
        $length = strlen($password);
        if ($length < $this->minLength || $length > $this->maxLength) {
            return false;
        }

        // Validar mayúsculas/minúsculas
        if ($this->requireMixedCase) {
            if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
                return false;
            }
        }

        // Validar números
        if ($this->requireNumbers && !preg_match('/\d/', $password)) {
            return false;
        }

        // Validar caracteres especiales
        if ($this->requireSpecialChars && !preg_match('/[\W_]/', $password)) {
            return false;
        }

        // Validar caracteres comunes (prevenir contraseñas débiles)
        $commonPasswords = $this->getCommonPasswords();
        if (in_array(strtolower($password), $commonPasswords)) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene las fortalezas de la contraseña
     *
     * @param string $password Contraseña a evaluar
     * @return array<string, bool|int> Detalles de fortaleza
     */
    public function getStrengthDetails(string $password): array
    {
        return [
            'length' => strlen($password),
            'min_length' => $this->minLength,
            'max_length' => $this->maxLength,
            'has_uppercase' => (bool) preg_match('/[A-Z]/', $password),
            'has_lowercase' => (bool) preg_match('/[a-z]/', $password),
            'has_number' => (bool) preg_match('/\d/', $password),
            'has_special' => (bool) preg_match('/[\W_]/', $password),
            'is_common' => in_array(strtolower($password), $this->getCommonPasswords()),
            'entropy' => $this->calculateEntropy($password),
            'is_strong' => $this->isStrong($password),
        ];
    }

    /**
     * Calcula la entropía de la contraseña (medida de fortaleza)
     *
     * @param string $password Contraseña
     * @return float Entropía en bits
     */
    public function calculateEntropy(string $password): float
    {
        $charset = 0;

        if (preg_match('/[a-z]/', $password)) $charset += 26;
        if (preg_match('/[A-Z]/', $password)) $charset += 26;
        if (preg_match('/\d/', $password)) $charset += 10;
        if (preg_match('/[\W_]/', $password)) $charset += 32;

        if ($charset === 0) {
            return 0;
        }

        return strlen($password) * log($charset, 2);
    }

    /**
     * Permite usar Bcrypt (para compatibilidad)
     *
     * @param int $cost Costo del hash (4-31)
     */
    public function useBcrypt(int $cost = 12): void
    {
        $cost = max(4, min(31, $cost));
        $this->algorithm = PASSWORD_BCRYPT;
        $this->options = ['cost' => $cost];
    }

    /**
     * Permite ajustar las opciones de Argon2id manualmente
     *
     * @param int $memory Costo de memoria (en KiB)
     * @param int $time Costo de tiempo
     * @param int $threads Número de hilos
     */
    public function setArgonOptions(int $memory, int $time, int $threads): void
    {
        $this->algorithm = PASSWORD_ARGON2ID;
        $this->options = [
            'memory_cost' => max(1 << 10, min(1 << 20, $memory)),
            'time_cost' => max(1, min(10, $time)),
            'threads' => max(1, min(8, $threads)),
        ];
    }

    /**
     * Verifica si la contraseña está expirada
     *
     * @param int $createdAt Timestamp de creación
     * @return bool True si expiró
     */
    public function isExpired(int $createdAt): bool
    {
        if ($this->expiryDays <= 0) {
            return false;
        }

        $expiryTime = $createdAt + ($this->expiryDays * 86400);
        return time() > $expiryTime;
    }

    /**
     * Guarda el historial de contraseñas
     *
     * @param int $userId ID del usuario
     * @param string $hash Hash de la contraseña
     * @param array $history Historial existente (opcional)
     */
    public function saveHistory(int $userId, string $hash, array $history = []): void
    {
        if ($this->history <= 0) {
            return;
        }

        // Añadir nuevo hash al historial
        $history[] = [
            'hash' => $hash,
            'created_at' => time(),
        ];

        // Mantener solo las últimas N contraseñas
        if (count($history) > $this->history) {
            $history = array_slice($history, -$this->history);
        }

        $this->passwordHistory = $history;
    }

    /**
     * Verifica si la contraseña ya fue usada anteriormente
     *
     * @param string $password Contraseña a verificar
     * @param array $history Historial de contraseñas
     * @return bool True si ya fue usada
     */
    public function isReused(string $password, array $history): bool
    {
        if (empty($history) || $this->history <= 0) {
            return false;
        }

        foreach ($history as $entry) {
            if (isset($entry['hash']) && $this->verify($password, $entry['hash'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera una contraseña aleatoria segura
     *
     * @param int $length Longitud de la contraseña
     * @param bool $includeSpecial Incluir caracteres especiales
     * @return string Contraseña generada
     */
    public function generate(int $length = 16, bool $includeSpecial = true): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($includeSpecial) {
            $characters .= '!@#$%^&*()_-+=<>?';
        }

        $password = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }

    /**
     * Obtiene las contraseñas más comunes (para validación)
     *
     * @return array Lista de contraseñas comunes
     */
    private function getCommonPasswords(): array
    {
        return [
            'password', '123456', '123456789', '12345', '12345678',
            '1234567', 'password1', '1234567890', '1234', 'admin',
            'qwerty', 'letmein', 'welcome', 'monkey', 'abc123',
            'dragon', 'master', 'login', 'passw0rd', 'shadow',
            '123123', '1q2w3e', 'qwertyuiop', 'admin123', 'iloveyou',
            '654321', '123321', 'qwerty123', 'password123', '123456a',
            '111111', '123456abc', 'password1234', 'qwerty1234', '987654321'
        ];
    }

    /**
     * Obtiene información del hash (algoritmo, opciones)
     *
     * @param string $hash Hash a analizar
     * @return array Información del hash
     */
    public function getHashInfo(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * Obtiene el algoritmo actual
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Obtiene las opciones actuales
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Obtiene la configuración de políticas
     */
    public function getPolicies(): array
    {
        return [
            'min_length' => $this->minLength,
            'max_length' => $this->maxLength,
            'require_mixed_case' => $this->requireMixedCase,
            'require_numbers' => $this->requireNumbers,
            'require_special_chars' => $this->requireSpecialChars,
            'history' => $this->history,
            'expiry_days' => $this->expiryDays,
        ];
    }
}
