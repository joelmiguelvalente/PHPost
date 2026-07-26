<?php

declare(strict_types=1);

/**
 * @package    Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::init('registro', 'guest');

if($tsUser->is_member) {
   Container::get(Response::class)->redirect($Routes->route('url'));
   die;
}

if($ctx->continue()) {

   $registro = $tsCore->reCaptchaConfig();
   $file = ($registro["captcha_provider"] === 'recaptcha') ? 'api' : 'enterprise';
   $key = $registro["public_key"];
   //
   $endpoint = "https://www.google.com/recaptcha/{$file}.js?render={$key}";
   $smarty->assign("service_captcha", $registro["captcha_provider"]);
   $smarty->assign("recaptcha", $endpoint);
   $smarty->assign("publicKey", $key);
   $smarty->assign("tsAbierto", $tsCore->reCaptchaConfig("c_reg_active"));
}

Controller::render($tsAjax, $tsTitle, $tsPage);
