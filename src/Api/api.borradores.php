<?php

declare(strict_types=1);

/**
 * @package    Api
 * @author     PHPost Team
 * @copyright  2026
 */

defined('TS_HEADER') || exit('No se permite el acceso directo al script.');

const ACTIONS = [
	'borradores'            => ['nivel' => 2, 'template' => 'home', 'ajax' => false],
	'borradores-agregar'    => ['nivel' => 2, 'template' => '', 'ajax' => true],
	'borradores-guardar'    => ['nivel' => 2, 'template' => '', 'ajax' => true],
	'borradores-eliminar'   => ['nivel' => 2, 'template' => '', 'ajax' => true],
	'borradores-get'        => ['nivel' => 2, 'template' => '', 'ajax' => true],
];

if (!array_key_exists($action, ACTIONS)) {
	http_response_code(403);
	exit('Acción inválida');
}

$config = ACTIONS[$action];

$tsLevel = $config['nivel'];
$tsAjax  = (int) $config['ajax'];
$tsPage  = sprintf('p.borradores.%s', $config['template']);

// DEPENDE EL NIVEL
$tsLevelMsg = $tsUser->setLevel($tsLevel, true);

if ($tsLevelMsg != 1) {
	echo '0: ' . $tsLevelMsg['mensaje'];
	die();
}

$tsBorradores = Container::get(tsBorradores::class);

// CODIGO
match ($action) {
	'borradores' => (static function() use ($tsBorradores, $smarty) {
		$tsBorradores = $tsBorradores->getDrafts();
		$smarty->assign("tsBorradores", $tsBorradores);
	})(),
	'borradores-get' => (static function() use ($tsBorradores) {
		$_GET['action'] = $_POST['borrador_id'];
		$tsBorrador = $tsBorradores->getDraft(0);
		echo '1: <div style="text-align:left; padding-left:15px;">
	<strong>Título:</strong><br />
	<input type="text" value="' . $tsBorrador['b_title'] . '" style="width:480px" onfocus="this.select()" /><br />
	<strong>Cuerpo:</strong><br />
	<textarea style="width:490px; height:140px" onfocus="this.select()">' . $tsBorrador['b_body'] . '</textarea>
</div>';
	})(),
	'borradores-agregar' => print $tsBorradores->newDraft(),
	'borradores-guardar' => print $tsBorradores->newDraft(true),
	'borradores-eliminar' => print $tsBorradores->delDraft(),
};
