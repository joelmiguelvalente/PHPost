<?php

/**
 * ------------------------------------------------------------
 * StepHandlerInterface
 * ------------------------------------------------------------
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

interface StepHandlerInterface
{
    /**
     * Procesa la lógica del paso (GET o POST).
     * Devuelve un array de datos que el template necesita.
     */
    public function handle(InstallerRequest $request): StepResult;

    /**
     * Nombre interno del paso (slug).
     */
    public function getName(): string;
}
