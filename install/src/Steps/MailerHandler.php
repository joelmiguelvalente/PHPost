<?php

/**
 * @package   PHPost\Install\Steps
 * @copyright 2026
 */

declare(strict_types=1);

final class MailerHandler implements StepHandlerInterface
{
    public function __construct(
        private readonly ConfigWriter   $configWriter,
        private readonly InstallerLogger $logger,
    ) {}

    public function getName(): string { return 'datos_phpmailer'; }

    public function handle(InstallerRequest $request): StepResult
    {
        $defaults = ['smtphost' => '', 'smtpuser' => '', 'smtppass' => '', 'smtpfrom' => ''];

        if (!$request->isPost()) {
            return StepResult::view(['mailer' => $defaults]);
        }

        // Botón "Omitir"
        if ($request->has('omitir')) {
            $this->logger->info('Paso PHPMailer omitido.');
            return StepResult::redirectTo('datos_sitio');
        }

        $mailer = [
            'smtphost' => $request->string('smtphost'),
            'smtpuser' => $request->string('smtpuser'),
            'smtppass' => $request->raw('smtppass'),
            'smtpfrom' => $request->string('smtpfrom'),
        ];

        $v = new Validator();
        $v->required('smtphost', $mailer['smtphost'], 'SMTP Host')
          ->smtpHost('smtphost', $mailer['smtphost'])
          ->required('smtpuser', $mailer['smtpuser'], 'SMTP Usuario')
          ->smtpUser('smtpuser', $mailer['smtpuser'])
          ->required('smtppass', $mailer['smtppass'], 'SMTP Contraseña')
          ->minLength('smtppass', $mailer['smtppass'], 6, 'SMTP Contraseña')
          ->required('smtpfrom', $mailer['smtpfrom'], 'Remitente')
          ->maxLength('smtpfrom', $mailer['smtpfrom'], 100, 'Remitente');

        if ($v->fails()) {
            return StepResult::withError($v->firstError(), ['mailer' => $mailer]);
        }

        $localUse   = file_exists(dirname(__DIR__, 2) . '/.local') ? '.local' : '';
        $configPath = dirname(__DIR__, 2) . "/config/Config.Mailer{$localUse}.php";

        try {
            $this->configWriter->write($configPath, [
                'smtphost' => $mailer['smtphost'],
                'smtpuser' => $mailer['smtpuser'],
                'smtppass' => $mailer['smtppass'],
                'smtpfrom' => $mailer['smtpfrom'],
            ]);

            $this->logger->info('PHPMailer configurado.', ['host' => $mailer['smtphost']]);
            return StepResult::redirectTo('datos_sitio');

        } catch (Throwable $e) {
            $this->logger->error('Error al guardar config de Mailer', ['error' => $e->getMessage()]);
            return StepResult::withError($e->getMessage(), ['mailer' => $mailer]);
        }
    }
}
