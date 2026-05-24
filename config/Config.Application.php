<?php

declare(strict_types=1);

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
 * @package    Config
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

namespace config;

use config\AbstractConfig;

final class ConfigApplication extends AbstractConfig
{
   public function __construct()
   {
    	/**
		 * Entorno de ejecución de la aplicación
		 * development | staging | production
		 */
		$APP_ENV = 'development';
		$isLocalDev = ($APP_ENV === 'development' && file_exists(dirname(__DIR__, 1)."/.local"));

		$this->items = [

			/**
			 * --------------------------------------------------------
			 * Application Info
			 * --------------------------------------------------------
			 */
			'app' => [
				'name'      	=> 'PHPost Risus',
				'slogan'	   	=> 'Inteligencia recargada 2026',
				'server'    	=> 'https://discord.gg/StWZtrt2DE',
				'version'   	=> '3.6.00',
				'status'    	=> $APP_ENV,
				'description'	=> 'Descubre nuestra plataforma completamente renovada. Actualizaciones constantes, nuevas funcionalidades y experiencia mejorada. En constante evolución para ofrecerte lo mejor.'
			],

			/**
			 * --------------------------------------------------------
			 * Debug
			 * --------------------------------------------------------
			 */
			'debug' => [
				// 1 - E_ALL
				// 2 - E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED
				// 3 - 0
				'level'  => $isLocalDev ? 1 : 3,
				'active' => $isLocalDev,
				'logs' 	=> 'always',
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
					'secure'   => in_array($APP_ENV, ['production', 'staging']),
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
				'backups'     => TS_STORAGE . '/backups',
				'cache'       => TS_STORAGE . '/cache',
				'logs'        => TS_STORAGE . '/logs',
				'media'       => TS_STORAGE . '/media',
				'uploads'     => TS_STORAGE . '/uploads',
			],
		];
   }
}
