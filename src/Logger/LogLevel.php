<?php

declare(strict_types=1);

/**
 * @package    PHPost/Logger
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

enum LogLevel: string {
   case INFO = 'INFO';
   case ERROR = 'ERROR';
   case EXCEPTION = 'EXCEPTION';
   case FATAL = 'FATAL';
   case WARNING = 'WARNING';
   case DEBUG = 'DEBUG';
}
