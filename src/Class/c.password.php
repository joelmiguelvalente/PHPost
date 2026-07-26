<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsPassword {

    public function __construct(
        protected tsCore $Core,
        protected Password $Password
    ) {}

    public function process(int $type, string $email, string $hash, array $post): array {
        // Validar usuario existe
        $user = DB::fetch("SELECT user_id, user_name, user_email FROM u_miembros WHERE user_email = :email", ['email' => $email]);
        if (!$user) return [
            'error' => true,
            'message' => ['titulo' => 'Opps!', 'mensaje' => 'Email no registrado']
        ];

        // Validar hash
        $contact = DB::fetch("SELECT * FROM w_contacts WHERE hash = :hash AND user_email = :email AND type = :type ORDER BY id DESC LIMIT 1", [
            'hash' => $hash, 'email' => $email, 'type' => $type
        ]);
        if (!$contact) return [
            'error' => true,
            'message' => ['titulo' => 'Opps!', 'mensaje' => 'Clave inválida']
        ];

        // Limpiar contactos expirados
        DB::query("DELETE FROM w_contacts WHERE time < :time", ['time' => time() - 86400]);

        if ($type === 2) {
            // Activar cuenta
            DB::query("UPDATE u_miembros SET user_activo = 1 WHERE user_id = :id", ['id' => $user['user_id']]);
            DB::query("DELETE FROM w_contacts WHERE user_id = :id", ['id' => $user['user_id']]);
            return [
                'error' => false,
                'message' => ['titulo' => 'Ok', 'mensaje' => 'Cuenta validada']
            ];
        }

        if ($type === 1 && !empty($post)) {
            // Validar campos
            $pass = $post['pass'] ?? '';
            $confirm = $post['pass_confirm'] ?? '';
            if ($pass === '' || $confirm === '') {
                return ['error' => true, 'message' => ['titulo' => 'Error', 'mensaje' => 'Complete todos los campos']];
            }
            if ($pass !== $confirm) {
                return ['error' => true, 'message' => ['titulo' => 'Error', 'mensaje' => 'Las contraseñas no coinciden']];
            }
            if (strlen($pass) < 6) {
                return ['error' => true, 'message' => ['titulo' => 'Error', 'mensaje' => 'La contraseña debe tener al menos 6 caracteres']];
            }
            // Cambiar password
            $hashed = $this->Password->create($pass, $user['user_name']);
            DB::query("UPDATE u_miembros SET user_password = :pass WHERE user_id = :id", [
                'pass' => $hashed, 'id' => $user['user_id']
            ]);
            DB::query("DELETE FROM w_contacts WHERE user_id = :id", ['id' => $user['user_id']]);
            return [
                'error' => false,
                'message' => ['titulo' => 'Ok', 'mensaje' => 'Contraseña actualizada']
            ];
        }

        // Mostrar formulario
        return [
            'error' => false,
            'form' => ['key' => $hash, 'email' => $email]
        ];
    }
}
