<?php 

/**
 * @name usuarios.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('usuarios')->everybody();
// sincronizamos
$ctx->exportLegacy();

$tsLevelMsg = $tsCore->setLevel($ctx->getLevel(), true);
if (is_array($tsLevelMsg)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", $tsLevelMsg);
   // sincroniza nuevamente
   $ctx->exportLegacy();
}

if($ctx->continue()) {

   $tsPaises = require_once TS_EXTRAS . "/Paises.php";
   $smarty->assign("tsPaises", $tsPaises);
   // USUARIOS
   $tsUsers = $tsUser->getUsuarios();
   $smarty->assign("tsUsers", $tsUsers['data']);
   $smarty->assign("tsPages", $tsUsers['pages']);
   $smarty->assign("tsTotal", $tsUsers['total']);
   // FILTROS
   $smarty->assign("tsFiltro", [
   	'online' => trim($_GET['online'] ?? ''),
   	'avatar' => trim($_GET['avatar'] ?? ''),
   	'sex' 	 => trim($_GET['sexo'] ?? ''),
   	'pais' 	 => trim($_GET['pais'] ?? ''),
   	'rango'  => trim($_GET['rango'] ?? '')
   ]);
    // RANGOS
	$query = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT rango_id, r_name FROM u_rangos ORDER BY rango_id'));
    $smarty->assign("tsRangos", $query);
    
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
