<?php 

require_once __DIR__ . "/functions.php";

spl_autoload_register(function ($class) {
	$prefix = 'Smarty\\';
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}
	$relative_class = substr($class, $len);
	$file = __DIR__ . '/' . str_replace('\\', '/', $relative_class) . '.php';
	if (file_exists($file)) {
		require_once($file);
	}
});