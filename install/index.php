<?php

/**
 * @name      index.php
 * @author    PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

define('TS_HEADER', true);

require_once __DIR__ . '/src/InstallKernel.php';

(new InstallKernel())->run();
