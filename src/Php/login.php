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

$ctx = Controller::init('login', 'guest');

if($tsUser->is_member) {
   Container::get(Response::class)->redirect($Routes->route('url'));
   die;
}

if($ctx->continue()) {}

Controller::render($tsAjax, $tsTitle, $tsPage);
