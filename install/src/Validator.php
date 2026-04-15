<?php

/**
 * ------------------------------------------------------------
 * Validator
 * ------------------------------------------------------------
 * Reglas de validación reutilizables para el instalador.
 * No lanza excepciones: devuelve arrays de errores.
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

final class Validator
{
    private array $errors = [];

    // ── Fluent API ────────────────────────────────────────────

    public function required(string $field, string $value, string $label): self
    {
        if ($value === '') {
            $this->errors[$field] = "El campo «{$label}» es obligatorio.";
        }
        return $this;
    }

    public function email(string $field, string $value, string $label): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "«{$label}» debe ser un email válido.";
        }
        return $this;
    }

    public function url(string $field, string $value, string $label): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "«{$label}» debe ser una URL válida.";
        }
        return $this;
    }

    public function alphanumeric(string $field, string $value, string $label): self
    {
        if ($value !== '' && !ctype_alnum($value)) {
            $this->errors[$field] = "«{$label}» solo puede contener letras y números.";
        }
        return $this;
    }

    public function minLength(string $field, string $value, int $min, string $label): self
    {
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] = "«{$label}» debe tener al menos {$min} caracteres.";
        }
        return $this;
    }

    public function maxLength(string $field, string $value, int $max, string $label): self
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "«{$label}» no puede superar los {$max} caracteres.";
        }
        return $this;
    }

    public function matches(string $field, string $a, string $b, string $label): self
    {
        if ($a !== $b) {
            $this->errors[$field] = "Los campos «{$label}» no coinciden.";
        }
        return $this;
    }

    public function smtpHost(string $field, string $value): self
    {
        if ($value === '') return $this;
        $valid = filter_var($value, FILTER_VALIDATE_IP) ||
                 (bool) preg_match('/^(?=.{1,253}$)(?!-)([a-zA-Z0-9-]{1,63}\.)+[a-zA-Z]{2,63}$/', $value);
        if (!$valid) {
            $this->errors[$field] = 'El servidor SMTP no es válido.';
        }
        return $this;
    }

    public function smtpUser(string $field, string $value): self
    {
        if ($value === '') return $this;
        $valid = str_contains($value, '@')
            ? filter_var($value, FILTER_VALIDATE_EMAIL) !== false
            : (bool) preg_match('/^[a-zA-Z0-9._-]{2,64}$/', $value);
        if (!$valid) {
            $this->errors[$field] = 'El usuario SMTP no es válido.';
        }
        return $this;
    }

    // ── Resultados ───────────────────────────────────────────

    public function passes(): bool { return empty($this->errors); }
    public function fails(): bool  { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }

    public function firstError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    public function errorFor(string $field): string
    {
        return $this->errors[$field] ?? '';
    }
}
