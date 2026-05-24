<?php

declare(strict_types=1);

/**
 * @package    PHPost/Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsType = (int)($_GET['type'] ?? 0);
$tsTitle = $tsType === 1 ? 'Recuperar contrase&ntilde;a ' : "Validar cuenta  - {$tsCore->settings['titulo']}";
	
/**
 * Inicializamos variable
 */

$ctx = Controller::page('aviso')->guest();
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

$email = $tsCore->setSecure($_GET['email']);
$key = htmlspecialchars($_GET['hash']);
$tsData = db_exec([__FILE__, __LINE__], 'query', 'SELECT user_id, user_name, user_email FROM u_miembros WHERE user_email = \''.$email.'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
	// borrar viejos
db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM `w_contacts` WHERE `time` < \''.(time() - 86400).'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );

// EXISTE?
if(!db_exec('num_rows', $tsData)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", [
		'titulo' => 'Opps!', 
		'mensaje' => 'No existe ning&uacute;n usuario con ese email', 
		'but' => 'Ir a p&aacute;gina principal'
	]);
   // sincroniza nuevamente
   $ctx->exportLegacy();
}

// hash
$hash = db_exec([__FILE__, __LINE__], 'query', 'SELECT * FROM w_contacts WHERE hash = \''.$tsCore->setSecure($key).'\' AND user_email = \''.$email.'\' AND type = '.(int)$type.' ORDER BY id DESC LIMIT 1') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
if(!db_exec('num_rows', $hash)) {
   $ctx->changePage('aviso');
   $ctx->stop();
   $smarty->assign("tsAviso", [
		'titulo' => 'Opps!', 
		'mensaje' => 'La clave de validaci&oacute;n no es correcta'
	]);
   // sincroniza nuevamente
   $ctx->exportLegacy();
}

if($ctx->continue()) {
	$data = db_exec('fetch_assoc', $tsData);
	if($tsType == 2){
			if(db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_activo = \'1\' WHERE user_id = \''.$data['user_id'].'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') )) {
			db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM w_contacts WHERE user_id = \''.$data['user_id'].'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
			$smarty->assign('tsAviso', array('titulo' => 'Ok', 'mensaje' => 'Cuenta validada', 'but' => 'Ir a la p&aacute;gina principal'));;
			}else{
			$smarty->assign('tsAviso', array('titulo' => 'Opps', 'mensaje' => 'Ha ocurrido un error', 'but' => 'Reintentar', 'link' => ''.$tsCore->settings['url'].'/validar/'.$key.'/2/'.$email.''));}
			}else{
		if($_POST){
			if(empty($_POST['pass'])){ 
			$smarty->assign('tsAviso', array('titulo' => 'Opps', 'mensaje' => 'Escriba una contrase&ntilde;a', 'but' => 'Volver', 'link' => ''.$tsCore->settings['url'].'/password/'.$key.'/1/'.$email.''));
			}else{
			db_exec([__FILE__, __LINE__], 'query', 'UPDATE u_miembros SET user_password = \''.md5(md5($_POST['pass']).strtolower($data['user_name'])).'\' WHERE user_id = \''.$data['user_id'].'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
			db_exec([__FILE__, __LINE__], 'query', 'DELETE FROM w_contacts WHERE user_id = \''.$data['user_id'].'\'') or exit( show_error('Error al ejecutar la consulta de la l&iacute;nea '.__LINE__.' de '.__FILE__.'.', 'db') );
			$smarty->assign('tsAviso', array('titulo' => 'Ok', 'mensaje' => 'Contrase&ntilde;a actualizada', 'but' => 'Ir a la p&aacute;gina principal'));
			}
		}else{
			$smarty->assign('tsAviso', array('titulo' => 'Actualizar contrase&ntilde;a', 'mensaje' => '<form method="post">Escribe tu nueva contrase&ntilde;a: <input type="password" name="pass" required/><input type="submit" class="mBtn btnOk" value="Reestablecer contrase&ntilde;a" id="shit" /></form><style type="text/css">#shit{margin-bottom:-45px}</style>'));
		}
	  }
}

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
