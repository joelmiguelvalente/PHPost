<?php

/**
 * ------------------------------------------------------------
 * PHPost Configuration File
 * ------------------------------------------------------------
 *
 * Archivo central de configuración de la aplicación.
 * Define parámetros globales de la app y de la conexión
 * a la base de datos.
 *
 * Este archivo debe ser incluido una sola vez desde
 * el bootstrap principal de la aplicación.
 *
 * @name      settings.database.php
 * @author    Miguel92
 * @copyright 2026
 */

declare(strict_types=1);

final class ConfigDatabase extends AbstractConfig
{
   public function __construct()
   {
   	/**
		 * ------------------------------------------------------------
		 * Database Configuration
		 * ------------------------------------------------------------
		 *
		 * Parámetros necesarios para establecer la conexión
		 * con el servidor de base de datos.
		 *
		 * @var array{
		 *   driver: string,
		 *   hostname: string,
		 *   username: string,
		 *   password: string,
		 *   database: string,
		 *   charset: string,
		 *   collation: string,
		 *   port: int
		 * }
		 */
      $this->items = [

			/**
			 * Driver de base de datos
			 * Ejemplo: mysql, pgsql, sqlite
			 */
			'driver' => 'mysql',

			/**
			 * Hostname o IP del servidor de base de datos
			 */
			'hostname' => 'localhost',

			/**
			 * Usuario con permisos sobre la base de datos
			 */
			'username' => 'root',

			/**
			 * Contraseña del usuario de base de datos
			 * IMPORTANTE: no versionar credenciales reales
			 */
			'password' => '',

			/**
			 * Nombre de la base de datos a utilizar
			 */
			'database' => 'phpost',

			/**
			 * Charset por defecto de la conexión
			 * utf8mb4 permite emojis y caracteres extendidos
			 */
			'charset' => 'utf8mb4',

			/**
			 * Collation asociada al charset
			 */
			'collation' => 'utf8mb4_unicode_ci',

			/**
			 * Puerto del servidor de base de datos
			 */
			'port' => 3306,
		];
	}
}