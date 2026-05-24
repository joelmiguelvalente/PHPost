<?php

declare(strict_types=1);

/**
 * @package    Config
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

define('SOURCEPATH', BASEPATH . '/src');

const PATHS = [
   'TS_ROOT'     => BASEPATH,
	'TS_STORAGE'  => BASEPATH . '/storage',
   'TS_ASSETS'   => BASEPATH . '/assets',
   'TS_THEMES'   => BASEPATH . '/themes',
   'TS_VIEWS'    => BASEPATH . '/views',
	'TS_CONFIG'   => BASEPATH . '/config',
	'TS_BACKUPS'  => BASEPATH . '/storage/backups',
   'TS_SOURCES'  => SOURCEPATH,
	'TS_CLASS' 	  => SOURCEPATH . '/Class',
	'TS_DATABASE' => SOURCEPATH . '/Database',
	'TS_ENUM' 	  => SOURCEPATH . '/Enum',
	'TS_EXTRAS'   => SOURCEPATH . '/Extras',
	'TS_HELPERS'  => SOURCEPATH . '/Helpers',
	'TS_LIBS' 	  => SOURCEPATH . '/Libs',
	'TS_LOGGER'   => SOURCEPATH . '/Logger',
	'TS_UTILS' 	  => SOURCEPATH . '/Utils'
];

foreach (PATHS as $name => $path) {
   define($name, $path);
}

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('./'));
