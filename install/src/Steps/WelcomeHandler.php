<?php

/**
 * @package   PHPost\Install\Steps
 * @copyright 2026
 */

declare(strict_types=1);

final class WelcomeHandler implements StepHandlerInterface
{
    public function getName(): string { return 'bienvenida'; }

    public function handle(InstallerRequest $request): StepResult
    {
        $licensePath = dirname(__DIR__, 3) . '/LICENSE';
        $license     = is_readable($licensePath)
            ? file_get_contents($licensePath)
            : 'No se encontró el archivo LICENSE.';

        if ($request->isPost()) {
            return StepResult::redirectTo('permisos');
        }

        return StepResult::view(['license' => $license]);
    }
}
