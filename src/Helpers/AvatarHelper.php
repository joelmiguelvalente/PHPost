<?php

declare(strict_types=1);

/**
 * @package    Helpers
 * @author     Miguel92
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

final class AvatarHelper {

    public function loadAvatar(): mixed {
        return Container::get(
            Avatar::class, [
                Container::get(Routes::class)->avatarPath()
            ]
        );
    }

    public function createAvatar(int $uid, string $nick, int|string $color): void {
        $this->loadAvatar()->ensure($uid, $nick, $color);
    }

}
