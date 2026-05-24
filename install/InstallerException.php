<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * InstallerException
 * ------------------------------------------------------------
 * Excepción tipada para el instalador de PHPost.
 * Permite distinguir errores de instalación de los del sistema.
 *
 * @package    PHPost
 * @subpackage Install
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class InstallerException extends RuntimeException
{
    public static function missingFields(): self
    {
        return new self('Todos los campos obligatorios deben estar completos.');
    }

    public static function invalidStep(string $step): self
    {
        return new self("El paso '{$step}' no existe o no es válido.");
    }

    public static function configWriteFailed(string $file): self
    {
        return new self("No se pudo escribir el archivo de configuración: {$file}");
    }

    public static function backupFailed(string $file): self
    {
        return new self("No se pudo crear el respaldo de: {$file}");
    }

    public static function adminAlreadyExists(): self
    {
        return new self('Ya existe un administrador registrado. No se puede continuar.');
    }

    public static function dbNotConfigured(): self
    {
        return new self('La base de datos no está configurada. Vuelve al paso anterior.');
    }
}
