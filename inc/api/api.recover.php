<?php

/**
 * @name ajax.recover.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

$tsLevel = 1; // solo visitantes
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if(!$tsLevelMsg) { 
	die('0: '.$tsLevelMsg);
}
	
require_once dirname(__DIR__, 1) . '/utils/IP.php';
require_once dirname(__DIR__, 1) . '/class/c.emails.php';
$IP = new IP;
$tsEmail = new tsEmail($tsCore);
	
$email = $tsCore->setSecure($_REQUEST['r_email']);
$user_info = db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_name, user_registro, user_activo FROM u_miembros WHERE user_email = '$email'");
if(!db_exec('num_rows', $user_info)){
	die('0: El email no se encuentra registrado.');
}
$tsData = db_exec('fetch_assoc', $user_info);
$uid = (int)$tsData['user_id'];
$time = time();
$hash = strtoupper(bin2hex(random_bytes(4)));

switch($action){
	case 'recover-pass':
		db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_contacts (user_id, user_email, `time`, `type`, `hash`) VALUES ($uid, '$email', $time, 1, '$hash')");
		$body = 'Recuperar contrase&ntilde;a en <strong>'.$tsCore->settings['titulo'].'</strong><br /><br />
		Hola '.$tsData['user_name'].':<br />
		La verificación es usada para asegurar que sólo usted tenga acceso a 
		su cuenta de '.$tsCore->settings['titulo'].' y que, si alguna vez olvida su contrase&ntilde;a, tengamos una forma de generarle una nueva. <br /><br />
		Para recuperar su contrase&ntilde;a, acceda a <a href="'.$tsCore->settings['url'].'/password/'.$hash.'/1/'.$tsCore->setSecure($email).'">este enlace</a><br /><br /><br />
		Si usted no pidi&oacute; recuperaci&oacute;n de su contrase&ntilde;a, ignore este e-mail.<br /><br />
		El staff de <strong>'.$tsCore->settings['titulo'].'</strong>';
		
		// <--
		if(!$tsEmail->sendSignup($email, 'password_recovery', $body)) {
			die('0: Hubo un error al intentar procesar lo solicitado');
		}
		die('1: Las intrucciones para recuperar su contrase&ntilde;a de <b>'.$tsCore->settings['titulo'].'</b> a <b>'.$email.'</b>, si no aparece el e-mail en su bandeja de entrar, revise en correo no deseado porque puede haberse filtrado...');
		// -->
	break;
	case 'recover-validation':
		if((int)$tsData['user_activo'] === 1) die('0: La cuenta ya se encuentra activada');
		$pinHash = password_hash($hash, PASSWORD_DEFAULT);
		db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_activate (user_id, user_email, code_hash, expire_at, type, used, ip) VALUES ($uid, '{$tsData['user_email']}', '$pinHash', $time, 'validation', 0, {$IP->executeIP()})");

		$title = $tsCore->settings['titulo'];
		$body = <<<ACTIVE
		<div style="background:#0f7dc1;padding:10px;font-family:Arial, Helvetica,sans-serif;color:#000">
			<h1 style="color:#FFFFFF; font-weight:bold; font-size:30px;">$title</h1>
			<div style="background:#FFF;padding:10px;font-size:16px">
				<h2 style="font-family:Arial, Helvetica,sans-serif;color:#000;font-size:22px">Hola {$tsData['user_name']}</h2>
				<p style="font-family:Arial, Helvetica,sans-serif;color:#000">&iexcl;Te damos la bienvenida a $title!</p>
				<p>Para finalizar con el proceso de registro, confirma tu direcci&oacute;n de email accediendo a <a href="{$tsCore->route('url')}/validar/$pinHash/2/{$tsData['user_email']}">este enlace</a> y luego ingresando este pin <strong>$hash</strong>
				</p>

				<br /> <br />
				<p>Posteriormente podr&aacute; acceder con las siguientes credenciales:</p>
				<p>Usuario: <strong>{$tsData['user_nick']}</strong></p>
				<p>Contrase&ntilde;a: <strong>{$tsData['user_password']}</strong></p>
				<hr />
				<p>Antes de empezar a interactuar con la comunidad, te recomendamos que visites el <a target="_blank" href="{$tsCore->route('url')}/pages/protocolo/">Protocolo</a> del sitio.</p>
				<p>Esperamos que disfrutes enormemente tu visita.</p>
				<p>&iexcl;Te damos la bienvenida a Muchas gracias!</p>
				<p>Staff de $title.</p>		
				<div style="border-top:#CCC solid 1px;padding:10px 0">
					<span style="color:#666;font-size:11px">
						<center>El staff de <strong>$title</strong></center>
					</span> 
				</div>
			</div>
		</div>
		ACTIVE;
		
		// <--
		if(!$tsEmail->sendSignup($email, 'activate', $body)) {
			die('0: Hubo un error al intentar procesar lo solicitado');
		}
		die('1: <div class="box_cuerpo" style="padding: 12px 20px; border-top:1px solid #CCC">Hemos enviado un correo a <b>'.$email.'</b> con los &uacute;ltimos pasos para finalizar con el registro.<br><br>Si en los pr&oacute;ximos minutos no lo encuentras en tu bandeja de entrada, por favor, revisa tu carpeta de correo no deseado, es posible que se haya filtrado.<br><br>&iexcl;Muchas gracias!</div>');
		// -->
	break;
	default:
		die('0: Este archivo no existe.');
	break;
}