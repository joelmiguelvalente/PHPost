<div class="box">
	<div class="box-header">
		<span class="box_txt" title="&Uacute;ltimos comentarios">&Uacute;ltimos comentarios</span>
		<div class="box_icon">
			<div onclick="actualizar_comentarios()" class="size9">
				<span class="systemicons actualizar"></span>
			</div>
		</div>
	</div>
	<div class="box-content" id="ult_comm" style="height: 330px;">
		{include "views/api/p.posts.last-comentarios.tpl"}
	</div>
</div>