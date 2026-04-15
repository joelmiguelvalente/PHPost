<?php

/**
 * @package   PHPost\Install\Steps
 * @copyright 2026
 */

declare(strict_types=1);

final class PermissionsHandler implements StepHandlerInterface
{
    /**
     * Permisos por directorio según su propósito.
     * - 0755: lectura pública, escritura solo dueño (avatars, media, uploads)
     * - 0750: solo app, sin acceso público (cache, logs)
     * - 0700: acceso exclusivo al dueño (backups)
     */
    private const DIR_PERMISSIONS = [
        'avatar'  => 0755,
        'media'   => 0755,
        'uploads' => 0755,
        'cache'   => 0750,
        'logs'    => 0750,
        'backups' => 0700,
    ];

    private const DEFAULT_PERMISSION = 0755;

    public function getName(): string { return 'permisos'; }

    public function handle(InstallerRequest $request): StepResult
    {
        $paths  = Config::app('paths');
        $checks = [];
        $allOk  = true;

        foreach ($paths as $name => $route) {
            $expected = self::DIR_PERMISSIONS[$name] ?? self::DEFAULT_PERMISSION;

            if (!is_dir($route)) {
                mkdir($route, $expected, true);
            }

            $actual = (int) substr(sprintf('%o', @fileperms($route)), -3);
            $expectedOctal = (int) decoct($expected); // ej: 755, 750, 700
            $ok = is_writable($route); // más fiable que comparar chmod exacto

            $checks[$name] = [
                'route'    => str_replace(TS_STORAGE, '../storage', $route),
                'chmod'    => $actual,
                'expected' => $expectedOctal,
                'ok'       => $ok,
                'label'    => $ok
                    ? "Correcto"
                    : "Permisos incorrectos (necesita $expectedOctal)",
            ];

            if (!$ok) $allOk = false;
        }

        #if ($allOk && $request->isGet()) {
        #    return StepResult::redirectTo('base_de_datos');
        #}

        if ($request->isPost()) {
            if ($allOk) {
                return StepResult::redirectTo('base_de_datos');
            }
            return StepResult::withError(
                'Algunos directorios no tienen los permisos correctos. Corrígelos en tu FTP y vuelve a verificar.',
                ['checks' => $checks, 'allOk' => $allOk]
            );
        }

        return StepResult::view(['checks' => $checks, 'allOk' => $allOk]);
    }
}
