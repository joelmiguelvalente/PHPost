<?php

/**
 * ------------------------------------------------------------
 * StepRegistry
 * ------------------------------------------------------------
 * Registro centralizado de pasos del instalador.
 * Define el orden del flujo y proporciona los handlers.
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

final class StepRegistry
{
    /** Orden canónico del flujo de instalación */
    public const FLOW = [
        'bienvenida',
        'permisos',
        'base_de_datos',
        'datos_phpmailer',
        'datos_sitio',
        'datos_admin',
        'finalizar',
    ];

    /** Etiquetas para el menú de progreso */
    public const LABELS = [
        'bienvenida'      => 'Bienvenida',
        'permisos'        => 'Permisos',
        'base_de_datos'   => 'Base de datos',
        'datos_phpmailer' => 'PHPMailer',
        'datos_sitio'     => 'Datos del sitio',
        'datos_admin'     => 'Administrador',
        'finalizar'       => 'Finalizar',
    ];

    /** @var array<string, StepHandlerInterface> */
    private array $handlers = [];

    public function register(StepHandlerInterface $handler): void
    {
        $this->handlers[$handler->getName()] = $handler;
    }

    public function get(string $step): StepHandlerInterface
    {
        if (!isset($this->handlers[$step])) {
            throw new InvalidArgumentException("Paso desconocido: «{$step}»");
        }
        return $this->handlers[$step];
    }

    public function has(string $step): bool
    {
        return isset($this->handlers[$step]);
    }

    public function getFlow(): array
    {
        return self::FLOW;
    }

    public function getNextStep(string $current): ?string
    {
        $pos = array_search($current, self::FLOW, true);
        if ($pos === false) return null;
        return self::FLOW[$pos + 1] ?? null;
    }

    public function stepIndex(string $step): int
    {
        return (int) array_search($step, self::FLOW, true);
    }
}
