<!DOCTYPE html>
<html lang="es">
<head>
<title>{$tsTitle}</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="{$tsRoutes.tema.images}/favicon.ico" type="image/x-icon" />
{load file=['estilo','phpost','extras','wysibb',$tsPage] type="css"}
<script>
const global_data = {
   app: {
      domain:'{$tsRoutes.domain}',
      title: '{$tsConfig.titulo}',
      slogan: '{$tsConfig.slogan}'
   },
   user_key: {$tsUser->uid},
   postid: {$tsPost.post_id|default:0},
   fotoid: {$tsFoto.foto_id|default:0},
   notifica: {$tsNots},
   mensaje: {$tsMPs}
};
const route = {
   url:'{$tsConfig.url}',
   assets:'{$tsRoutes.assets.base}',
   img:'{$tsRoutes.tema.images}',
   smiles:'{$tsConfig.url}/files/smiles'
}
</script>
{load file=['jquery.min','jquery.plugins','acciones','wysibb',$tsPage] type="js"}
{if $tsUser->is_admod || $tsUser->permisos.moacp || $tsUser->permisos.most || $tsUser->permisos.moayca || $tsUser->permisos.mosu || $tsUser->permisos.modu || $tsUser->permisos.moep || $tsUser->permisos.moop || $tsUser->permisos.moedcopo || $tsUser->permisos.moaydcp || $tsUser->permisos.moecp}
<script src="{$tsRoutes.assets.js}/moderacion.js?{$smarty.now}" defer></script>
{/if}
{if $tsConfig.c_allow_live}
<script src="{$tsRoutes.assets.js}/live.js?{$smarty.now}" defer></script>
{/if}
</head>
<body>
   
   {if $tsUser->is_admod}{$tsConfig.install}{/if}

   <div id="loading" style="display:none">
      <img src="{$tsRoutes.tema.images}/ajax-loader.gif" alt="Cargando"> Procesando...
   </div>

   <div id="swf"></div>
   <div id="js" style="display:none"></div>
   <div id="mask"></div>
   <div id="mydialog"></div>
   <div class="UIBeeper" id="BeeperBox"></div>

   <div id="brandday">
      <div class="rtop"></div>
      <main id="maincontainer">
       	<!--MAIN CONTAINER-->
         <div id="head">
         	<div id="logo">
               <a id="logoi" title="{$tsConfig.titulo}" href="{$tsConfig.url}">
                  <img border="0" align="top" title="{$tsConfig.titulo}" alt="{$tsConfig.titulo}" src="{$tsRoutes.tema.images}/space.gif">
               </a>
            </div>
            <div id="banner">
               {if $tsPage == 'posts' && $tsPost.post_id}
                  {include file='m.global_search.tpl'}
               {else}
                  {include file='m.global_ads_468.tpl'}
               {/if}
            </div>
         </div>
         <div id="contenido_principal">
            {include file='head_menu.tpl'}
            {include file='head_submenu.tpl'}
            {include file='head_noticias.tpl'}
            <section id="cuerpocontainer">
            <!--Cuperpo-->