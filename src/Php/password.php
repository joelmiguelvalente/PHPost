<?php

declare(strict_types=1);

/**
 * @package    Php
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

require_once dirname(__DIR__, 2) . "/header.php";

$ctx = Controller::init('reset_password', 'guest');

$tsType  = (int)($_GET['type'] ?? 0);
$email   = $_GET['email'] ?? '';
$key     = $_GET['hash'] ?? '';
$tsTitle = $tsType === 1 ? 'Recuperar contraseña' : 'Validar cuenta';

$action = Container::get(tsPassword::class);
$result = $action->process($tsType, $email, $key, $_POST);

if (isset($result['message'])) {
    $smarty->assign("tsAviso", $result['message']);
} elseif (isset($result['form'])) {
    $smarty->assign("tsForm", $result['form']);
}

Controller::render($tsAjax, $tsTitle, $tsPage);
