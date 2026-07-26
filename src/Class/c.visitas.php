<?php

declare(strict_types=1);

/**
 * @package    Class
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

class tsVisitas {

	private int $userID;

	private string $myIP;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User,
		protected IP $IP
	) {
		//
		$this->userID = (int)$this->User->uid;
		$this->myIP = $this->IP->getIPBinary();
	}

	/**
	 * @access private
	 * @param int
	 * @return int
	 */
	private function isVisited(int $id, int $type = 1): int {
		$ipCondition = "`ip` LIKE :ip";
	    $params = [
	        'target'  => $id,
	        'type' => $type,
	        'ip'   => $this->myIP
	    ];

	    if ($this->User->is_member) {
	        $condition = "(`user` = :user OR $ipCondition)";
	        $params['user'] = $this->userID;
	    } else {
	        $condition = $ipCondition;
	    }

		return DB::numRows("SELECT id FROM `w_visitas` WHERE `target_id` = :target AND `type` = :type AND $condition LIMIT 1", $params);
	}

	private function addView(int $id, int $type = 1): void {
		DB::insert('w_visitas', [
			'user' => $this->userID,
			'target_id' => $id,
			'type' => $type,
			'date' => time(),
			'ip' => $this->myIP
		]);
	}

	private function updateViewPost(int $id): void {
		$sql = (!$this->User->is_member) ? "" : " AND post_user != {$this->User->uid}";
		DB::raw("UPDATE p_posts SET post_hits = post_hits + 1 WHERE post_id = :pid $sql", ['pid' => $id]);
	}

	public function updateViews(int $id, int $type = 1): int {
		$time = time();
		$visitado = $this->isVisited((int)$id, (int)$type);
		if(($this->User->is_member AND $visitado === 0 AND $this->userID !== $id) || 
			((int)$this->Core->settings['c_hits_guest'] === 1 AND !$this->User->is_member AND !$visitado)
		) {
			$this->addView((int)$id, (int)$type);
			if($type === 2) {
				$this->updateViewPost((int)$id);
			}
		} else {
			$forId = ($type === 2) ? $id : $this->userID;
			$whereUser = '';
			if ($type === 2 && $this->User->is_member) {
			   $whereUser = "AND `user` = {$this->userID}";
			}
			DB::raw("UPDATE `w_visitas` SET `date` = :date, ip = :ip WHERE `target_id` = :target AND `type` = :type $whereUser", [
				'date' => $time,
				'ip' => $this->myIP,
				'target' => $forId,
				'type' => $type
			]);
		}
		return $visitado;
	}

	public function updateViewsGuest(int $visitado, int $id, int $type = 1): void {
		if((int)$this->Core->settings['c_hits_guest'] === 1 AND !$this->User->is_member AND !$visitado) {
			$this->addView((int)$id, (int)$type);
			$this->updateViewPost((int)$id);
		}
	}

	public function getLastViews(int $id, int $type = 1): array {
		$query = "SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.target_id = :target AND v.type = :type AND v.user > 0 ORDER BY v.date DESC LIMIT 10";
		return DB::fetchAll($query, [
			'target' => $id,
			'type' => $type
		]);
	}

	# Es más para el portal que visitas, solo post
	public function addViewPortal(int $id): void {
		if((int)$this->Core->settings['c_allow_portal'] === 1 AND $this->User->is_member) {
			$data = DB::fetch("SELECT last_posts_visited as visited FROM u_portal WHERE user_id = :uid LIMIT 1", ['uid' => $this->User->uid]);
			//
			$visited = [];
			if ($data && !empty($data['visited'])) {
			   $visited = json_decode($data['visited'], true);
			   if (!is_array($visited)) {
			      $visited = [];
			   }
			}
			// mantener máximo 10
			$visited = array_slice($visited, -9);
			if (!in_array($id, $visited, true)) {
			   $visited[] = $id;
			}
			//
			$visited = json_encode($visited);
			DB::update('u_portal', ['last_posts_visited' => $visited], 'user_id = :id', ['id' => $this->User->uid]);
		}
	}

}
