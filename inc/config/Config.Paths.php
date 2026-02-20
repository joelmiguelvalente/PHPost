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
   'TS_INCLUDES' => BASEPATH . '/inc',
   'TS_THEMES'   => BASEPATH . '/themes',
   'TS_VIEWS'    => BASEPATH . '/views',
	'TS_CLASS' 	  => BASEPATH . '/inc/class',
	'TS_CONFIG'   => BASEPATH . '/inc/config',
	'TS_DATABASE' => BASEPATH . '/inc/database',
	'TS_EXTRA'    => BASEPATH . '/inc/extras',
	'TS_HELPERS'  => BASEPATH . '/inc/helpers',
	'TS_LIBS' 	  => BASEPATH . '/inc/libs',
	'TS_LOGGER'   => BASEPATH . '/inc/logger',
	'TS_UTILS' 	  => BASEPATH . '/inc/utils'
];

foreach (PATHS as $name => $path) {
   define($name, $path);
}

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('./'));