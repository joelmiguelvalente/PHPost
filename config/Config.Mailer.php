<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * PHPost - Configuration File
 * ------------------------------------------------------------
 *
 * Archivo central de configuración de la aplicación.
 * Define parámetros globales y servicios externos
 *
 * Este archivo debe ser incluido una única vez
 * desde el bootstrap principal.
 *
 * @package    Config
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

namespace config;

use config\AbstractConfig;

final class ConfigMailer extends AbstractConfig
{
   public function __construct()
   {

   	/**
		 * ------------------------------------------------------------
		 * PHPMailer / SMTP Configuration
		 * ------------------------------------------------------------
		 *
		 * Parámetros necesarios para el envío de correos electrónicos
		 * mediante PHPMailer usando protocolo SMTP.
		 *
		 * IMPORTANTE:
		 * - No versionar credenciales reales.
		 * - Utilizar variables de entorno en producción.
		 *
		 * @var array{
		 *   SMTP_HOST: string,
		 *   SMTP_USER: string,
		 *   SMTP_PASS: string,
		 *   SMTP_FROM: string,
		 *   SMTP_PORT: int,
		 *   SMTP_SECURE: string,
		 *   SMTP_CHARSET: string
		 * }
		 */
      $this->items = [

			/**
			 * Hostname del servidor SMTP
			 * Ejemplo: smtp.gmail.com
			 */
			'SMTP_HOST' => 'smtphost',

			/**
			 * Usuario de autenticación SMTP
			 * Normalmente una dirección de correo
			 */
			'SMTP_USER' => 'smtpuser',

			/**
			 * Contraseña o App Password del usuario SMTP
			 */
			'SMTP_PASS' => 'smtppass',

			/**
			 * Dirección de correo remitente (From)
			 */
			'SMTP_FROM' => 'smtpfrom',

			/**
			 * Método de cifrado de la conexión
			 * Valores comunes: tls | ssl
			 */
			'SMTP_SECURE' => 'tls',

			/**
			 * Puerto del servidor SMTP
			 * 587 => TLS
			 * 465 => SSL
			 */
			'SMTP_PORT' => 587
		];
		parent::__construct();
	}
}
