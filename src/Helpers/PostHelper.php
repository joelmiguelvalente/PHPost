<?php

declare(strict_types=1);

/**
 * @package    Helpers
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class PostHelper {

	public function __construct(
		protected tsCore $Core,
		protected tsUser $User
	) {}

	/**
	 * @access public
	 * @return bool
	 */
	public function canSeeInactiveUsers(): bool {
		return ($this->User->is_admod && (int)$this->Core->settings['c_see_mod'] === 1);
	}

	/**
	 * @access public
	 * @param string
	 * @return bool
	 */
	public function canPermsUsers(string $perm): bool {
		return (!$this->User->is_admod && $this->User->permiso($perm) === false);
	}

	/**
	 * @access public
	 * @return string
	 */
	public function activeUserSqlCondition(string $add = ''): string {
		return $this->canSeeInactiveUsers() ? '' : "AND u.user_activo = 1 AND u.user_baneado = 0 $add";
	}

}
