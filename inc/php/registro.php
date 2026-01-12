<?php

/**
 * @name registro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

/**
 * Inicializamos variable
 * 
 * $tsPage  = Plantilla para mostrar con este archivo.
 * $tsLevel = Nivel de acceso a esta pagina (ver faqs).
 * $tsAjax  = La respuesta sera por ajax si/no.
 */

$tsPage  = "registro";
$tsLevel = 1; 
$tsAjax  = (!isset($_GET['ajax']) && empty($_GET['ajax']));

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * En caso de problemas la variable cambia
*/
$tsContinue = true;  // CONTINUAR EL SCRIPT

/**
 * Verificamos el nivel de acceso
*/
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
   $tsPage = 'aviso';
   $tsAjax = 0;
   $smarty->assign("tsAviso", $tsLevelMsg);
   $tsContinue = false;
}

if($tsUser->is_member) {
   header("Location: {$tsCore->route('url')}");
   die;
}

/**
 * Si no hay problemas, continuamos
*/
if ($tsContinue) {
   $smarty->assign("publicKey", $tsCore->settings["pkey"]);
   $smarty->assign("tsAbierto", $tsCore->settings["c_reg_active"]);
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once dirname(__DIR__, 2) . "/footer.php";
}