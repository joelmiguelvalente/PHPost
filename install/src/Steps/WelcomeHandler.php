<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install\src\Steps
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

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
