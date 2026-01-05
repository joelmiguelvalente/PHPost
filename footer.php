<?php 

if (!defined('TS_HEADER')) 
    exit('No se permite el acceso directo al script');

/**
 * El footer permite mostrar la plantilla
 *
 * @name    footer.php
 * @package Smarty 4.0
 * @link https://github.com/smarty-php/smarty
 * @author  PHPost Team
 * @author  Miguel92
 * @copyright PHPost 2022
 * @version v4.0
*/

// Pagina solicitada
$smarty->assign("tsPage", $tsPage);
# Por si quieren cambiar la pagina de error
# Si no encuentra la plantilla t.$tsPage.tpl
# Mostrar esta pagina
$smarty->templateError = '404.html';

$smarty->setTheme(TS_TEMA);
$smarty->setPage($tsPage);

$smarty->load($tsPage);