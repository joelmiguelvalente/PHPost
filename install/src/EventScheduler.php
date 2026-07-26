<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
 * @copyright  2026
*/

final class EventScheduler
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function install(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }
        try {
            $this->db->exec(
                file_get_contents(__DIR__ . '/sql/events.sql')
            );
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function isEnabled(): bool
    {
        $row = $this->db->query("SHOW VARIABLES LIKE 'event_scheduler'")->fetch();
        return strtolower($row['Value'] ?? '') === 'on';
    }
}
