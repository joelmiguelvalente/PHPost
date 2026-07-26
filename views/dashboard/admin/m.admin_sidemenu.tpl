<div class="sidebar-group">
  	{include "dashboard/aside/Toggle.tpl" icon="dashboard" label="General"}
   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		{include "dashboard/aside/Item.tpl" label="Centro de Administración"}
		{include "dashboard/aside/Item.tpl" link="creditos" label="Soporte y Créditos"}
		{include "dashboard/aside/Item.tpl" link="dbmanager" label="DB Manager"}
   </div>
</div>
<div class="sidebar-group">
  	{include "dashboard/aside/Toggle.tpl" icon="settings" label="Configuración"}
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		{include "dashboard/aside/Item.tpl" link="configs" label="Configuración"}
		{include "dashboard/aside/Item.tpl" link="registro" label="Configuración del registro"}
		{include "dashboard/aside/Item.tpl" link="phpmailer" label="Configuración del phpmailer"}
		{include "dashboard/aside/Item.tpl" link="temas" label="Temas y apariencia"}
		{include "dashboard/aside/Item.tpl" link="imageprovider" label="Proveedores de imagenes"}
		{include "dashboard/aside/Item.tpl" link="news" label="Noticias"}
		{include "dashboard/aside/Item.tpl" link="ads" label="Publicidad"}
	</div>
</div>
<div class="sidebar-group">
  	{include "dashboard/aside/Toggle.tpl" icon="security" label="Control"}
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		{include "dashboard/aside/Item.tpl" link="medals" label="Medallas"}
		{include "dashboard/aside/Item.tpl" link="afs" label="Afiliados"}
		{include "dashboard/aside/Item.tpl" link="stats" label="Estadísticas"}
		{include "dashboard/aside/Item.tpl" link="blacklist" label="Bloqueos"}
		{include "dashboard/aside/Item.tpl" link="badwords" label="Censuras"}
	</div>
</div>
<div class="sidebar-group">
  	{include "dashboard/aside/Toggle.tpl" icon="article" label="Contenido"}
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		{include "dashboard/aside/Item.tpl" link="posts" label="Todos los Posts"}
		{include "dashboard/aside/Item.tpl" link="fotos" label="Todas las Fotos"}
		{include "dashboard/aside/Item.tpl" link="cats" label="Categorías"}
	</div>
</div>
<div class="sidebar-group">
  	{include "dashboard/aside/Toggle.tpl" icon="group" label="Usuarios"}
	<div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
		{include "dashboard/aside/Item.tpl" link="users" label="Todos los Usuarios"}
		{include "dashboard/aside/Item.tpl" link="sesiones" label="Sesiones"}
		{include "dashboard/aside/Item.tpl" link="nicks" label="Cambios de Nicks"}
		{include "dashboard/aside/Item.tpl" link="rangos" label="Rangos de Usuarios"}
	</div>
</div>
