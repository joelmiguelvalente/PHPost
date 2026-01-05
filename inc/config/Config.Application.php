<?php

/**
 * ------------------------------------------------------------
 * PHPost - Application Configuration
 * ------------------------------------------------------------
 *
 * Configuración global de la aplicación.
 * Define políticas de comportamiento, seguridad y entorno.
 *
 * Este archivo NO contiene lógica de negocio.
 *
 * @package   PHPost
 * @copyright 2026
 */

declare(strict_types=1);

final class ConfigApplication extends AbstractConfig
{
   public function __construct()
   {
    	/**
		 * Entorno de ejecución de la aplicación
		 * development | staging | production
		 */
		$APP_ENV = 'development';

		$this->items = [

			/**
			 * --------------------------------------------------------
			 * Application Info
			 * --------------------------------------------------------
			 */
			'app' => [
				'name'    => 'PHPost Risus',
				'slogan'	 => 'Inteligencia recargada 2026',
				'version' => '2.3.026',
				'env'     => $APP_ENV,
				'debug'   => $APP_ENV === 'development',
				'server'  => 'https://discord.gg/StWZtrt2DE',
			],

			/**
			 * --------------------------------------------------------
			 * Localization
			 * --------------------------------------------------------
			 */
			'localization' => [
				'timezone' => 'America/Argentina/Buenos_Aires',
				'locale'   => 'es_AR',
				'charset'  => 'UTF-8',
			],

			/**
			 * --------------------------------------------------------
			 * Security Policies
			 * --------------------------------------------------------
			 */
			'security' => [
				/**
				 * Hashing de contraseñas
				 * Se recomienda PASSWORD_ARGON2ID
				 */
				'password' => [
					'algorithm' => PASSWORD_ARGON2ID,
					'options'   => [
						'memory_cost' => 1 << 17, // 128 MB
						'time_cost'   => 4,
						'threads'     => 2,
					],
					'min_length' => 8,
				],

				/**
				 * Generación de tokens seguros
				 */
				'token' => [
					'bytes' => 32, // random_bytes(32)
				],

				/**
				 * Política de sesiones
				 */
				'session' => [
					'name'     => 'phpost_session',
					'lifetime' => 0,
					'secure'   => $APP_ENV === 'production',
					'httponly' => true,
					'samesite' => 'Lax',
				],
			],

			/**
			 * --------------------------------------------------------
			 * Paths & URLs
			 * --------------------------------------------------------
			 */
			'paths' => [
				//'base_url'    => 'http://localhost/phpost',
				'avatar'      => dirname(__DIR__, 1) . '/storage/avatar',
				'cache'       => dirname(__DIR__, 1) . '/storage/cache',
				'logs'        => dirname(__DIR__, 1) . '/storage/logs',
				'uploads'     => dirname(__DIR__, 1) . '/storage/uploads',
			],
		];
   }
}