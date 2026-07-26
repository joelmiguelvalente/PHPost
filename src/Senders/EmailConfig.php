<?php

declare(strict_types=1);

/**
 * @package    Senders
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

// Mailer
class EmailConfig {

    public function mailerConfig(): array {
        return [
            'SMTP_HOST'      => Config::mail('SMTP_HOST'),
            'SMTP_USER'      => Config::mail('SMTP_USER'),
            'SMTP_PASS'      => Config::mail('SMTP_PASS'),
            'SMTP_FROM'      => Config::mail('SMTP_FROM'),
            'SMTP_FROM_NAME' => Config::mail('SMTP_FROM_NAME') ?? 'PHPost team',
            'SMTP_SECURE'    => Config::mail('SMTP_SECURE'),
            'SMTP_PORT'      => Config::mail('SMTP_PORT'),
            'SMTP_CHARSET'   => Config::mail('SMTP_CHARSET') ?? 'UTF-8',
            'SMTP_TIMEOUT'   => Config::mail('SMTP_TIMEOUT') ?? 10,
            'SMTP_DEBUG'     => Config::mail('SMTP_DEBUG') ?? false,
        ];
    }

    public function getMailerData(): array {
        return [
            'SMTP_HOST'      => $_POST['SMTP_HOST']      ?? '',
            'SMTP_USER'      => $_POST['SMTP_USER']      ?? '',
            'SMTP_PASS'      => $_POST['SMTP_PASS']      ?? '',
            'SMTP_FROM'      => $_POST['SMTP_FROM']      ?? '',
            'SMTP_FROM_NAME' => $_POST['SMTP_FROM_NAME']  ?? '',
            'SMTP_SECURE'    => $_POST['SMTP_SECURE']     ?? 'tls',
            'SMTP_PORT'      => $_POST['SMTP_PORT']       ?? '587',
            'SMTP_CHARSET'   => $_POST['SMTP_CHARSET']    ?? 'UTF-8',
            'SMTP_TIMEOUT'   => $_POST['SMTP_TIMEOUT']    ?? '10',
            'SMTP_DEBUG'     => isset($_POST['SMTP_DEBUG']) ? '1' : '0',
        ];
    }

    public function validateMailerData(): array {
        $data = $this->getMailerData();
        $errors = [];

        if (empty($data['SMTP_HOST'])) {
            $errors['SMTP_HOST'] = 'El servidor SMTP es requerido.';
        }
        if (empty($data['SMTP_USER']) || !filter_var($data['SMTP_USER'], FILTER_VALIDATE_EMAIL)) {
            $errors['SMTP_USER'] = 'El usuario debe ser un correo válido.';
        }
        if (empty($data['SMTP_PASS'])) {
            $errors['SMTP_PASS'] = 'La contraseña es requerida.';
        }
        if (empty($data['SMTP_FROM']) || !filter_var($data['SMTP_FROM'], FILTER_VALIDATE_EMAIL)) {
            $errors['SMTP_FROM'] = 'El remitente debe ser un correo válido.';
        }
        // SMTP_FROM_NAME — opcional, sin validación forzosa

        if (!in_array($data['SMTP_SECURE'], ['tls', 'ssl', ''], true)) {
            $errors['SMTP_SECURE'] = 'El cifrado debe ser tls, ssl o vacío.';
        }
        if (empty($data['SMTP_PORT']) || !is_numeric($data['SMTP_PORT'])) {
            $errors['SMTP_PORT'] = 'El puerto debe ser un número.';
        }
        // SMTP_CHARSET — opcional, si se provee validar formato básico
        if (!empty($data['SMTP_CHARSET']) && !preg_match('/^[A-Za-z0-9_-]+$/', $data['SMTP_CHARSET'])) {
            $errors['SMTP_CHARSET'] = 'El charset tiene un formato inválido.';
        }
        // SMTP_TIMEOUT — opcional, debe ser numérico >= 1
        if (!empty($data['SMTP_TIMEOUT']) && (!is_numeric($data['SMTP_TIMEOUT']) || (int)$data['SMTP_TIMEOUT'] < 1)) {
            $errors['SMTP_TIMEOUT'] = 'El timeout debe ser un número mayor a 0.';
        }
        // SMTP_DEBUG — opcional bool, se acepta 0/1
        if (!in_array($data['SMTP_DEBUG'], ['0', '1'], true)) {
            $errors['SMTP_DEBUG'] = 'El modo debug debe ser 0 o 1.';
        }

        return $errors;
    }

    public function saveMailerConfig(): bool {
        $isLocal = Config::app('debug.active');
        $filePath = TS_CONFIG . '/Config.Mailer' . ($isLocal ? '.local' : '') . '.php';
        $data = $this->getMailerData();

        // Normalizar tipos antes de guardar
        $data['SMTP_PORT']    = (int)$data['SMTP_PORT'];
        $data['SMTP_TIMEOUT'] = (int)$data['SMTP_TIMEOUT'];
        $data['SMTP_DEBUG']   = $data['SMTP_DEBUG'] === '1';

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($data, true) . ";\n";

        return (bool) file_put_contents($filePath, $content);
    }

}
