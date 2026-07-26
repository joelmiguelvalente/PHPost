
<!DOCTYPE html>
<html lang="es">
<head>
<title>{$tsTitle}</title>
{hook name="header"}
{meta 
	description="{Config::app('app.meta.description')}"
	url=$tsRoutes.canonical
	favicon_name="main-#.png"
	favicon_path="{$tsRoutes['assets:images']}/phpost"
	favicon_sizes=[16, 32, 64]
	image="{$tsRoutes['assets:images']}/phpost/banner-social.png"
	og=true
	twitter=true
	json_ld=true
	keywords="{Config::app('app.meta.keywords')}"
	data=[
		'robots' => 'index,follow',
		'generator' => 'Miguel92'
	]
}
{load file=['estilo','phpost','extras','dialog',$tsPage] type="css" cache=true}
<script nonce="{$csp_nonce}">
const global_data = {
	app: {
		domain: {$tsRoutes.domain|json_encode nofilter},
		title: {$tsConfig.titulo|json_encode nofilter},
		slogan: {$tsConfig.slogan|json_encode nofilter}
	},
	csrf_token: {$tsCsrf|json_encode nofilter},
	user_key: {$tsUser->uid},
	postid: {$tsPost.post_id|default:0},
	fotoid: {$tsFoto.foto_id|default:0},
	notifica: {$tsNots|default:0},
	mensaje: {$tsMPs|default:0},
	muro: {
		stream: {
			total: {$tsMuro.total|default:0}
		}
	}
};
const route = {
	url: {$tsConfig.url|json_encode nofilter},
	canonical: {$tsRoutes.canonical|json_encode nofilter},
	assets: {$tsRoutes["assets:base"]|json_encode nofilter},
	img: {$tsRoutes["tema:images"]|json_encode nofilter},
	smiles: {$tsRoutes["assets:images"]|json_encode nofilter} + '\/smiles'
}
</script>
{load file=['jquery.min','jquery.plugins','acciones',$tsPage] type="js"}
{if $tsUser->is_admod || $tsUser->canModerate()}
<script src="{$tsRoutes['assets:js']}/moderacion.js" defer></script>
{/if}
{if $tsConfig.c_allow_live}
<link rel="stylesheet" href="{$tsRoutes['assets:css']}/live.css">
<script src="{$tsRoutes['assets:js']}/live.js" defer></script>
{/if}
</head>
<body>

	<div id="loading" style="display:none">
		<img src="{$tsRoutes['tema:images']}/ajax-loader.gif" alt="Cargando"> Procesando...
	</div>

	<div id="swf"></div>
	<div id="js" style="display:none"></div>
	<div class="UIBeeper" id="BeeperBox" role="log" aria-live="polite" aria-label="Notificaciones"></div>

	<div id="brandday">
		<main id="maincontainer">
			<!--MAIN CONTAINER-->
			<div id="head">
				<a id="logo" title="{$tsConfig.titulo}" href="{$tsConfig.url}"></a>
				<div id="banner">
					{if $tsPage == 'posts' && $tsPost.post_id}
						{include "m.global_search.tpl"}
					{else}
						{include "m.global_ads_468.tpl"}
					{/if}
				</div>
			</div>
			<div id="contenido_principal">
				{include "head_menu.tpl"}
				{include "head_submenu.tpl"}
				{include "head_noticias.tpl"}
				<section id="cuerpocontainer">
				<!--Cuperpo-->
