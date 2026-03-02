<?php

/**
 * @name Config.Paths.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

const PATHS = [
   'TS_ROOT'     => BASEPATH,
	'TS_STORAGE'  => BASEPATH . '/storage',
   'TS_ASSETS'   => BASEPATH . '/assets',
   'TS_SOURCES'  => BASEPATH . '/src',
   'TS_THEMES'   => BASEPATH . '/themes',
   'TS_VIEWS'    => BASEPATH . '/views',
	'TS_CONFIG'   => BASEPATH . '/config',
	'TS_CLASS' 	  => BASEPATH . '/src/Class',
	'TS_DATABASE' => BASEPATH . '/src/Database',
	'TS_EXTRA'    => BASEPATH . '/src/Extras',
	'TS_HELPERS'  => BASEPATH . '/src/Helpers',
	'TS_LIBS' 	  => BASEPATH . '/src/Libs',
	'TS_LOGGER'   => BASEPATH . '/src/Logger',
	'TS_UTILS' 	  => BASEPATH . '/src/Utils'
];

foreach (PATHS as $name => $path) {
   define($name, $path);
}

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('./'));