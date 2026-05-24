<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install\src\Steps
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class FinalizeHandler implements StepHandlerInterface
{
    public function __construct(
        private readonly LockManager     $lock,
        private readonly InstallerLogger  $logger,
    ) {}

    public function getName(): string { return 'finalizar'; }

    public function handle(InstallerRequest $request): StepResult
    {
        // Crear el .lock en cualquier GET del paso final
        if (!$this->lock->isLocked()) {
            try {
                $this->lock->lock();
                $this->logger->info('Instalación completada. Archivo .lock creado.');
            } catch (Throwable $e) {
                $this->logger->error('No se pudo crear el .lock', ['error' => $e->getMessage()]);
            }
        }

        // Obtener URL del sitio para el botón "Ir al sitio"
        try {
            $conn    = new InstallerDB(
                Config::db('hostname'),
                Config::db('username'),
                Config::db('password'),
                Config::db('database'),
            );
            $row     = $conn->selectOne('SELECT url FROM w_configuracion WHERE phpost_id = ?', [1]);
            $siteUrl = $row['url'] ?? '/';
        } catch (Throwable) {
            $siteUrl = '/';
        }

        // Si el usuario hace POST en el paso final → redirigir al sitio
        if ($request->isPost()) {
            header('Location: ' . $siteUrl);
            exit;
        }

        $installDir = basename(dirname(__DIR__, 1));
        return StepResult::view(['siteUrl' => $siteUrl, 'installDir' => $installDir]);
    }
}
