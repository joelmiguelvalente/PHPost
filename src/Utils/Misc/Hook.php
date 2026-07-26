<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @subpackage Misc
 * @author     Miguel92
 * @copyright  2026
 */

final class Hook
{
    /**
     * Hooks registrados.
     *
     * @var array<string, array<int, array{priority:int, callback:callable}>>
     */
    private static array $hooks = [];

    /**
     * Registra un callback para un hook.
     */
    public static function register(string $name, callable $callback, int $priority = 10): void {
        self::$hooks[$name][] = [
            'priority' => $priority,
            'callback' => $callback,
        ];
    }

    /**
     * Ejecuta un hook y devuelve todo el HTML generado.
     */
    public static function render(string $name): string {
        if (empty(self::$hooks[$name])) {
            return '';
        }

        $hooks = self::$hooks[$name];

        usort($hooks, static fn(array $a, array $b): int => $a['priority'] <=> $b['priority']);

        $output = '';

        foreach ($hooks as $hook) {
            $output .= (string) ($hook['callback'])();
        }

        return trim(htmlspecialchars_decode($output));
    }

    /**
     * Permite usar:
     *
     * {$hook->header}
     */
    public function __get(string $name): string {
        return $this->render($name);
    }

    /**
     * Comprueba si un hook existe.
     */
    public static function has(string $name): bool {
        return isset(self::$hooks[$name]);
    }

    /**
     * Elimina todos los callbacks de un hook.
     */
    public static function clear(string $name): void {
        unset(self::$hooks[$name]);
    }
}
