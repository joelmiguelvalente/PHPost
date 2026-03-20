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
require_once TS_UTILS . '/ReCaptcha.php';

class tsRegistro {

	private string $myIP;

	public function __construct(
		protected tsCore $Core, 
		protected tsUser $User
	) {
		$this->myIP = (new IP)->getIP();
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
		$column = !empty($username) ? "user_name" : "LOWER(user_email)";
		$param = !empty($username) ? $username : strtolower($email ?? '');
		return DB::value("SELECT COUNT(user_id) FROM u_miembros WHERE $column = :search LIMIT 1", ['search' => $param]) === 1;
	}

	private function getPostData(bool $check = false): array {
		$username = $this->Core->parseBadWords(trim($_POST['nick'] ?? ''));
		$email = $this->Core->setSecure(strtolower(trim($_POST['email'] ?? '')));
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
		if (!empty($vars['user_nick']) AND ctype_digit($vars['user_nick'])) {
			return "3: T&uacute; nick no pueder solo n&uacute;meros.";
		}
		// Existe el usuario
		if($this->checkUserExists($vars['user_nick'], $vars['user_email'])) {
			return '0: El '.$which.' ya se encuentra registrado.';
		}
		if(DB::exists("SELECT 1 FROM w_blacklist WHERE (type = 3 AND value = :email1) OR (type = 4 AND value = :email2) OR (type = 4 AND value = :nick) LIMIT 1", [
      	'email1' => $this->strstr($vars['user_email']),
      	'email2' => $this->strstr($vars['user_email'], true),
      	'nick' => $vars['user_nick']
    	])) {
     		return '0: Parte del '.$which.' no est&aacute; permitida';
     	}
	
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
      $reCaptcha = new ReCaptcha($this->Core);  // Usar la misma clave para reCAPTCHA o hCaptcha
		$reCaptcha->RECAPTCHA_TOKEN = $captcha;  // Token de reCAPTCHA o hCaptcha
		$reCaptcha->verify_human();
	}

