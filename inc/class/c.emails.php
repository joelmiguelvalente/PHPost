<?php

/**
 * @name c.emails.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class tsEmail {

	private tsCore $Core;

	private string $to;
	private string $subject;
	private string $body;
	private string $headers;

	public function __construct(tsCore $Core) {
		$this->Core = $Core;
	}

	/* =========================
	 * API PÚBLICA
	 * ========================= */

	public function to(string $email): self {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new InvalidArgumentException('Email inválido');
		}
		$this->to = $email;
		return $this;
	}

	public function subject(string $type): self {
		$this->subject = $this->buildSubject($type);
		return $this;
	}

	public function body(string $html): self {
		$this->body = $this->wrapTemplate($html);
		return $this;
	}

	public function send(): bool {
		$this->headers = $this->buildHeaders();

		return mail(
			$this->to,
			$this->subject,
			$this->body,
			$this->headers
		);
	}

	/* =========================
	 * ATAJOS
	 * ========================= */

	public function sendSignup(string $to, string $subject, string $body): bool {
		return $this->to($to)->subject($subject)->body($body)->send();
	}

	/* =========================
	 * PRIVADOS
	 * ========================= */

	private function buildSubject(string $type): string {
		$subject = match ($type) {
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
			default  => 'Notificación'
		};

		return '=?UTF-8?B?' . base64_encode($subject) . '?=';
	}

	private function buildHeaders(): string {
		$sender = sprintf(
			'%s <no-reply@%s>',
			$this->Core->settings['titulo'],
			$this->Core->route('domain')
		);

		return implode("\r\n", [
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'Content-Transfer-Encoding: 8bit',
			'From: ' . $sender,
			'Reply-To: ' . $sender,
			'Return-Path: ' . $sender,
			'X-Mailer: PHPost'
		]);
	}

	private function wrapTemplate(string $body): string {
		$html = file_get_contents(TS_EXTRA . '/emails/basic.php');

		return str_replace(
			['{WEBSITE}', '{CONTENTBODY}', '{LOGOWEB}', '{TERMS}', '{PRIV}'],
			[
				$this->Core->settings['titulo'],
				$body,
				$this->Core->route('tema:images') . '/phpostmin.gif',
				$this->Core->route('url') . '/pages/terminos-y-condiciones/',
				$this->Core->route('url') . '/pages/privacidad/'
			],
			$html
		);
	}
}