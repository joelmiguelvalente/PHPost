<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
 * @copyright  2026
*/

define('TS_HEADER',     true);
define('ABSPATH',       dirname(__DIR__, 2));
define('TS_STORAGE',    ABSPATH . '/storage');
define('INSTALL_LOCK',  ABSPATH . '/installed.lock');
define('LICENSE',       ABSPATH . '/LICENSE');

/**
 * Si esta instalado, lo devolvemos al sitio
 */
if (file_exists(INSTALL_LOCK)) {
    header('Location: ../index.php');
    exit;
}

/**
 * Existe la sesión? Entonces la creamos
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerar ID de sesión periódicamente
if (!isset($_SESSION['install_created'])) {
    session_regenerate_id(true);
    $_SESSION['install_created'] = time();
}

require_once dirname(__DIR__, 2) . '/config/Config.php';
require_once ABSPATH . '/src/Utils/Security/CsrfToken.php';
//
require_once __DIR__ . '/Step.php';
require_once __DIR__ . '/Connection.php';
require_once __DIR__ . '/helpers.php';

$CsrfToken = new CsrfToken;

try {
    // Instanciar Step
    $stepParam = $_GET['step'] ?? 'bienvenida';
    $Step = new Step($stepParam);
    // Validar acceso al paso
    $lastCompleted = $_SESSION['install_step'] ?? 'bienvenida';
    if (!$Step->canAccess($lastCompleted)) {
        redirectWithError('Debes completar los pasos anteriores primero.', $lastCompleted);
    }
    // Guardar paso actual
    $_SESSION['install_step'] = $Step->current();
    $csrfExcluded = ['bienvenida', 'permisos'];
    // Validar CSRF para peticiones POST (excepto welcome)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($Step->current(), $csrfExcluded, true)) {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$CsrfToken->validate($csrfToken)) {
            // Log del intento de CSRF
            error_log(sprintf('[CSRF] Intento de ataque detectado en paso "%s" desde IP: %s', $Step->current(), $_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            redirectWithError('Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.', $Step->current());
        }
    }

    // Limpiar error de sesión
    if (isset($_SESSION['install_error'])) {
        $error = $_SESSION['install_error'];
        unset($_SESSION['install_error']);
    }

} catch (Throwable $e) {
    $error = $e->getMessage();
    $tsTitle = "Error | Instalación de " . Config::app('app.name');
    // Mostrar error sin continuar
    require_once __DIR__ . '/../index.php';
    exit;
}

#$csrfToken = $CsrfToken->generate();
$tsTitle = $Step->label() . " | Instalación de " . Config::app('app.name');

$DB = null;
if (
    isset($_SESSION['db_connection']) &&
    is_array($_SESSION['db_connection']) &&
    in_array($Step->current(), ['datos_sitio', 'datos_admin', 'finalizar'], true)
) {
    try {
        $DB = new Connection($_SESSION['db_connection']);
        if (!$DB->connect()) {
            $errors[] = 'No se pudo establecer conexión con la base de datos.';
        }
    } catch (Throwable $e) {
        $errors[] = 'Error de conexión: ' . $e->getMessage();
    }
}

match($Step->current()) {
    'bienvenida' => (function() use ($CsrfToken, $Step) {
        $license = file_get_contents(LICENSE) ?: 'Licencia no encontrada.';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['license'])) {
            $_SESSION['LICENSE_ACCEPTED'] = true;
            $CsrfToken->regenerate();
            header('Location: ' . $Step->nextUrl());
            exit;
        }
    })(),
    'permisos' => (function() use ($CsrfToken, $Step, &$errors) {
        if (empty($_SESSION['LICENSE_ACCEPTED'])) {
            header('Location: index.php');
            exit;
        }

        $permisos = [];
        $extensiones = [];
        $hasErrors = false;

        if (Config::app('php.version_id') < Config::app('php.version_min')) {
            $errors['php'] = sprintf(
                'Se requiere PHP %s o superior. Versión actual: %s',
                Config::app('php.version_min_display'),
                Config::app('php.version')
            );
            $hasErrors = true;
        }

        $extensiones['php'] = [
            'name' => 'PHP',
            'hability' => true,
            'message' => 'PHP ' . Config::app('php.version')
        ];

        foreach (Config::app('php.extensions.required') as $ext) {
            $loaded = extension_loaded($ext);
            $extensiones[$ext]['name'] = ucfirst($ext);
            $extensiones[$ext]['hability'] = $loaded;
            $extensiones[$ext]['message'] = $loaded ? "Disponible" : "No disponible";
            if (!$loaded) {
                $hasErrors = true;
            }
        }

        // Verificación de permisos SIN crear los directorios
        foreach(Config::app('paths') as $pid => $permiso) {
            $path = $permiso['full_path'];
            $exists = is_dir($path);
            $isWritable = $exists ? is_writable($path) : false;
            $isOk = $exists && $isWritable;
            $currentChmod = $exists ? substr(sprintf('%o', fileperms($path)), -3) : '---';
            $requiredChmod = $permiso['chmod'] ?? 755;
            if (!$isWritable) {
                $hasErrors = true;
            }
            $permisos[$pid] = [
                'chmod' => $currentChmod,
                'chmod_ok' => $requiredChmod,
                'root' => $permiso['short_path'],
                'exists' => $exists,
                'writable' => $isWritable,
                'css' => $isOk ? 'ok' : 'fail'
            ];
        }

        // Si hay algún permiso incorrecto, marcamos error
        foreach($permisos as $permiso) {
            if ($permiso['css'] === 'fail') {
                $errors['permisos'] = 'Algunos directorios no tienen los permisos correctos.';
                $hasErrors = true;
                break;
            }
        }
        if (!$hasErrors && empty($errors)) {
            $CsrfToken->regenerate();
            header("Location: ./" . $Step->nextUrl());
            die;
        }
    })(),
    'base_de_datos' => (function() use ($CsrfToken, $Step, $DB, &$errors) {
        if (empty($_SESSION['LICENSE_ACCEPTED'])) {
            header('Location: index.php');
            exit;
        }

        $default = sanitizeInput(['hostname', 'username', 'password', 'database']);
        $success = false;
        $connectionChecked = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar campos requeridos
            foreach (['hostname', 'username', 'database'] as $field) {
                if (empty($default[$field])) {
                    $errors[] = "El campo '{$field}' es obligatorio.";
                }
            }

            if (empty($errors)) {
                try {
                    $DB = new Connection($default);
                    // Verificar conexión
                    if ($DB->check()) {
                        $_SESSION['db_connection'] = $default;
                        $success = true;
                        $connectionChecked = true;
                        // Guardamos los datos de conexión
                        $DB->saveData();
                    } else {
                        $error = 'No se pudo conectar a la base de datos. Verifica los datos.';
                    }

                    // Si se confirma la conexión Y se ha enviado el flag de comprobado
                    if ($success && isset($_POST['comprobado']) && $_POST['comprobado'] === 'true') {
                        require_once __DIR__ . '/collections.php';

                        // Conectar a la base de datos
                        if (!$DB->connect()) {
                            $error = 'No se pudo conectar a la base de datos.';
                            return;
                        }

                        try {
                            // Eliminamos las tablas existentes para una instalacion desde cero
                            $tables = $DB->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($tables as $table) {
                                $DB->query("DROP TABLE IF EXISTS `{$table}`");
                            }

                            $DB->beginTransaction();

                            $ejecutados = [];
                            $allSuccess = true;

                            foreach ($phpost_sql as $key => $sql) {
                                try {
                                    $DB->query($sql);
                                    $ejecutados[$key] = 1;
                                } catch (Throwable $e) {
                                    $ejecutados[$key] = 0;
                                    $errors[$key] = $e->getMessage();
                                    $allSuccess = false;
                                }
                            }
                            if ($allSuccess) {
                                $DB->commit();
                                require_once __DIR__ . '/EventScheduler.php';
                                $events = new EventScheduler($DB);
                                $events->install();
                                $_POST = [];
                                $CsrfToken->regenerate();
                                header('Location: ' . $Step->nextUrl());
                                exit;
                            } else {
                                if ($DB->inTransaction()) {
                                    $DB->rollBack();
                                }
                                $error = 'Error creando tablas: ' . implode(', ', $errors);
                            }

                        } catch (Throwable $e) {
                            if ($DB && $DB->inTransaction()) {
                                $DB->rollBack();
                            }
                            $error = 'Error en la base de datos: ' . $e->getMessage();
                        }
                    }

                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
    })(),
    'datos_phpmailer' => (function() use ($CsrfToken, $Step, &$errors) {
        if (empty($_SESSION['LICENSE_ACCEPTED'])) {
            $CsrfToken->regenerate();
            header('Location: index.php');
            exit;
        }

        $default = sanitizeInput(['smtphost', 'smtpuser', 'smtppass', 'smtpfrom']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['omitir'])) {
                header('Location: ' . $Step->nextUrl());
                exit;
            }

            $configFile = Config::app('app.development') ? 'Config.Mailer.local.php' : 'Config.Mailer.php';
            $filename = ABSPATH . "/config/{$configFile}";
            if (!file_exists($filename)) {
                $error = 'Archivo de configuración no encontrado.';
                return;
            }
            $content = file_get_contents($filename);
            $replace = str_replace(['smtphost', 'smtpuser', 'smtppass', 'smtpfrom'], $default, $content);

            if (file_put_contents($filename, $replace) !== false) {
                $CsrfToken->regenerate();
                header('Location: ' . $Step->nextUrl());
                exit;
            } else {
                $error = 'No se pudo guardar la configuración. Verifica permisos.';
            }
        }
    })(),
    'datos_sitio' => (function() use ($CsrfToken, $Step, $DB, &$errors) {
        if (empty($_SESSION['LICENSE_ACCEPTED'])) {
            header('Location: index.php');
            exit;
        }

        if (!$DB || !$DB->isConnected()) {
            $errors[] = 'No hay conexión a la base de datos.';
            return;
        }

        $required = ['titulo', 'slogan', 'url', 'email'];
        $default = sanitizeInput([...$required, 'captcha_provider', 'public_key', 'secret_key']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validaciones
            foreach ($required as $field) {
                if (empty($default[$field])) {
                    $errors[] = "El campo '{$field}' es obligatorio.";
                }
            }
            if (!filter_var($default['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Introduce un email válido.';
            }
            if (!filter_var($default['url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'Introduce una URL válida.';
            }

            if (empty($errors)) {
                try {
                    // Verificar que no exista admin
                    $admin = $DB->selectOne('u_miembros', 'user_id', ['user_rango' => 1]);
                    if ($admin) {
                        $errors[] = 'Ya existe un usuario administrador configurado.';
                        return;
                    }
                    $time = time();
                    // Iniciar transacción
                    $DB->beginTransaction();

                    // Actualizar portada
                    $DB->update('p_posts', [
                        'post_portada'  => createURL('/assets/images/phpost/main-512.webp'),
                        'post_status'   => 1,
                        'post_date'     => $time,
                        'post_update'   => $time
                    ], ['post_id' => 1]);

                    // Actualizar categoría
                    $DB->update('p_categorias', [
                        'c_nombre'  => $default['titulo'],
                        'c_seo'     => slugify($default['titulo']),
                        'c_img'     => 'script.png'
                    ], ['cid' => 41]);

                    // Actualizar configuración captcha
                    $DB->update('w_registro', [
                        'captcha_provider'  => $default['captcha_provider'],
                        'public_key'        => $default['public_key'],
                        'secret_key'        => $default['secret_key']
                    ], ['reg_id' => 1]);

                    // Actualizar configuración sitio
                    $result = $DB->update('w_configuracion', [
                        'titulo'        => $default['titulo'],
                        'slogan'        => $default['slogan'],
                        'url'           => $default['url'],
                        'email'         => $default['email'],
                        'version'       => Config::app('app.version'),
                        'version_code'  => Config::app('app.version_code')
                    ], ['phpost_id' => 1]);

                    if ($result !== false) {
                        $DB->commit();
                        $CsrfToken->regenerate();
                        header('Location: ' . $Step->nextUrl());
                        exit;
                    } else {
                        $DB->rollBack();
                        $errors[] = 'Error al guardar la configuración.';
                    }

                } catch (Throwable $e) {
                    if ($DB) $DB->rollBack();
                    $errors[] = 'Error: ' . $e->getMessage();
                }
            }
        }
    })(),
    'datos_admin' => (function() use ($CsrfToken, $Step, $DB, &$errors) {
        if (empty($_SESSION['LICENSE_ACCEPTED'])) {
            header('Location: index.php');
            exit;
        }

        if (!$DB || !$DB->isConnected()) {
            $errors[] = 'No hay conexión a la base de datos.';
            return;
        }

        $default = sanitizeInput(['nickname', 'email', 'password', 'confirm_password']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once ABSPATH . '/src/Utils/Security/Password.php';
            require_once ABSPATH . '/src/Utils/Media/Avatar.php';

            $Avatar = new Avatar(createURL('/storage/avatar/'));
            $Password = new Password();

            // Validaciones
            if (in_array('', $default, true)) {
                $errors[] = 'Todos los campos son requeridos.';
            }

            if (!empty($default['nickname']) && !ctype_alnum($default['nickname'])) {
                $errors[] = "«nickname» solo puede contener letras y números.";
            }

            if (!empty($default['email']) && !filter_var($default['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "«email» debe ser un email válido.";
            }

            if ($default['password'] !== $default['confirm_password']) {
                $errors[] = "Las contraseñas no coinciden.";
            }

            if (!empty($default['password']) && !$Password->isStrong($default['password'])) {
                $errors[] = 'La contraseña es débil. Debe contener: al menos una minúscula, una mayúscula, un número y un carácter especial.';
            }

            if (empty($errors)) {
                try {
                    // Verificar que no exista admin
                    $admin = $DB->selectOne('u_miembros', '1', ['user_rango' => 1]);
                    if ($admin) {
                        $errors[] = 'Ya existe un administrador registrado.';
                        return;
                    }

                    $DB->beginTransaction();

                    $hash = $Password->create($default['password'], $default['nickname']);
                    $time = time();

                    // Insertar usuario
                    $nuevoId = $DB->insert('u_miembros', [
                        'user_name'         => $default['nickname'],
                        'user_password'     => $hash,
                        'user_email'        => $default['email'],
                        'user_rango'        => 1,
                        'user_registro'     => $time,
                        'user_puntosxdar'   => 50,
                        'user_activo'       => 1,
                    ]);

                    if (!$nuevoId) {
                        throw new RuntimeException('Error al crear el usuario.');
                    }

                    // Tablas relacionadas
                    foreach (['u_perfil', 'u_miembros_sets'] as $table) {
                        $DB->insert($table, ['user_id' => $nuevoId]);
                    }

                    $DB->insert('u_portal', [
                        'user_id'            => $nuevoId,
                        'last_posts_visited' => '',
                        'last_posts_shared'  => '',
                        'last_posts_cats'    => '',
                        'c_monitor'          => ''
                    ]);

                    // Stats de fundación
                    $DB->update('w_stats', [
                        'stats_time_foundation' => $time,
                        'stats_time_upgrade'    => $time
                    ], ['stats_no' => 1]);

                    // Generar avatar
                    $Avatar->ensure((int)$nuevoId, $default['nickname'], 'D6030B');

                    $DB->commit();

                    // Guardar datos del admin en sesión
                    $_SESSION['admin_created'] = [
                        'id'    => $nuevoId,
                        'name'  => $default['nickname'],
                        'email' => $default['email']
                    ];
                    $CsrfToken->regenerate();
                    header('Location: ' . $Step->nextUrl());
                    exit;

                } catch (Throwable $e) {
                    if ($DB) $DB->rollBack();
                    $errors[] = 'Error: ' . $e->getMessage();
                }
            }
        }
    })(),
    'finalizar' => (function() use ($Step, $DB, &$errors) {
        if (empty($_SESSION['LICENSE_ACCEPTED'])) {
            header('Location: index.php');
            exit;
        }

        if (!$DB || !$DB->isConnected()) {
            $errors[] = 'No hay conexión a la base de datos.';
            return;
        }

        $datos = $DB->selectOne('w_configuracion', 'titulo, url', ['phpost_id' => 1]);
        $user = $DB->selectOne('u_miembros', 'user_name, user_email', ['user_id' => 1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Crear archivo de bloqueo
            if (!file_exists(INSTALL_LOCK)) {
                $content = "Instalado: " . date('Y-m-d H:i:s') . "\n";
                $content .= "Versión: " . Config::app('app.version') . "\n";
                $content .= "Admin: " . ($user['user_name'] ?? 'N/A') . "\n";
                file_put_contents(INSTALL_LOCK, $content);
            }

            // Redirigir según elección
            $baseUrl = createURL();
            if (isset($_POST['finish_site'])) {
                header('Location: ' . $baseUrl);
                exit;
            }

            if (isset($_POST['finish_admin'])) {
                header('Location: ' . $baseUrl . '/admin');
                exit;
            }
        }
    })(),
}

// Generar token CSRF para el formulario
$csrfToken = generateCsrfToken();
