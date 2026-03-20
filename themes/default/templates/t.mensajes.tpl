{include "main_header.tpl"}
	<div class="grid gap-3" style="grid-template-columns: 250px 1fr;">
		
		{include "m.mensajes_menu.tpl"}
	
		<div>
			<div style="display: none;" id="m-mensaje"></div>
			<div class="box">
				<div class="box-header">
					<span class="box_txt">Mensajes</span>
					<form class="box_icon" method="get" action="{$tsConfig.url}/mensajes/search/">
						<input type="text" name="qm" placeholder="Buscar por asunto" value="{$tsMensajes.texto}" class="form-control onblur_effect"/>
					</form>
				</div>
				<div class="box-content" id="mensajes">
					{if $tsAction == '' || $tsAction == 'enviados' || $tsAction == 'respondidos' || $tsAction == 'search'}
						{include "m.mensajes_list.tpl"}
					{elseif $tsAction == 'leer'}
						<div class="grid gap-3 grid-leer">
							{include "m.mensajes_leer.tpl"}
						</div>
					{elseif $tsAction == 'avisos'}
						{include "m.mensajes_avisos.tpl"}
					{/if}
				</div>
			</div>
		</div>
	</div>
				
{include "main_footer.tpl"}