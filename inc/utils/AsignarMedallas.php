<?php

/**
 * @package    PHPost
 * @author     Miguel92
 * @copyright  2026
 * @version    2.0.0
*/

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class AsignarMedalla {

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
		$medallas = result_array(db_exec('query', "SELECT * FROM w_medallas WHERE m_type = '{$this->type}' ORDER BY m_cant DESC"));

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
		$exists = db_exec('num_rows', db_exec('query', "SELECT id FROM w_medallas_assign WHERE medal_id = '$medalId' AND medal_for = '{$this->objectId}'"));
		if ($exists) {
			return;
		}
		db_exec('query', "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES ('$medalId', '{$this->objectId}', time(), '{$_SERVER['REMOTE_ADDR']}')");
		if ($this->ownerUserId && $this->notType) {
			db_exec('query', "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES ({$this->ownerUserId}, $medalId, '{$this->objectId}', '{$this->notType}', time()')");
		}
		db_exec('query', "UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = $medalId");
	}
}