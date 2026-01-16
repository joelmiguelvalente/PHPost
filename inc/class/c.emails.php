<?php

/**
 * @name c.registro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

final class tsEmail {

	private tsCore $core;

	private string $to;
	private string $subject;
	private string $body;
	private string $headers;

	public function __construct(tsCore $core) {
		$this->core = $core;
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

	public function sendSignup(string $to, string $body): bool {
		return $this->to($to)->subject('signup')->body($body)->send();
	}

	/* =========================
	 * PRIVADOS
	 * ========================= */

	private function buildSubject(string $type): string {
		$subject = match ($type) {
			'signup' => 'Por favor completa tu registro',
			default  => 'Notificación'
		};

		return '=?UTF-8?B?' . base64_encode($subject) . '?=';
	}

	private function buildHeaders(): string {
		$sender = sprintf(
			'%s <no-reply@%s>',
			$this->core->settings['titulo'],
			$this->core->settings['domain']
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
		$html = file_get_contents(TS_EXTRA . 'emails/basic.php');

		return str_replace(
			['{WEBSITE}', '{CONTENTBODY}', '{LOGOWEB}', '{TERMS}', '{PRIV}'],
			[
				$this->core->settings['titulo'],
				$body,
				$this->core->route('tema:images') . '/phpostmin.gif',
				$this->core->route('url') . '/pages/terminos-y-condiciones/',
				$this->core->route('url') . '/pages/privacidad/'
			],
			$html
		);
	}
}