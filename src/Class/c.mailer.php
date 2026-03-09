<?php

/**
 * @name c.phpmailer.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class Mailer {

	public function mailerConfig(): array {
		return [
			'SMTP_HOST' 	=> Config::mail('SMTP_HOST'),
			'SMTP_USER' 	=> Config::mail('SMTP_USER'),
			'SMTP_PASS' 	=> Config::mail('SMTP_PASS'),
			'SMTP_FROM' 	=> Config::mail('SMTP_FROM'),
			'SMTP_SECURE'	=> Config::mail('SMTP_SECURE'),
			'SMTP_PORT' 	=> Config::mail('SMTP_PORT')
		];
	}

	public function getMailerData() {
		return [
         'SMTP_HOST'   => $_POST['SMTP_HOST'],
         'SMTP_USER'   => $_POST['SMTP_USER'],
         'SMTP_PASS'   => $_POST['SMTP_PASS'],
         'SMTP_FROM'   => $_POST['SMTP_FROM'],
         'SMTP_SECURE' => $_POST['SMTP_SECURE'],
         'SMTP_PORT'   => $_POST['SMTP_PORT']
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
      if (!in_array($data['SMTP_SECURE'], ['tls', 'ssl', ''])) {
         $errors['SMTP_SECURE'] = 'El cifrado debe ser tls, ssl o vacío.';
      }
      if (empty($data['SMTP_PORT']) || !is_numeric($data['SMTP_PORT'])) {
         $errors['SMTP_PORT'] = 'El puerto debe ser un número.';
      }
      return $errors;
   }

   public function saveMailerConfig(): bool {
      $isLocal = Config::app('debug.active');
      $filePath = TS_CONFIG . '/Config.Mailer' . ($isLocal ? '.local' : '') . '.php';
      $content  = file_get_contents($filePath);

      $replacements = $this->getMailerData();
      foreach ($replacements as $key => $value) {
         $content = preg_replace("/('$key'\s*=>\s*)'[^']*'/", "'$key' => '$value'", $content);
      }
      // SMTP_PORT es int, sin comillas
      $content = preg_replace("/('SMTP_PORT'\s*=>\s*)\d+/", "'SMTP_PORT' => " . (int)$replacements['SMTP_PORT'], $content);
      return (bool) file_put_contents($filePath, $content);
   }

}