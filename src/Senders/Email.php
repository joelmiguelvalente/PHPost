<?php

declare(strict_types=1);

/**
 * @package    Senders
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

require_once TS_LIBS . '/phpmailer/src/PHPMailer.php';
require_once TS_LIBS . '/phpmailer/src/SMTP.php';
require_once TS_LIBS . '/phpmailer/src/Exception.php';

final class Email {

    private string $to        = '';
    public string  $asunto    = '';
    private string $body      = '';
    public string  $lastError = '';
    public array   $config    = [];
    public array   $headers   = [];
    private array  $subjects  = [];

    public function __construct(protected tsCore $Core) {
        $this->config = [
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

    /* API PÚBLICA */
    public function to(string $email): self {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido: ' . $email);
        }
        $this->to = $email;
        return $this;
    }

    public function subject(string $type): self {
        $notification = $this->buildNotification($type);
        $this->asunto = $notification['subject'];
        $this->headers = $notification['headers'] + $this->headers;
        return $this;
    }

    public function body(array $placeholders, string $type): self {
        $this->body = $this->wrapTemplate($placeholders, $type);
        return $this;
    }

    public function send(string $to, string $subject, mixed $body): bool {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Logger::warning('Intento de envío a email inválido', ['to' => $to]);
            return false;
        }
        $notification = $this->buildNotification($subject);

        try {
            $mail = $this->createMailer();
            $mail->addAddress($to);
            $mail->Subject = $notification['subject'];
            $mail->Body    = $body;

            foreach ($notification['headers'] + $this->headers as $key => $value) {
                $mail->addCustomHeader($key, $value);
            }
            $this->headers = [];

            $result = $mail->send();

            return $result;

        } catch (MailerException $e) {
            Logger::error('Fallo al enviar email', [
                'to'      => $to,
                'subject' => $notification['subject'],
                'error'   => $e->getMessage(),
                'host'    => $this->config['SMTP_HOST'],
            ]);
            return false;
        } catch (Throwable $e) {
            Logger::exception($e, 'mailer');
            return false;
        }
    }

    /* ATAJOS */
    public function sendFast(string $to, array $placeholders): bool {
        $type = $this->asunto;
        $this->body($placeholders, $type);
        return $this->send($to, $type, $this->body);
    }

    /* PRIVADOS */
    /**
     * Instancia y configura PHPMailer según el modo definido en settings:
     *   mail_mode = 'smtp'  → usa servidor SMTP externo (recomendado en producción)
     *   mail_mode = 'mail'  → usa la función mail() nativa de PHP (fallback)
     */
    private function createMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        $mail->CharSet = $this->config['SMTP_CHARSET'] ?? 'UTF-8';
        $mail->isHTML(true);

        $mail->setFrom(
            $this->config['SMTP_FROM'],
            $this->config['SMTP_FROM_NAME'] ?? 'PHPost'
        );

        // Timeout
        $mail->Timeout = (int)($this->config['SMTP_TIMEOUT'] ?? 10);

        // Debug SMTP (solo development)
        $smtpDebug = (bool)($this->config['SMTP_DEBUG'] ?? false);
        $mail->SMTPDebug = $smtpDebug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

        $mail->isSMTP();
        $mail->Host       = $this->config['SMTP_HOST'];
        $mail->Port       = (int)$this->config['SMTP_PORT'];
        $mail->SMTPAuth   = !empty($this->config['SMTP_USER']);
        $mail->Username   = $this->config['SMTP_USER'];
        $mail->Password   = $this->config['SMTP_PASS'];

        $mail->SMTPSecure = match (strtoupper($this->config['SMTP_SECURE'] ?? 'TLS')) {
            'SSL' => PHPMailer::ENCRYPTION_SMTPS,
            'TLS' => PHPMailer::ENCRYPTION_STARTTLS,
            default => '',
        };

        // En desarrollo, relajar verificación SSL
        if (Config::app('debug.active')) {
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        return $mail;
    }

    private function buildNotification(string $type = ''): array {
        if (empty($this->subjects)) {
            $this->subjects = require __DIR__ . '/subjects.php';
        }
        return $this->subjects[$type] ?? ['subject' => 'Notificación', 'headers' => []];
    }

    private function wrapTemplate(array $placeholders, string $type): string {
        $specific = __DIR__ . '/emails/' . $type . '.php';
        $fallback  = __DIR__ . '/emails/basic.php';
        $templatePath = is_file($specific) ? $specific : $fallback;

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template de email no encontrado: ' . $templatePath);
        }

        $html = file_get_contents($templatePath);
        $Routes = Container::get(Routes::class);
        $replace = [
            'TITLE'   => Container::get(Html::class)->escape($this->Core->settings['titulo']),
            'WEBSITE' => $Routes->route('url'),
            'TERMS'   => $Routes->absoluteUrl('url', 'pages/terminos-y-condiciones/'),
            'PRIV'    => $Routes->absoluteUrl('url', 'pages/privacidad/'),
            'LOGOWEB' => $Routes->absoluteUrl('url', 'assets/images/phpost/main-32.png'),
            ...$placeholders
        ];

        foreach ($replace as $key => $value) {
            $html = str_replace('{' . $key . '}', $value, $html);
        }

        return $html;
    }
}
