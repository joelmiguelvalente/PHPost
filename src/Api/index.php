<?php

declare(strict_types=1);

/**
 * @package    src\Api
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

/**
 * Inicializamos variable
 * 
 * $tsPage	= Plantilla para mostrar con este archivo.
 * $tsLevel	= Nivel de acceso a esta pagina (ver faqs).
 * $tsAjax	= La respuesta sera por ajax si/no.
 */
$tsPage = "";
$tsLevel = 0;
$tsAjax = (!isset($_GET['ajax']) && empty($_GET['ajax']));
	
require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

// Con esto evitamos que empiece con t.
$useExtension = false;

$action = trim($_GET['action'] ?? '');
[$actionType] = explode('-', $action, 2);

# QUE ARCHIVO NECESITAMOS?
$actionFile = __DIR__ . "/api.$actionType.php";

# Verificamos que exista el archivo
if(!file_exists($actionFile)) {
	echo "0: No se encontró el archivo que se ha solicitado.";
}

# Desde esta archivo se modificaran las variables
require_once $actionFile;

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
	require_once dirname(__DIR__, 2) . "/footer.php";
}
