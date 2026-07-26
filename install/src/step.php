<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
*/

final class Step {

    public const FLOW = [
        'bienvenida',
        'permisos',
        'base_de_datos',
        'datos_phpmailer',
        'datos_sitio',
        'datos_admin',
        'finalizar'
    ];

    public const LABELS = [
        'bienvenida' => 'Bienvenida',
        'permisos' => 'Permisos',
        'base_de_datos' => 'Base de datos',
        'datos_phpmailer' => 'PHPMailer',
        'datos_sitio' => 'Datos del sitio',
        'datos_admin' => 'Administrador',
        'finalizar' => 'Finalizar'
    ];

    private string $current;

    private ?string $lastCompleted = null;

    public function __construct(string $current)
    {
        $current = trim($current);

        if (!isset(self::LABELS[$current])) {
            throw new InvalidArgumentException("Paso desconocido: {$current}");
        }

        $this->current = $current;
        $this->lastCompleted = $_SESSION['install_step'] ?? $this->first();
    }

    /**
     * Paso actual.
     */
    public function current(): string
    {
        return $this->current;
    }

    /**
     * Nombre legible del paso.
     */
    public function label(?string $page = null): string
    {
        $page ??= $this->current;
        return self::LABELS[$page] ?? $page;
    }

    /**
     * Índice dentro del flujo.
     */
    private function index(?string $page = null): int
    {
        $page ??= $this->current;

        $index = array_search($page, self::FLOW, true);

        if ($index === false) {
            throw new InvalidArgumentException("Paso desconocido: {$page}");
        }

        return $index;
    }

    /**
     * Paso siguiente.
     */
    public function next(?string $page = null): ?string
    {
        $index = $this->index($page);
        return self::FLOW[$index + 1] ?? null;
    }

    /**
     * Paso anterior.
     */
    public function previous(?string $page = null): ?string
    {
        $index = $this->index($page);
        return self::FLOW[$index - 1] ?? null;
    }

    /**
     * Primer paso.
     */
    public function first(): string
    {
        return self::FLOW[0];
    }

    /**
     * Último paso.
     */
    public function last(): string
    {
        return self::FLOW[array_key_last(self::FLOW)];
    }

    /**
     * ¿Es el primer paso?
     */
    public function isFirst(?string $page = null): bool
    {
        return $this->index($page) === 0;
    }

    /**
     * ¿Es el último paso?
     */
    public function isLast(?string $page = null): bool
    {
        return $this->index($page) === array_key_last(self::FLOW);
    }

    /**
     * ¿Es un paso intermedio?
     */
    public function isIntermediate(?string $page = null): bool
    {
        $index = $this->index($page);
        return $index > 0 && $index < array_key_last(self::FLOW);
    }

    /**
     * URL de un paso.
     */
    public function url(?string $page = null): string
    {
        $page ??= $this->current;
        return 'index.php?step=' . $page;
    }

    /**
     * URL del siguiente paso.
     */
    public function nextUrl(): ?string
    {
        $next = $this->next();
        return $next ? $this->url($next) : null;
    }

    /**
     * URL del paso anterior.
     */
    public function previousUrl(): ?string
    {
        $previous = $this->previous();
        return $previous ? $this->url($previous) : null;
    }

    /**
     * ¿Existe el paso?
     */
    public static function exists(string $page): bool
    {
        return isset(self::LABELS[$page]);
    }

    /**
     * Todos los pasos.
     */
    public static function all(): array
    {
        return self::FLOW;
    }

    /**
     * ¿Se puede acceder al paso actual?
     */
    public function canAccess(?string $lastCompleted = null): bool
    {
        $last = $lastCompleted ?? $this->lastCompleted;
        $currentIndex = $this->index($this->current);
        $lastIndex = $this->index($last);

        // Solo puede acceder al siguiente paso o a uno anterior
        return $currentIndex <= $lastIndex + 1;
    }

    /**
     * Obtiene todos los pasos completados hasta ahora
     */
    public function getCompletedSteps(): array
    {
        $lastIndex = $this->index($this->lastCompleted);
        return array_slice(self::FLOW, 0, $lastIndex + 1);
    }

    /**
     * Obtiene los pasos pendientes
     */
    public function getPendingSteps(): array
    {
        $lastIndex = $this->index($this->lastCompleted);
        return array_slice(self::FLOW, $lastIndex + 1);
    }

    /**
     * Verifica si un paso está completado
     */
    public function isCompleted(string $page): bool
    {
        return $this->index($page) <= $this->index($this->lastCompleted);
    }

    /**
     * Obtiene el progreso en porcentaje
     */
    public function progress(): int
    {
        $total = count(self::FLOW) - 1;
        $current = $this->index($this->current);
        return (int) round(($current / $total) * 100);
    }

    /**
     * __toString para compatibilidad
     */
    public function __toString(): string
    {
        return $this->current;
    }
}
