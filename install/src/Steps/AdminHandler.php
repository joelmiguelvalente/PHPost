<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install\src\Steps
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class AdminHandler implements StepHandlerInterface
{
    public function __construct(
        private readonly string          $siteUrl,
        private readonly PasswordHandler $password,
        private readonly InstallerLogger  $logger,
    ) {}

    public function getName(): string { return 'datos_admin'; }

    public function handle(InstallerRequest $request): StepResult
    {
        $defaults = ['user_name' => '', 'user_email' => ''];

        if (!$request->isPost()) {
            return StepResult::view(['user' => $defaults]);
        }

        $input = [
            'user_name'     => $request->string('user_name'),
            'user_email'    => $request->string('user_email'),
            'user_password' => $request->raw('user_password'),
            'user_confirm'  => $request->raw('user_confirm'),
        ];

        // Validación
        $minLen = Config::app('security.password.min_length') ?? 8;
        $v = new Validator();
        $v->required('user_name',     $input['user_name'],     'Nombre de usuario')
          ->alphanumeric('user_name',  $input['user_name'],     'Nombre de usuario')
          ->maxLength('user_name',     $input['user_name'], 30, 'Nombre de usuario')
          ->required('user_email',     $input['user_email'],    'Email')
          ->email('user_email',        $input['user_email'],    'Email')
          ->required('user_password',  $input['user_password'], 'Contraseña')
          ->minLength('user_password', $input['user_password'], $minLen, 'Contraseña')
          ->matches('user_confirm',    $input['user_password'], $input['user_confirm'], 'Contraseñas');

        if ($v->fails()) {
            return StepResult::withError($v->firstError(), ['user' => $input]);
        }

        // Comprobar fortaleza de contraseña
        if (!$this->password->isStrong($input['user_password'])) {
            return StepResult::withError(
                'La contraseña debe tener al menos una mayúscula, un número y un carácter especial.',
                ['user' => $input]
            );
        }

        try {
            $conn = new InstallerDB(
                Config::db('hostname'),
                Config::db('username'),
                Config::db('password'),
                Config::db('database'),
            );

            // Prevenir doble registro de admin
            if ($conn->exists('SELECT 1 FROM u_miembros WHERE user_rango = ? LIMIT 1', [1])) {
                $this->logger->warning('Intento de registrar un segundo administrador.');
                return StepResult::withError(
                    'Ya existe un administrador registrado. No se puede continuar.',
                    ['user' => $input]
                );
            }

            $hash   = $this->password->create($input['user_password']);
            $userId = $conn->insert('u_miembros', [
                'user_name'       => $input['user_name'],
                'user_password'   => $hash,
                'user_email'      => $input['user_email'],
                'user_rango'      => 1,
                'user_registro'   => time(),
                'user_puntosxdar' => 50,
                'user_activo'     => 1,
            ]);

            // Tablas relacionadas
            foreach (['u_perfil', 'u_portal', 'u_miembros_sets'] as $table) {
                $conn->insert($table, ['user_id' => $userId]);
            }

            // Stats de fundación
            $conn->update('w_stats', [
                'stats_time_foundation' => time(),
                'stats_time_upgrade'    => time(),
            ], 'stats_no = ?', [1]);

            // Generar avatar
            require_once dirname(__DIR__, 3) . '/src/Utils/Avatar.php';
            $Avatar = new Avatar($this->siteUrl . '/storage/avatar/', true);
            $Avatar->ensure($userId, $input['user_name'], '#D6030B');

            $this->logger->info('Administrador creado.', ['user_id' => $userId, 'username' => $input['user_name']]);
            return StepResult::redirectTo('finalizar');

        } catch (Throwable $e) {
            $this->logger->error('Error al crear administrador', ['error' => $e->getMessage()]);
            return StepResult::withError($e->getMessage(), ['user' => $input]);
        }
    }
}