	private function verifyEmailUser(string $username, string $email): ?string {
		// Validación 
    	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      		return '0: El formato del email es inválido.';
    	}
		// COMPROBAR NUEVAMENTE QUE EL USUARIO O EMAIL NO SE ENCUENTREN REGISTRADOS
		$exists = DB::exists("SELECT 1 FROM u_miembros WHERE user_name = :username OR LOWER(user_email) = :email LIMIT 1", [
			'username' => $username, 
			'email' => strtolower($email)
		]);
		// 
  		if ($exists) {
  		   return '0: El nombre de usuario o email ya están registrados.';
  		}
  		$reCaptchaActive = (int)$this->Core->reCaptchaConfig('c_reg_active') === 0;
		if($reCaptchaActive) {
			return '0: Hubo problemas al intentar registrarle, hay campos vac&iacute;os, inv&aacute;lidos o no se le permite el registro.';
		}
		return null;
	}

	private function insertMuroMessage(int $uid, string $message) {
	   $m_id = DB::insert('u_muro', [
	   	'p_user' => $uid,
			'p_user_pub' => 1,
			'p_date' => time(),
			'p_body' => $message,
			'p_type' => 1
	   ]);
	   DB::insert('u_monitor', [
	   	'user_id' => $uid,
			'obj_user' => 1,
			'obj_uno' => $m_id,
			'not_type' => 12,
			'not_total' => 1,
			'not_menubar' => 1,
			'not_monitor' => 1
	   ]);
	}

	private function insertMessagePrivate(int $uid, string $message, string $titulo) {
		$preview = substr($message, 0, 75);
	   $mp_id = DB::insert('u_mensajes', [
	   	'mp_to' => $uid,
			'mp_from' => 1,
			'mp_subject' => $titulo,
			'mp_preview' => $preview,
			'mp_date' => time()
	   ]);
	   DB::insert('u_respuestas', [
	   	'mp_id' => $mp_id,
			'mr_from' => 1,
			'mr_body' => $message,
			'mr_ip' => $this->myIP,
			'mr_date' => time()
	   ]);
	}

	private function insertAvise(int $uid, string $message, string $titulo) {
	   DB::insert('u_avisos', [
	   	'user_id' => $uid,
			'av_subject' => $titulo,
			'av_body' => $message,
			'av_date' => $time,
			'av_type' => 4
	   ]);
	}

	private function sendMessageWelcome(int $uid, array $tsData = []) {
		$welcome = $this->Core->reCaptchaConfig('c_met_welcome');
		if($welcome > 0 && $welcome < 4) {
			$heading = 'Bienvenid' . (in_array($tsData['user_sexo'], ['none','male']) ? 'o' : 'a'); 
         $message = str_replace(
         	['[usuario]', '[welcome]', '[web]'], 
         	[$tsData['user_nick'], $heading, $this->Core->settings['titulo']], 
	         $this->Core->parseBBCode($this->Core->reCaptchaConfig('c_message_welcome'))
	      );
         //
         $time = time();
         $title = "$heading a {$this->Core->settings['titulo']}";
	      switch($welcome) {
	         case 1: $this->insertMuroMessage($uid, $message); break;
	         case 2: $this->insertMessagePrivate($uid, $message, $title); break;
		 		case 3: $this->insertAvise($uid, $message, $title); break;
			}
		}
	}

	private function sendEmail(int $uid, array $tsData = []) {
		$pin = strtoupper(bin2hex(random_bytes(4))); // ej: A9F3C8D2
		$pinHash = password_hash($pin, PASSWORD_DEFAULT);
		$time = time();
		$expires = $time + 900; // 15 minutos
		#$words = str_split($pin, 1);
		
	   if(!DB::insert('w_activate', [
	   	'user_id' => $uid,
			'user_email' => $tsData['user_email'],
			'code_hash' => $pinHash,
			'expire_at' => $time,
			'type' => 'activation',
			'used' => 0,
			'ip' => $this->myIP,
	   ])) {
			return '0: Ocurri&oacute; un error, int&eacute;ntelo de nuevo.';
		}

		$placeholders = [
			'USERNAME' => $tsData['user_nick'],
			'PASSWORD' => $tsData['user_password'],
			'LINK' => $this->Core->route('url') . "/validar/$pinHash/2/{$tsData['user_email']}",
			'PIN' => $pin,
		];
		// <--
		$Email = new tsEmail($this->Core);
		$Email->asunto = 'activate';
		$Email->sendFast('joelmiguelvalente@gmail.com', $placeholders) OR die('0: Hubo un error al intentar procesar lo solicitado');
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
		$newPassword = (new PasswordHandler)->create($tsData['user_password']);
		// Rango por defecto
		$rango = (int)$this->Core->reCaptchaConfig('c_reg_rango') ?? 3;
		//
		$uid = DB::insert('u_miembros', [
	   	'user_name' => $tsData['user_nick'],
			'user_password' => $newPassword,
			'user_email' => $tsData['user_email'],
			'user_rango' => $rango,
			'user_registro' => $tsData['user_registro']
	   ]);
		if(!$uid) {
			return '0: Ocurrio un error, intentalo ma&aacute;s tarde.';
		}
      // Agregamos datos en diversas tablas
      DB::insert('u_perfil', [
      	'user_id' => $uid, 
      	'user_pais' => 'XX', 
      	'p_avatar' => 0, 
      	'user_sexo' => $tsData['user_sexo']
      ]);
      DB::insert('u_portal', ['user_id' => $uid]);
      DB::insert('u_miembros_sets', ['user_id' => $uid]);
      
      # Generamos automaticamente un avatar
      (new Avatar)->ensure((int)$uid, $tsData['user_nick'], 171717);

		# MENSAJE PARA DAR LA BIENVENIDA BIENVENIDA
		$this->sendMessageWelcome($uid, $tsData);

		// ENVIAMOS EL EMAIL
		if((int)$this->Core->reCaptchaConfig('c_reg_activate') === 0) {
			$this->sendEmail($uid, $tsData);
		} else {
			# Activamos cuenta directamente!
			DB::update('u_miembros', ['user_activo' => 1], 'user_id = :id', ['id' => $uid]);
			# Iniciamos la sesión
			$this->User->loginUser($tsData['user_nick'], $tsData['user_password'], true);
			return "1: Bienvenido a <strong>{$this->Core->settings['titulo']}</strong>, Ahora estas registrado y tu cuenta ha sido activada, podr&aacute;s disfrutar de esta comunidad inmediatamente.<br><br>&iexcl;Muchas gracias!";
		}
	}
}
