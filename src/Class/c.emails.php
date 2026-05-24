<?php

declare(strict_types=1);

/**
 * @package    PHPost/Class
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

require_once TS_LIBS . '/phpmailer/src/PHPMailer.php';
require_once TS_LIBS . '/phpmailer/src/SMTP.php';
require_once TS_LIBS . '/phpmailer/src/Exception.php';

final class tsEmail {

	private string $to        = '';
	public string $asunto     = '';
	private string $body      = '';
	public string  $lastError = '';
	public array   $config    = [];

	public function __construct(protected tsCore $Core) {
		$this->config = [
			'mail_mode' => 'smtp',
			'smtp_host' => Config::mail('SMTP_HOST') ?? 'localhost',
			'smtp_user' => Config::mail('SMTP_USER') ?? '',
			'smtp_pass' => Config::mail('SMTP_PASS') ?? '',
			'smtp_port' => Config::mail('SMTP_PORT') ?? 587,
			'smtp_from' => Config::mail('SMTP_FROM'),
			'smtp_secure' => Config::mail('SMTP_SECURE') ?? 'TLS'
		];
	}

	/* =========================
	 * API PÚBLICA
	 * ========================= */

	public function to(string $email): self {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new InvalidArgumentException('Email inválido: ' . $email);
		}
		$this->to = $email;
		return $this;
	}

	public function subject(string $type): self {
		$this->asunto = $this->buildSubject($type);
		return $this;
	}

	public function body(array $placeholders, string $type): self {
		$this->body = $this->wrapTemplate($placeholders, $type);
		return $this;
	}

	public function send(): bool {
		$mail = $this->createMailer();

		try {
			$from = $this->config['smtp_from'];

			$mail->setFrom($from, $this->Core->settings['titulo']);
			$mail->addAddress($this->to);
			$mail->Subject = $this->asunto;
			$mail->Body    = $this->body;

			return $mail->send();

		} catch (MailerException $e) {
			$this->lastError = $e->getMessage();
			error_log('[tsEmail] Error al enviar a ' . $this->to . ': ' . $e->getMessage());
			return false;
		}
	}

	/* =========================
	 * ATAJOS
	 * ========================= */

	public function sendFast(string $to, array $placeholders): bool {
        $type = $this->asunto;
		return $this->to($to)->subject($this->asunto)->body($placeholders, $type)->send();
	}

	/* =========================
	 * PRIVADOS
	 * ========================= */

	/**
	 * Instancia y configura PHPMailer según el modo definido en settings:
	 *   mail_mode = 'smtp'  → usa servidor SMTP externo (recomendado en producción)
	 *   mail_mode = 'mail'  → usa la función mail() nativa de PHP (fallback)
	 */
	private function createMailer(): PHPMailer {
		$mail      = new PHPMailer(true); // true = lanza excepciones
		$mode      = $this->config['mail_mode'] ?? 'mail';

		$mail->CharSet  = PHPMailer::CHARSET_UTF8;
		$mail->isHTML(true);

		if ($mode === 'smtp') {
			$mail->isSMTP();
			$mail->Host       = $this->config['smtp_host'];
			$mail->Port       = (int) ($this->config['smtp_port']);
			$mail->SMTPAuth   = !empty($this->config['smtp_user']);
			$mail->Username   = $this->config['smtp_user'];
			$mail->Password   = $this->config['smtp_pass'];

			$mail->SMTPSecure = match((int)($this->config['smtp_secure'] ?? 'TLS')) {
				'SSL' => PHPMailer::ENCRYPTION_SMTPS,
				'TLS' => PHPMailer::ENCRYPTION_STARTTLS,
				default => '',
			};

			if (Config::app('debug.active')) {
				$mail->SMTPOptions = [
					'ssl' => [
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					]
				];
			}

			// Debug: 0 = sin output, 2 = verbose (solo en desarrollo)
			#$mail->SMTPDebug = Config::app('debug.active') ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
		}
		// Si $mode === 'mail', PHPMailer usa mail() por defecto

		return $mail;
	}

	private function buildSubject(string $type): string {
		return match ($type) {
			'signup'            => 'Por favor completa tu registro.',
			'activate'          => 'Active su cuenta.',
			'welcome'           => 'Bienvenido a nuestra comunidad.',
			'password_recovery' => 'Instrucciones para recuperar tu contraseña.',
			'email_change'      => 'Confirma el cambio de tu dirección de correo.',
			'twofactor_setup'   => 'Configura tu verificación en dos pasos.',
			'security_alert'    => 'Hemos detectado un intento de acceso a tu cuenta.',
			'support_reply'     => 'Tienes una respuesta de soporte.',
			'payment_success'   => 'Tu pago se ha procesado correctamente.',
			'payment_failed'    => 'Hubo un problema con tu pago.',
			'newsletter'        => 'Últimas novedades y actualizaciones.',
			'admin_notice'      => 'Tienes un aviso importante del administrador.',
			'ban_notice'        => 'Tu cuenta ha sido suspendida.',
			'system_update'     => 'Actualización importante del sistema.',
			'new_access'   		=> 'Nuevos datos de acceso.',
			default             => 'Notificación',
		};
	}

	private function wrapTemplate(array $placeholders, string $type): string {
		$specific = TS_EXTRAS . '/emails/' . $type . '.php';
		$fallback  = TS_EXTRAS . '/emails/basic.php';
		$templatePath = is_file($specific) ? $specific : $fallback;

		if (!is_file($templatePath)) {
			throw new RuntimeException('Template de email no encontrado: ' . $templatePath);
		}

		$html = file_get_contents($templatePath);

		$placeholders['TITLE']   = htmlspecialchars($this->Core->settings['titulo'], ENT_QUOTES, 'UTF-8');
		$placeholders['WEBSITE'] = htmlspecialchars($this->Core->settings['titulo'], ENT_QUOTES, 'UTF-8');
		$placeholders['TERMS']   = $this->Core->settings['url'] . '/pages/terminos-y-condiciones/';
		$placeholders['PRIV']    = $this->Core->settings['url'] . '/pages/privacidad/';
		$placeholders['LOGOWEB'] = $this->Core->settings['url'] . '/assets/images/phpost/main-32.png';

		foreach ($placeholders as $key => $value) {
			$html = str_replace('{' . $key . '}', $value, $html);
		}

		return $html;
	}
}
