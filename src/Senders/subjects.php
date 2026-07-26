<?php

declare(strict_types=1);

/**
 * @package    Senders
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

$transactional = ['headers' => ['X-Notify-Type' => 'transactional']];
$security = ['headers' => ['X-Notify-Type' => 'security']];

return [
    'signup' => [
        'subject' => 'Por favor completa tu registro.', ...$transactional
    ],
    'activate' => [
        'subject' => 'Active su cuenta.', ...$transactional
    ],
    'welcome' => [
        'subject' => 'Bienvenido a nuestra comunidad.', ...$transactional
    ],
    'password_recovery' => [
        'subject' => 'Instrucciones para recuperar tu contraseña.', ...$transactional
    ],
    'email_change' => [
        'subject' => 'Confirma el cambio de tu dirección de correo.', ...$security
    ],
    'twofactor_setup' => [
        'subject' => 'Configura tu verificación en dos pasos.', ...$security
    ],
    'security_alert' => [
        'subject' => 'Hemos detectado un intento de acceso a tu cuenta.',
        'headers' => ['X-Notify-Type' => 'alert', 'X-Priority' => '1']
    ],
    'support_reply' => [
        'subject' => 'Tienes una respuesta de soporte.', ...$transactional
    ],
    'payment_success' => [
        'subject' => 'Tu pago se ha procesado correctamente.', ...$transactional
    ],
    'payment_failed' => [
        'subject' => 'Hubo un problema con tu pago.',
        'headers' => ['X-Notify-Type' => 'alert']
    ],
    'newsletter' => [
        'subject' => 'Últimas novedades y actualizaciones.',
        'headers' => ['X-Notify-Type' => 'newsletter']
    ],
    'admin_notice' => [
        'subject' => 'Tienes un aviso importante del administrador.', ...$transactional
    ],
    'ban_notice' => [
        'subject' => 'Tu cuenta ha sido suspendida.',
        'headers' => ['X-Notify-Type' => 'alert', 'X-Priority' => '1']
    ],
    'system_update' => [
        'subject' => 'Actualización importante del sistema.', ...$transactional
    ],
    'new_access' => [
        'subject' => 'Nuevos datos de acceso.', ...$security
    ],
    'content_deleted' => [
        'subject' => 'Contenido eliminado', ...$transactional
    ]
];
