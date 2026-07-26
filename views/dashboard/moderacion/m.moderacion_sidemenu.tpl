<div class="sidebar-group">
	{include "dashboard/aside/Toggle.tpl" icon="dashboard" label="Principal"}
   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
   	{include "dashboard/aside/Item.tpl" label="Centro de Moderación"}
   </div>
</div>
<div class="sidebar-group">
  	{include "dashboard/aside/Toggle.tpl" icon="report" label="Denuncias"}
   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
   	{include "dashboard/aside/Item.tpl" link="posts" label="Post" total=$tsModerar.repposts flex=true}
   	{include "dashboard/aside/Item.tpl" link="fotos" label="Fotos" total=$tsModerar.repfotos flex=true}
   	{include "dashboard/aside/Item.tpl" link="mps" label="Mensajes" total=$tsModerar.repmps flex=true}
   	{include "dashboard/aside/Item.tpl" link="users" label="Usuarios" total=$tsModerar.repusers flex=true}
   </div>
</div>
{if $tsUser->is_admod || 
$tsUser->permiso('moderacion.usuarios.ver_baneados') || 
$tsUser->permiso('moderacion.usuarios.buscador')}
	<div class="sidebar-group">
  		{include "dashboard/aside/Toggle.tpl" icon="account_tree" label="Gestión"}
	   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
	   	{if $tsUser->is_admod || $tsUser->permiso('moderacion.usuarios.ver_baneados')}
	   		{include "dashboard/aside/Item.tpl" link="banusers" label="Usuarios" total=$tsModerar.suspusers flex=true}
	   	{/if}
	   	{if $tsUser->is_admod || $tsUser->permiso('moderacion.usuarios.buscador')}
	   		{include "dashboard/aside/Item.tpl" link="buscador" label="Buscador de Contenido"}
	   	{/if}
	   </div>
	</div>
{/if}
{if $tsUser->is_admod || 
$tsUser->permiso('moderacion.posts.papelera') || 
$tsUser->permiso('moderacion.fotos.papelera')}
	<div class="sidebar-group">
  		{include "dashboard/aside/Toggle.tpl" icon="delete_sweep" label="Papelera de Reciclaje"}
	   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
	   	{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.papelera')}
	   		{include "dashboard/aside/Item.tpl" link="pospelera" label="Post eliminados" total=$tsModerar.pospelera flex=true}
	   	{/if}
	   	{if $tsUser->is_admod || $tsUser->permiso('moderacion.fotos.papelera')}
	   		{include "dashboard/aside/Item.tpl" link="fopelera" label="Fotos eliminadas" total=$tsModerar.fospelera flex=true}
	   	{/if}
	   </div>
	</div>
{/if}
{if $tsUser->is_admod || 
$tsUser->permiso('moderacion.posts.desaprobados') || 
$tsUser->permiso('moderacion.comentarios.desaprobados')}
	<div class="sidebar-group">
  		{include "dashboard/aside/Toggle.tpl" icon="disabled_visible" label="Contenido desaprobado"}
	   <div class="sidebar-submenu mt-1 ml-8 space-y-1 hidden">
	   	{if $tsUser->is_admod || $tsUser->permiso('moderacion.posts.desaprobados')}
	   		{include "dashboard/aside/Item.tpl" link="revposts" label="Post" total=$tsModerar.revposts flex=true}
	   	{/if}
	   	{if $tsUser->is_admod || $tsUser->permiso('moderacion.comentarios.desaprobados')}
	   		{include "dashboard/aside/Item.tpl" link="comentarios" label="Comentarios" total=$tsModerar.revcomentarios flex=true}
	   	{/if}
	   </div>
	</div>
{/if}
