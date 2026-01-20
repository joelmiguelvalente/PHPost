1:
<div id="perfil_info" status="activo">
	<div class="widget big-info clearfix">
		<div class="title-w clearfix">
			<h3>Informaci&oacute;n de {$tsUsername}</h3>
		</div>
		<ul>
			<li><label>Pa&iacute;s</label><strong>{$tsPais}</strong></li>
			{if $tsPerfil.p_sitio}<li><label>Sitio Web</label><strong><a rel="nofollow" href="{$tsPerfil.p_sitio}">{$tsPerfil.p_sitio}</a></strong></li>{/if}			
			<li><label>Es usuario desde</label><strong>{$tsPerfil.user_registro|hace:true}</strong></li>
			<li><label>&Uacute;ltima vez activo</label><strong>{$tsPerfil.user_lastactive|fecha}</strong></li>			
		</ul>
	</div>
</div>