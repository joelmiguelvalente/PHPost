<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install\src\Steps
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class SiteHandler implements StepHandlerInterface
{
    public function __construct(
        private readonly string        $baseUrl,
        private readonly Extras        $extras,
        private readonly InstallerLogger $logger,
    ) {}

    public function getName(): string { return 'datos_sitio'; }

    public function handle(InstallerRequest $request): StepResult
    {
        $defaults = [
            'titulo' => '',
            'slogan' => '',
            'email'  => '',
            'url'    => $this->baseUrl,
            'skey'   => '',
            'pkey'   => '',
        ];

        if (!$request->isPost()) {
            return StepResult::view(['site' => $defaults]);
        }

        $site = [
            'titulo' => $request->string('titulo'),
            'slogan' => $request->string('slogan'),
            'email'  => $request->string('email'),
            'url'    => $request->string('url'),
            'skey'   => $request->string('skey'),
            'pkey'   => $request->string('pkey'),
        ];

        $v = new Validator();
        $v->required('titulo', $site['titulo'], 'Nombre del sitio')
          ->maxLength('titulo', $site['titulo'], 100, 'Nombre del sitio')
          ->required('slogan', $site['slogan'], 'Lema')
          ->required('email',  $site['email'],  'Email')
          ->email('email',     $site['email'],  'Email')
          ->required('url',    $site['url'],    'URL')
          ->url('url',         $site['url'],    'URL');

        if ($v->fails()) {
            return StepResult::withError($v->firstError(), ['site' => $site]);
        }

        try {
            $conn = new InstallerDB(
                Config::db('hostname'),
                Config::db('username'),
                Config::db('password'),
                Config::db('database'),
            );

            // Seguridad: verificar que la DB está configurada y no hay admin aún
            if (Config::db('hostname') === 'dbhost') {
                return StepResult::withError(
                    'Vuelve al paso de base de datos; las credenciales no se guardaron correctamente.',
                    ['site' => $site]
                );
            }

            $seo     = $this->extras->slugify($site['titulo']);
            $version = Config::app('app.name') . ' ' . Config::app('app.version');

            $conn->update('p_categorias', ['c_nombre' => $site['titulo'], 'c_seo' => $seo], 'cid = ?', [30]);
            $conn->update('w_registro',   ['public_key' => $site['pkey'], 'secret_key' => $site['skey']], 'reg_id = ?', [1]);
            $conn->update('w_configuracion', [
                'titulo'        => $site['titulo'],
                'slogan'        => $site['slogan'],
                'url'           => rtrim($site['url'], '/'),
                'email'         => $site['email'],
                'tema'          => 'default',
                'version'       => $version,
                'version_code'  => $this->extras->slugify($version, '_'),
            ], 'phpost_id = ?', [1]);

            $this->logger->info('Datos del sitio guardados.', ['titulo' => $site['titulo']]);
            return StepResult::redirectTo('datos_admin');

        } catch (Throwable $e) {
            $this->logger->error('Error en datos del sitio', ['error' => $e->getMessage()]);
            return StepResult::withError($e->getMessage(), ['site' => $site]);
        }
    }
}
