<?php

/**
 * ------------------------------------------------------------
 * ConfigWriter
 * ------------------------------------------------------------
 * Escribe los archivos de configuración de forma segura:
 * backup atómico → escritura → verificación.
 *
 * Sustituye el str_replace frágil del instalador anterior.
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

final class ConfigWriter
{
    /**
     * Reemplaza marcadores en un archivo de configuración.
     *
     * @param string $filePath  Ruta absoluta al archivo .php de config
     * @param array  $markers   ['marcador' => 'valor_real', ...]
     *
     * @throws RuntimeException si no se puede leer, respaldar o escribir
     */
    public function write(string $filePath, array $markers): void
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("No se puede leer el archivo de configuración: {$filePath}");
        }

        $original = file_get_contents($filePath);

        if ($original === false) {
            throw new RuntimeException("Error al leer: {$filePath}");
        }

        // Crear backup atómico
        $backup = $filePath . '.bak';
        if (file_put_contents($backup, $original, LOCK_EX) === false) {
            throw new RuntimeException("No se pudo crear el respaldo de: {$filePath}");
        }

        try {
            $updated = $this->applyMarkers($original, $markers);

            if (file_put_contents($filePath, $updated, LOCK_EX) === false) {
                throw new RuntimeException("Error al escribir: {$filePath}");
            }

            // Verificar que el archivo se escribió correctamente
            $written = file_get_contents($filePath);
            if ($written !== $updated) {
                throw new RuntimeException("Verificación fallida para: {$filePath}");
            }

            // Eliminar backup si todo fue bien
            @unlink($backup);

        } catch (Throwable $e) {
            // Restaurar backup ante cualquier fallo
            @copy($backup, $filePath);
            @unlink($backup);
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    // ── Privado ──────────────────────────────────────────────

    /**
     * Aplica marcadores al contenido del archivo.
     * Cada marcador debe ser único en el archivo para evitar
     * reemplazos accidentales.
     */
    private function applyMarkers(string $content, array $markers): string
    {
        foreach ($markers as $marker => $value) {
            $escaped = addslashes((string) $value);
            $count   = 0;

            $content = str_replace(
                "'{$marker}'",
                "'{$escaped}'",
                $content,
                $count
            );

            if ($count === 0) {
                throw new RuntimeException(
                    "Marcador '{$marker}' no encontrado en el archivo de configuración."
                );
            }

            if ($count > 1) {
                throw new RuntimeException(
                    "Marcador '{$marker}' aparece {$count} veces (ambiguo). Revisa el template de configuración."
                );
            }
        }

        return $content;
    }
}
