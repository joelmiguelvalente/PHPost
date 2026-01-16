<?php

/**
 * @name c.visitas.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once dirname(__DIR__, 1) . '/utils/IP.php';

class tsVisitas {

	protected tsCore $Core;
	protected tsUser $User;

	private int $userID;
	private string $IP;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
		//
		$this->userID = (int)$this->User->uid;
		$this->IP = (new IP)->executeIP();
	}

	/**
	 * @access private
	 * @param int
	 * @return int
	 */
	private function isVisited(int $uid): int {
		$ip = "`ip` LIKE '$this->IP'";
		$like = $this->User->is_member ? "(`user` = {$this->userID} OR $ip)" : $ip;
		$visitado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM `w_visitas` WHERE `for` = $uid AND `type` = 1 AND $like LIMIT 1"));
		return $visitado;
	}

	public function setVisitaCuenta(int $uid): void {
		$time = time();
		$visitado = $this->isVisited($uid);
		if(($this->User->is_member AND $visitado === 0 AND $this->userID !== $uid) || 
			((int)$this->Core->settings['c_hits_guest'] === 1 AND !$this->User->is_member AND !$visitado)
		) {
			db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_visitas (`user`, `for`, `type`, `date`, `ip`) VALUES ({$this->userID}, $uid, 1, $time, '{$this->IP}')");
		} else {
			db_exec([__FILE__, __LINE__], 'query', "UPDATE `w_visitas` SET `date` = $time, ip = '{$this->IP}' WHERE `for` = {$this->userID} AND `type` = 1");
		}
	}

}