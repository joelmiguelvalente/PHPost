<div class="box">
	<div class="box-header">
		<span class="box_txt">Men&uacute;</span>
	</div>
	<div class="box-content" id="admin_menu">
		<div id="mp-menu" class="cat-list">
			<div class="cat-list_item mp_inbox{if $tsAction == ''} active{/if}"><a href="{$tsConfig.url}/mensajes/">Mensajes Recibidos</a></div>
			<div class="cat-list_item mp_send{if $tsAction == 'enviados'} active{/if}"><a href="{$tsConfig.url}/mensajes/enviados/">Mensajes Enviados</a></div>
			<div class="cat-list_item mp_return{if $tsAction == 'respondidos'} active{/if}"><a href="{$tsConfig.url}/mensajes/respondidos/">Mensajes Respondidos</a></div>
		
			{if $tsAction == 'search'}
				<div class="cat-list_item mp_search active"><a href="#">Resultados de b&uacute;squeda</a></div>
			{/if}                         
			<div class="cat-list_item mp_new"><span role="button" onclick="mensaje.nuevo();">Escribir Nuevo Mensaje</span></div>
		
			<div class="cat-list_item mp_avisos{if $tsAction == 'avisos'} active{/if}"><a href="{$tsConfig.url}/mensajes/avisos/">Avisos/Alertas</a></div>
		</div>
	</div>
</div