<?php

declare(strict_types=1);

/**
 * @package    PHPost/Utils
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
    exit('No se permite el acceso directo al script');
}

// Pagina solicitada
$smarty->assign("tsPage", $tsPage);

# Por si quieren cambiar la pagina de error
# Si no encuentra la plantilla t.$tsPage.tpl
# Mostrar esta pagina
$smarty->templateError = 'views/error/404.html';

$smarty->setTheme(TS_TEMA);
$smarty->setPage($tsPage);

$smarty->load($tsPage, (!isset($useExtension) ? true : $useExtension));
