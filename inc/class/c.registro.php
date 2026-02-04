<?php

/**
 * @name c.registro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

require_once __DIR__ . '/c.emails.php';
require_once TS_UTILS . '/Avatar.php';
require_once TS_UTILS . '/PasswordHandler.php';
require_once TS_UTILS . '/reCaptcha.php';

class tsRegistro {

	protected tsCore $Core;
	protected tsUser $User;

	public function __construct(tsCore $Core, tsUser $User) {
		$this->Core = $Core;
		$this->User = $User;
	}

	/**
    * @name strstr
    * @access private
    * @param string
    * @return string
   */
	private function strstr(string $haystack, bool $before_needle = false): string {
	   return empty($haystack) ? '' : $this->Core->setSecure(strstr($haystack, '@', $before_needle));
	}

	/**
    * @name strstr($string)
    * @access private
    * @param string
    * @param string
    * @return bool
   */
	private function checkUserExists(string $username = '', string $email = ''): bool {
		$username = $this->Core->setSecure($username);
		$email = $this->Core->setSecure(strtolower($email ?? ''));
		$q = !empty($username) ? "user_name = '$username'" : "LOWER(user_email) = '$email'";
		return db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM u_miembros WHERE $q LIMIT 1")) === 1;
	}

	private function getPostData(bool $check = false): array {
		$username = $this->Core->setSecure($this->Core->parseBadWords(htmlspecialchars($_POST['nick'] ?? '')));
		$email = $this->Core->setSecure(strtolower($_POST['email'] ?? ''));
		// DATOS NECESARIOS
		$data = [
			'user_nick' => $username,
			'user_email' => $email
		];
		
		if($check) {
			$data = [
				...$data,
				'user_password' => $this->Core->parseBadWords($_POST['password']),
				'user_sexo' => 'none',
				'user_terminos' => $_POST['terminos'],
				'user_captcha' => $_POST['response'],
				'user_registro' => time(),
			];
		}
		return $data;
	}

   /**
    * @name checkUserEmail($pid)
    * @access public
    * @param
    * @return string
   */
	public function checkUserEmail() {
		// Variables
		$vars = $this->getPostData();
      $which = empty($vars['user_nick']) ? 'email' : 'nick';
		// No puede ser solo números
		if (!empty($vars['user_nick']) AND ctype_digit($vars['user_nick'])) return "3: T&uacute; nick no pueder solo n&uacute;meros.";
		// Existe el usuario
		if($this->checkUserExists($vars['user_nick'], $vars['user_email'])) return '0: El '.$which.' ya se encuentra registrado.';
		// Verificamos que no este en la lista negra
     	if(db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', 
         "SELECT id FROM w_blacklist WHERE 
         (type = 3 AND value = '{$this->strstr($vars['user_email'])}') || 
         (type = 4 AND value = '{$this->strstr($vars['user_email'], true)}') || 
         (type = 4 AND value = '{$vars['user_nick']}') LIMIT 1"))
     	) return '0: Parte del '.$which.' no est&aacute; permitida';
	
		// retornar valor
		return "1: El $which est&aacute; disponible.";
	}

   /**
    * @name registerUser
    * @access private
    * @param
    * @return string
   */
	private function verifyCaptcha(string $captcha) {
		// Verificando el captcha
      $reCaptcha = new reCaptcha();  // Usar la misma clave para reCAPTCHA o hCaptcha
		$reCaptcha->RECAPTCHA_TOKEN = $captcha;  // Token de reCAPTCHA o hCaptcha
		$reCaptcha->verify_human();
	}

	private function verifyEmailUser(string $username, string $email) {
		// COMPROBAR NUEVAMENTE QUE EL USUARIO O EMAIL NO SE ENCUENTREN REGISTRADOS
		$query = db_exec([__FILE__, __LINE__], 'query', "SELECT user_name,user_email FROM u_miembros WHERE user_name = '$username' OR LOWER(user_email) = '$email' LIMIT 1");

		if(db_exec('num_rows', $query) === 0 || !filter_var($email, FILTER_VALIDATE_EMAIL) || (int)$this->Core->settings['c_reg_active'] === 0) {
			return '0: Hubo problemas al intentar registrarle, hay campos vac&iacute;os, inv&aacute;lidos o no se le permite el registro.';
		}
	}

	private function sendMessageWelcome(int $uid, array $tsData = []) {
		$send_welcome = $this->Core->settings['c_met_welcome'];
		if($send_welcome > 0 && $send_welcome < 4) {
			$sexo = 'Bienvenid' . (in_array($tsData['user_sexo'], ['none','male']) ? 'o' : 'a'); 
         $msg_bienvenida = str_replace(
         	['[usuario]', '[welcome]', '[web]'], 
         	[$tsData['user_nick'], $sexo, $this->Core->settings['titulo']], 
	         $this->Core->parseBBCode($this->Core->settings['c_message_welcome'])
	      );
         //
         $time = time();
         $titulo = "$sexo a {$this->Core->settings['titulo']}";
	      switch($send_welcome) {
	         case 1:
					db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_muro (p_user, p_user_pub, p_date, p_body, p_type) VALUES ($uid, 1, $time, '$msg_bienvenida', 1)"); 
		         $m_id = db_exec('insert_id');
					db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_monitor (user_id,obj_user,obj_uno, not_type,not_total,not_menubar,not_monitor) VALUES ($uid, 1, $m_id, 12, 1, 1, 1)");
				break;
	         case 2:
					$preview = substr($msg_bienvenida, 0, 75); 
					if(db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_mensajes (`mp_to`, `mp_from`, `mp_subject`, `mp_preview`, `mp_date`) VALUES ($uid, 1, '$titulo', '$preview', $time)")) {
		            $mp_id = db_exec('insert_id');
		            db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_respuestas (mp_id, mr_from, mr_body, mr_ip, mr_date) VALUES ($mp_id, 1, '$msg_bienvenida', '{$_SERVER['REMOTE_ADDR']}', $time)"); 
		         }
				break;
		 		case 3:
					db_exec([__FILE__, __LINE__], 'query', "INSERT INTO u_avisos (`user_id`, `av_subject`, `av_body`, `av_date`, `av_type`) VALUES ($uid, '$titulo', '$msg_bienvenida', $time, 4)");			
         	break;
			}
		}
	}

	private function sendEmail(int $uid, array $tsData = []) {
		$pin = strtoupper(bin2hex(random_bytes(4))); // ej: A9F3C8D2
		$pinHash = password_hash($pin, PASSWORD_DEFAULT);
		$time = time();
		$expires = $time + 900; // 15 minutos
		#$words = str_split($pin, 1);
				
		if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO w_activate (user_id, user_email, code_hash, expire_at, type, used, ip) VALUES ($uid, '{$tsData['user_email']}', '$pinHash', $time, 'activation', 0, $ip)")) {
			return '0: Ocurri&oacute; un error, int&eacute;ntelo de nuevo.';
		}

		$title = $this->Core->settings['titulo'];
		$body = <<<ACTIVE
		<div style="background:#0f7dc1;padding:10px;font-family:Arial, Helvetica,sans-serif;color:#000">
			<h1 style="color:#FFFFFF; font-weight:bold; font-size:30px;">$title</h1>
			<div style="background:#FFF;padding:10px;font-size:16px">
				<h2 style="font-family:Arial, Helvetica,sans-serif;color:#000;font-size:22px">Hola {$tsData['user_nick']}</h2>
				<p style="font-family:Arial, Helvetica,sans-serif;color:#000">&iexcl;Te damos la bienvenida a $title!</p>
				<p>Para finalizar con el proceso de registro, confirma tu direcci&oacute;n de email accediendo a <a href="{$this->Core->route('url')}/validar/$pinHash/2/{$tsData['user_email']}">este enlace</a> y luego ingresando este pin <strong>$pin</strong>
				</p>

				<br /> <br />
				<p>Posteriormente podr&aacute; acceder con las siguientes credenciales:</p>
				<p>Usuario: <strong>{$tsData['user_nick']}</strong></p>
				<p>Contrase&ntilde;a: <strong>{$tsData['user_password']}</strong></p>
				<hr />
				<p>Antes de empezar a interactuar con la comunidad, te recomendamos que visites el <a target="_blank" href="{$this->Core->route('url')}/pages/protocolo/">Protocolo</a> del sitio.</p>
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
		$email = new tsEmail($tsCore);
		$email->sendSignup($tsData['user_email'], 'activate', $bodyHtml) OR die('0: Hubo un error al intentar procesar lo solicitado');
		return "2: Te hemos enviado un correo a <b>$to</b> con los &uacute;ltimos pasos para finalizar con el registro.<br><br>Si en los pr&oacute;ximos minutos no lo encuentras en tu bandeja de entrada, por favor, revisa tu carpeta de correo no deseado, es posible que se haya filtrado.<br><br>&iexcl;Muchas gracias!";	
	}

   /**
    * @name registerUser
    * @access public
    * @param
    * @return string
   */
	public function registerUser(): string {
		// DATOS NECESARIOS
		$tsData = $this->getPostData(true);
		// ERRORS
		$errors = [
			'default'	=> 'El campo es requerido',
			'nick' 		=> 'El nombre de usuario ya se encuentra registrado.',
			'password' 	=> 'La contrase&ntilde;a tiene que ser distinta que el nick',
			'email' 		=> 'El formato es incorrecto',
			'email_2' 	=> 'El email ya est&aacute; en uso',
			'captcha' 	=> 'Validaci&oacute;n incorrecta',
		];
		// Verificar captcha
		$this->verifyCaptcha($tsData['user_captcha']);
		// COMPROBAR VACIOS
		foreach($tsData as $key => $val){
			if(empty($val)) return str_replace('user_', '', $key) . ": El campo es requerido";
		}
		// COMPROBAR QUE EL NOMBRE DE USUARIO SEA VALIDO
      if(!preg_match("/^[a-zA-Z0-9_-]{4,16}$/", $tsData['user_nick'])) {
      	return '0: Nombre de usuario inv&aacute;lido';
      }
		$this->verifyEmailUser($tsData['user_nick'], $tsData['user_email']);
		// PASAMOS BIEN... AHORA INSERTAR DATOS
		$PasswordHandler = new PasswordHandler;
		$newPassword = $PasswordHandler->create($tsData['user_password']);
		// Rango por defecto
		$rango = (int)$this->Core->settings['c_reg_rango'] ?? 3;
		//
		if(!db_exec([__FILE__, __LINE__], 'query', "INSERT INTO `u_miembros` (`user_name`, `user_password`, `user_email`, `user_rango`, `user_registro`) VALUES ('{$tsData['user_nick']}', '$newPassword', '{$tsData['user_email']}', $rango, {$tsData['user_registro']})")) {
			return '0: Ocurrio un error, intentalo ma&aacute;s tarde.';
		}
      $uid = (int)db_exec('insert_id');
      // Agregamos datos en diversas tablas
      db_exec([__FILE__, __LINE__], "query", "INSERT INTO u_perfil (user_id, user_pais, p_avatar, user_sexo) VALUES($uid, 'XX', 1, '{$tsData['user_sexo']}')");
      db_exec([__FILE__, __LINE__], "query", "INSERT INTO u_portal (user_id) VALUES($uid)");
      db_exec([__FILE__, __LINE__], "query", "INSERT INTO u_miembros_sets (user_id) VALUES($uid)");
      
      # Generamos automaticamente un avatar
      (new Avatar)->ensure((int)$uid, $tsData['user_nick'], 171717);

		# MENSAJE PARA DAR LA BIENVENIDA BIENVENIDA
		$this->sendMessageWelcome($uid, $tsData);

		// ENVIAMOS EL EMAIL
		if((int)$this->Core->settings['c_reg_activate'] === 0) {
			$this->sendEmail($uid, $tsData);
		} else {
			# Activamos cuenta directamente!
			db_exec([__FILE__, __LINE__], 'query', "UPDATE u_miembros SET user_activo = 1 WHERE user_id = $uid");
			$this->User->loginUser($tsData['user_nick'], $tsData['user_password'], true);
			return "1: Bienvenido a <strong>{$this->Core->settings['titulo']}</strong>, Ahora estas registrado y tu cuenta ha sido activada, podr&aacute;s disfrutar de esta comunidad inmediatamente.<br><br>&iexcl;Muchas gracias!";
		}
	}
}