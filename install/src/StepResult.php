<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * StepResult
 * ------------------------------------------------------------
 * Value object inmutable que cada StepHandler devuelve.
 *
 * @package    PHPost
 * @subpackage Install\src
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

final class StepResult
{
    private function __construct(
        private readonly array  $data,
        private readonly ?string $redirect,
        private readonly ?string $error,
        private readonly bool   $success,
    ) {}

    // ── Factories ────────────────────────────────────────────

    public static function view(array $data = []): self
    {
        return new self($data, null, null, true);
    }

    public static function redirectTo(string $step): self
    {
        return new self([], $step, null, true);
    }

    public static function withError(string $message, array $data = []): self
    {
        return new self($data, null, $message, false);
    }

    // ── Accessors ────────────────────────────────────────────

    public function getData(): array   { return $this->data; }
    public function getRedirect(): ?string { return $this->redirect; }
    public function getError(): ?string    { return $this->error; }
    public function isRedirect(): bool     { return $this->redirect !== null; }
    public function hasError(): bool       { return $this->error !== null; }
    public function isSuccess(): bool      { return $this->success; }
}
