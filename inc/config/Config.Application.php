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
				'name'      => 'PHPost Risus',
				'slogan'	   => 'Inteligencia recargada 2026',
				'version'   => '2.3.' . date('d'),
				'env'       => $APP_ENV,
				'debug'     => $APP_ENV === 'development',
				'debug_all' => true,
				'logs_show' => true,
				'server'    => 'https://discord.gg/StWZtrt2DE',
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
			 * Logs
			 * --------------------------------------------------------
			 */
			'logging' => [
			   'default_channel' => 'app',
			   'channels' => [
			      'app'   => 'app',
			      'auth'  => 'auth',
			      'admin' => 'admin',
			      'api'   => 'api',
			      'php'   => 'php',
			   ],
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
				'avatar'      => TS_STORAGE . '/avatar',
				'cache'       => TS_STORAGE . '/cache',
				'logs'        => TS_STORAGE . '/logs',
				'uploads'     => TS_STORAGE . '/uploads',
			],
		];
   }
}