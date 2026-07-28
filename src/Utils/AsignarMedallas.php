<?php

declare(strict_types=1);

/**
 * @package    Utils
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class AsignarMedalla {

	private int $objectId;
	private int $type;
	private int $notType;
	private ?int $ownerUserId = null;
	private ?int $rango = null;
	private array $metrics = [];

	public function __construct(int $type, int $objectId) {
		$this->type     = $type;     // 1=user, 2=post, 3=foto
		$this->objectId = $objectId;
	}

	public function setOwnerUser(int $uid): self {
		$this->ownerUserId = $uid;
		return $this;
	}

	public function setRango(?int $rango): self {
		$this->rango = $rango;
		return $this;
	}

	public function setNotificationType(int $type): self {
		$this->notType = $type;
		return $this;
	}

	public function addMetric(int $cond, int $value): self {
		$this->metrics[$cond] = $value;
		return $this;
	}

	public function ejecutar(): void {
		$medallas = DB::fetchAll("SELECT * FROM w_medallas WHERE m_type = :type ORDER BY m_cant DESC", ['type' => $this->type]);

		foreach ($medallas as $m) {
			$cond = (int)($m['m_cond_user'] ?? $m['m_cond_post'] ?? $m['m_cond_foto']);
			if ($m['m_cant'] <= 0) {
				continue;
			}
			if (
				($cond === 9 && $this->rango !== null && $m['m_cond_user_rango'] == $this->rango)
				|| (isset($this->metrics[$cond]) && $this->metrics[$cond] >= $m['m_cant'])
			) {
				$this->asignar((int)$m['medal_id']);
			}
		}
	}

	private function asignar(int $medalId): void {
		$exists = DB::exists("SELECT 1 FROM w_medallas_assign WHERE medal_id = :mid AND medal_for = :mfor", ['mid' => $medalId, 'mfor' => $this->objectId]);
		if ($exists) {
			return;
		}
		
		DB::insert('w_medallas_assign', [
			'medal_id' => $medalId,
			'medal_for' => $this->objectId,
			'medal_date' => time(),
			'medal_ip' => $_SERVER['REMOTE_ADDR'] ?? ''
		]);
		
		if ($this->ownerUserId && $this->notType) {
			DB::insert('u_monitor', [
				'user_id' => $this->ownerUserId,
				'obj_uno' => $medalId,
				'obj_dos' => $this->objectId,
				'not_type' => $this->notType,
				'not_date' => time()
			]);
		}
		
		DB::query("UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = :mid", ['mid' => $medalId]);
	}
}
