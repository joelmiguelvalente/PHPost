<?php

/**
 * @name registro.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";
$tsTitle = "{$tsCore->settings['titulo']} - {$tsCore->settings['slogan']}";

/**
 * Inicializamos variable
 */

$ctx = Controller::page('registro')->guest();
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

if($tsUser->is_member) {
   header("Location: {$tsCore->route('url')}");
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

if($tsAjax) {
	$smarty->assign("tsTitle", $tsTitle);
   require_once TS_ROOT . "/footer.php";
}
