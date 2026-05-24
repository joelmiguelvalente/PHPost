<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

define('TS_HEADER', true);

require_once __DIR__ . '/src/InstallKernel.php';

(new InstallKernel())->run();
