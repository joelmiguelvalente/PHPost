<?php

/**
 * @name Controller.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

final class Controller
{
	private static ?self $instance = null;

	private string $page;
	private int $level = 0;
	private bool $ajax;
	private bool $continue = true;

	private function __construct(string $page)
	{
		$this->page = $page;
		$this->ajax = !isset($_GET['ajax']) || empty($_GET['ajax']);
	}

	public static function page(string $page): self
	{
		return self::$instance = new self($page);
	}

	public static function instance(): self
	{
		if (!self::$instance) {
			throw new RuntimeException('Controller not initialized');
		}

		return self::$instance;
	}

	public function level(int $level): self
	{
		$this->level = $level;
		return $this;
	}

	public function requireLevel(int $level): self
	{
	   // Nunca bajar el requisito si ya existe uno más alto
	   $this->level = max($this->level, $level);
	   return $this;
	}

	public function everybody(): self
	{
	   return $this->requireLevel(0);
	}

	public function guest(): self
	{
	   return $this->requireLevel(1);
	}

	public function members(): self
	{
	   return $this->requireLevel(2);
	}

	public function moderator(): self
	{
	   return $this->requireLevel(3);
	}

	public function admin(): self
	{
	   return $this->requireLevel(4);
	}

	public function stop(): void
	{
		$this->continue = false;
	}

	public function continue(): bool
   {
	   return $this->continue;
   }

	public function changePage(string $page): void
	{
		$this->page = $page;
	}

	public function forceAjax(bool $state): void
	{
		$this->ajax = $state;
	}

	public function exportLegacy(): void
	{
		// ⚠️ puente de compatibilidad
		global $tsPage, $tsLevel, $tsAjax, $tsContinue;

		$tsPage     = $this->page;
		$tsLevel    = $this->level;
		$tsAjax     = $this->ajax;
		$tsContinue = $this->continue;
	}

	public function getLevel(): int
	{
		return $this->level;
	}
}
