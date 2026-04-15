<?php

/**
 * @name index.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

// Verificamos si el sitio fue instalado
if(!file_exists(__DIR__ . DIRECTORY_SEPARATOR . '.lock')) {
	header("Location: ./install/index.php?step=bienvenida");
	die;
}

// Incluimos header
require_once __DIR__ . '/header.php';

/*
 * -------------------------------------------------------------------
 *  Validamos que mostrar home/mi
 * -------------------------------------------------------------------
 */
// Checamos...
$doAction = (isset($_GET['do']) && $_GET['do'] === 'portal');
if((int)$tsCore->settings['c_allow_portal'] && $tsUser->is_member && $doAction) {
	// Portal/mi
	require_once __DIR__ . '/src/Php/portal.php';
} else {
	// Home
	require_once __DIR__ . '/src/Php/home.php';
}
