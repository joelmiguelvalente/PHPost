<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install\src\Contracts
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

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
