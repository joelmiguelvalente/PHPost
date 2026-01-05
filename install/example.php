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
 * @name      config.inc.php
 * @author    PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

$APP_ENV = 'development';

/**
 * ------------------------------------------------------------
 * Application Configuration
 * ------------------------------------------------------------
 *
 * Contiene información general de la aplicación y flags
 * de comportamiento global.
 *
 * @var array{
 *   name: string,
 *   version: string,
 *   timezone: string,
 *   debug: bool
 * }
 */
$app = [

	/**
	 * Nombre de la aplicación
	 */
	'name' => 'PHPost Risus',

	/**
	 * Versión actual de la aplicación
	 */
	'version' => '2.3.2026',

	/**
	 * Zona horaria por defecto del sistema
	 * Debe coincidir con una timezone válida de PHP
	 */
	'timezone' => 'America/Argentina/Buenos_Aires',

	/**
	 * Modo debug
	 * true  => entorno de desarrollo
	 * false => entorno de producción
	 */
	'debug' => $APP_ENV === 'development',
];

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
$database = [

	/**
	 * Driver de base de datos
	 * Ejemplo: mysql, pgsql, sqlite
	 */
	'driver' => 'mysql',

	/**
	 * Hostname o IP del servidor de base de datos
	 */
	'hostname' => 'dbhost',

	/**
	 * Usuario con permisos sobre la base de datos
	 */
	'username' => 'dbuser',

	/**
	 * Contraseña del usuario de base de datos
	 * IMPORTANTE: no versionar credenciales reales
	 */
	'password' => 'dbpass',

	/**
	 * Nombre de la base de datos a utilizar
	 */
	'database' => 'dbname',

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

/**
 * ------------------------------------------------------------
 * Return Configuration
 * ------------------------------------------------------------
 *
 * Se retorna un array unificado para facilitar
 * su consumo desde el resto de la aplicación.
 */
return [
	'database'	  => $database,
	'phpmailer'	  => $phpmailer,
	'application' => $application,
];