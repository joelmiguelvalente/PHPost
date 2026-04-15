<?php

/**
 * @package   PHPost\Install\Steps
 * @copyright 2026
 */

declare(strict_types=1);

final class DatabaseHandler implements StepHandlerInterface
{
    public function __construct(
        private readonly ConfigWriter  $configWriter,
        private readonly InstallerLogger $logger,
    ) {}

    public function getName(): string { return 'base_de_datos'; }

    public function handle(InstallerRequest $request): StepResult
    {
        $defaults = ['hostname' => '', 'username' => '', 'password' => '', 'database' => ''];

        if (!$request->isPost()) {
            return StepResult::view(['db' => $defaults]);
        }

        $db = [
            'hostname' => $request->string('hostname'),
            'username' => $request->string('username'),
            'password' => $request->raw('password'),
            'database' => $request->string('database'),
        ];

        // Validación
        $v = new Validator();
        $v->required('hostname', $db['hostname'], 'Servidor')
          ->required('username', $db['username'], 'Usuario')
          ->required('database', $db['database'], 'Base de datos');

        // En localhost la contraseña puede estar vacía
        $isLocal = in_array(
            $_SERVER['SERVER_NAME'] ?? 'localhost',
            ['localhost', '127.0.0.1', '::1'],
            true
        );
        if (!$isLocal) {
            $v->required('password', $db['password'], 'Contraseña');
        }

        if ($v->fails()) {
            return StepResult::withError($v->firstError(), ['db' => $db]);
        }

        $localUse   = file_exists(dirname(__DIR__, 3) . '/.local') ? '.local' : '';
        $configPath = dirname(__DIR__, 3) . "/config/Config.Database{$localUse}.php";

        try {
            // 1. Probar conexión
            $conn = new InstallerDB($db['hostname'], $db['username'], $db['password'], $db['database']);

            // 2. Eliminar tablas existentes
            $tables = $conn->select('SHOW TABLES');
            foreach ($tables as $row) {
                $conn->dropTable(array_values($row)[0]);
            }

            // 3. Guardar credenciales en el archivo de config
            $this->configWriter->write($configPath, [
                'dbhost' => $db['hostname'],
                'dbuser' => $db['username'],
                'dbpass' => $db['password'],
                'dbname' => $db['database'],
            ]);

            // 4. Ejecutar SQL del schema
            $phpost_sql = [];
            require dirname(__DIR__, 2) . '/database.php';

            $failed = [];
            foreach ($phpost_sql as $key => $sql) {
                try {
                    $conn->execute($sql);
                } catch (Throwable $e) {
                    $failed[$key] = $e->getMessage();
                    $this->logger->error("SQL #{$key} falló", ['error' => $e->getMessage()]);
                }
            }

            if (!empty($failed)) {
                return StepResult::withError(
                    'Algunas tablas no pudieron crearse. Revisa el log de instalación.',
                    ['db' => $db]
                );
            }

            $this->logger->info('Base de datos configurada correctamente.', ['database' => $db['database']]);
            return StepResult::redirectTo('datos_phpmailer');

        } catch (Throwable $e) {
            $this->logger->error('Error en configuración de base de datos', ['error' => $e->getMessage()]);
            return StepResult::withError(
                'No se pudo conectar a la base de datos: ' . $e->getMessage(),
                ['db' => $db]
            );
        }
    }
}
