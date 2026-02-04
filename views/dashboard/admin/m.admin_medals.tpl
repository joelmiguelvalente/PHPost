<h1 class="text-xl font-semibold mb-4">Medallas</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Configuraciones guardadas" color="green" show=$tsSave}
	{include "dashboard/Alert.tpl" text=$tsError color="red" show=$tsError}

	{if !$tsAct}
		{if !$tsMedals.medallas}
			{include "dashboard/Alert.tpl" text="No hay medallas." color="orange" show=true}
			<div class="text-center mt-6">
		      <a href="{$tsConfig.url}/admin/medals?act=nueva" class="items-center rounded-md bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary" title="Agregar nueva medalla">Agregar nueva medalla</a>
		   </div>
      {else}
	      <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
				<table class="min-w-full border-collapse text-sm">
					{include "dashboard/table/Thead.tpl" fields=[
						"ID",
						"Imagen",
						"Tipo",
						"T&iacute;tulo",
						"Descripci&oacute;n",
						"Creada por",
						"Fecha",
						"Total",
						"Acciones"
					]}
					<tbody class="divide-y dark:divide-gray-700">
						{foreach from=$tsMedals.medallas item=m}
							<tr class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors" id="medal_id_{$m.medal_id}">
								<td class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors text-center">{$m.medal_id}</td>
								<td class="px-3 py-2"><img src="{$tsRoutes.assets.images}/icons/med/{$m.m_image}_32.png" /></td>
								<td class="px-3 py-2">{if $m.m_type == 1}Usuario{elseif $m.m_type == 2}Post{else}Foto{/if}</td>
								<td class="px-3 py-2">{$m.m_title}</td>
								<td class="px-3 py-2">{$m.m_description}</td>
								<td class="px-3 py-2">{if $m.m_autor == 0}Sistema{else}<a href="{$tsConfig.url}/perfil/{$m.user_name}">{$m.user_name}</a>{/if}</td>
								<td>{$m.m_date|fecha:"short"}</td>
								<td class="px-3 py-2" id="total_med_assig_{$m.medal_id}">{$m.m_total}</td>
								<td class="px-3 py-2">
									<div class="flex justify-center gap-2">
										{include "dashboard/table/Action.tpl" action="medallas.asignar({$m.medal_id})" type="button" title="Asignar Medalla" icon="assignment_turned_in"}
										{include "dashboard/table/Action.tpl" action="medals?act=editar&mid={$m.medal_id}" title="Editar Medalla" icon="edit"}
										{include "dashboard/table/Action.tpl" action="medallas.borrar({$m.medal_id})" type="button" title="Borrar Medalla" icon="delete"}
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
					<tfoot class="bg-gray-50 dark:bg-surface-alt">
						<tr>
							<td colspan="3" class="px-3 py-3 text-left">
								P&aacute;ginas: {$tsAsignaciones.pages}
							</td>
							<td colspan="6" class="px-3 py-3 text-right">
								<a href="{$tsConfig.url}/admin/medals?act=nueva" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm">add</span> Agregar nueva medalla</a>
								<a href="{$tsConfig.url}/admin/medals?act=showassign" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm">visibility</span> Ver medallas asignadas</a>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		{/if}
	{elseif $tsAct == 'showassign'}
	   <div class="overflow-x-auto rounded-md border bg-white dark:bg-surface shadow-sm">
			<table class="min-w-full border-collapse text-sm">
				{include "dashboard/table/Thead.tpl" fields=[
					"ID",
					"Medalla",
					"Tipo",
					"Asignada a",
					"Fecha",
					"IP",
					"Acciones"
				]}
				<tbody class="divide-y dark:divide-gray-700">
					{foreach from=$tsAsignaciones.asignaciones item=m}
						<tr id="assign_id_{$m.id}" class="hover:bg-gray-50 dark:hover:bg-surface-alt transition-colors">
							<td class="px-3 py-2 text-gray-600 dark:text-gray-400">{$m.id}</td>
							<td class="px-3 py-2"><img src="{$tsRoutes.assets.images}/icons/med/{$m.m_image}_32.png" title="{$m.m_title}"/></td>
							<td class="px-3 py-2">{if $m.m_type == 1}Usuario{elseif $m.m_type == 2}Post{else}Foto{/if}</td>
							<td class="px-3 py-2">{if $m.m_type == 1}<a href="{$tsConfig.url}/perfil/{$m.user_name}">@{$m.user_name}</a>{elseif $m.m_type == 2}<a href="{$tsConfig.url}/posts/{$m.c_seo}/{$m.post_id}/{$m.post_title|seo}.html" target="_blank">{$m.post_title}</a>{else}<a href="{$tsConfig.url}/fotos/autor/{$m.foto_id}/{$m.f_title}.html" target="_blank">{$m.f_title}</a>{/if}</td>
							<td class="px-3 py-2">{$m.m_date|hace:true}</td>
							<td class="px-3 py-2">{$m.medal_ip}</td>
							<td class="px-3 py-2">
								<div class="flex justify-center gap-2">
									{include "dashboard/table/Action.tpl" action="medallas.borrar_asignacion({$m.id}, {$m.medal_id})" type="button" title="Borrar Asignaci&oacute;n" icon="delete"}
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
				<tfoot class="bg-gray-50 dark:bg-surface-alt">
					<tr>
						<td colspan="7" class="px-3 py-3 text-right">
							P&aacute;ginas: {$tsAsignaciones.pages}
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	{elseif $tsAct == 'nueva' || $tsAct == 'editar'}
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				$('#med_img').on('change', () => {
					let icono = $("#med_img option:selected").val();
					$('#c_icon').css({
						"background": 'url(\'{$tsRoutes.assets.images}/icons/med/'+icono+'_32.png\') no-repeat center',
						"background-size": '18px'
					})
				});
				// {literal}
				$('.radio-types input').on('click', e => {
					const id = e.target.id;
					$('select#ai_cond_post, select#ai_cond_foto, select#ai_cond_user, select#ai_cond_user_rango_span').slideUp();
					$(`select#${id}`).slideDown();
				});
				const medCondUser = $('select[name=m_cond_user]');
				medCondUser.on('change', () => {
					let isNine = parseInt(medCondUser.val()) === 9;
					$('#ai_cond_user_rango')[(isNine ? 'slideDown' : 'slideUp')]();
					$('#ai_cant')[(isNine ? 'slideUp' : 'slideDown')]();
				});
				
				// {/literal}
			});
		</script>
		<form method="post" autocomplete="off" class="space-y-6">
			<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
				{include "dashboard/Legend.tpl" text="{if $tsAct == 'nueva'}Nueva{else}Editar{/if} medalla"}

				{include "dashboard/FormGroup.tpl" id="m_title" label="T&iacute;tulo de la medalla" required=true name="m_title" value=$tsMed.m_title placeholder="Staff"}

				{include "dashboard/FormGroupTextarea.tpl" id="m_description" label="Descripci&oacute;n" name="m_description" value=$tsMed.m_description helper="Describe el motivo por el cual el contenido gana esta medalla."}

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-2 mb-3">
				  	<label for="m_image" class="font-medium text-gray-700 dark:text-gray-300">Icono de la categor&iacute;a</label>
				  	<div class="md:col-span-2 flex items-center">
				    	<div style="background:url({$tsRoutes.assets.images}/icons/med/{if $tsMed.m_image}{$tsMed.m_image}{else}{$tsIcons.0}{/if}_32.png) no-repeat center center;background-size:32px;display:block;width:32px;height:32px;margin-right:10px;" id="c_icon"></div>
				  		<select name="m_image" id="m_image" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary" style="width: 160px;">
							{html_options values=$tsIcons output=$tsIcons selected=$tsMed.m_image}
						</select>
				  	</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-2 mb-3">
				  	<label for="rSpecial" class="font-medium text-gray-700 dark:text-gray-300">Condici&oacute;n especial
					   <div class="md:col-span-2 flex items-center space-x-6 radio-types">
						 	{include "dashboard/Check.tpl" type="radio" name="m_type" value=1 checked=$tsMed.m_type label="Usuario" id="ai_cond_user"}
						 	{include "dashboard/Check.tpl" type="radio" name="m_type" value=2 checked=$tsMed.m_type label="Post" id="ai_cond_post"}
						 	{include "dashboard/Check.tpl" type="radio" name="m_type" value=3 checked=$tsMed.m_type label="Foto" id="ai_cond_foto"}
						</div>
				  	</label>
				  	<div class="md:col-span-2 flex items-center">
				    	<div class="flex items-center w-full">						
							<input type="text" id="ai_cant" name="m_cant" style="width:100px" maxlength="5" value="{$tsMed.m_cant}" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"{if $tsMed.m_cond_user == 9} style="display:none;"{/if} />
							<select name="m_cond_user" id="ai_cond_user" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary" style="width:175px;{if $tsMed.m_type != 1}display:none;{/if}">
								<option value="1"{if $tsMed.m_cond_user == 1} selected{/if}>Puntos</option>
								<option value="2"{if $tsMed.m_cond_user == 2} selected{/if}>Seguidores</option>
								<option value="3"{if $tsMed.m_cond_user == 3} selected{/if}>Siguiendo</option>
								<option value="4"{if $tsMed.m_cond_user == 4} selected{/if}>Comentarios en posts</option>
								<option value="5"{if $tsMed.m_cond_user == 5} selected{/if}>Comentarios en fotos</option>
								<option value="6"{if $tsMed.m_cond_user == 6} selected{/if}>Posts</option>
								<option value="7"{if $tsMed.m_cond_user == 7} selected{/if}>Fotos</option>
								<option value="8"{if $tsMed.m_cond_user == 8} selected{/if}>Medallas</option>
								<option value="9"{if $tsMed.m_cond_user == 9} selected{/if}>Rango</option>
							</select>
							<select name="m_cond_user_rango" id="ai_cond_user_rango" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary"{if $tsMed.m_type != 1 || $tsMed.m_cond_user != 9} style="display:none;"{/if} onchange="if($('#ai_cond_user').val() != 9) $('#ai_cond_user_rango').slideUp();">
								{foreach from=$tsRangos item=r}
									<option value="{$r.rango_id}" style="color:#{$r.r_color}" {if $r.rango_id == $tsMed.m_cond_user_rango}selected{/if}>{$r.r_name}</option>
								{/foreach}
							</select>
							<select name="m_cond_post" id="ai_cond_post" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary" style="width:175px;{if $tsMed.m_type != 2}display:none;{/if}">
								<option value="1"{if $tsMed.m_cond_post == 1} selected{/if}>Puntos</option>
								<option value="2"{if $tsMed.m_cond_post == 2} selected{/if}>Seguidores</option>
								<option value="3"{if $tsMed.m_cond_post == 3} selected{/if}>Comentarios</option>
								<option value="4"{if $tsMed.m_cond_post == 4} selected{/if}>Favoritos</option>
								<option value="5"{if $tsMed.m_cond_post == 5} selected{/if}>Denuncias</option>
								<option value="6"{if $tsMed.m_cond_post == 6} selected{/if}>Visitas</option>
								<option value="7"{if $tsMed.m_cond_post == 7} selected{/if}>Medallas</option>
								<option value="8"{if $tsMed.m_cond_post == 8} selected{/if}>veces compartido</option>
							</select>
							<select name="m_cond_foto" id="ai_cond_foto" class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-surface px-3 py-2 text-sm focus:ring-2 focus:ring-primary" style="width:175px;{if $tsMed.m_type != 3}display:none;{/if}">
								<option value="1"{if $tsMed.m_cond_foto == 1} selected{/if}>Puntos positivos</option>
								<option value="2"{if $tsMed.m_cond_foto == 2} selected{/if}>Puntos negativos</option>
								<option value="3"{if $tsMed.m_cond_foto == 3} selected{/if}>Comentarios</option>
								<option value="4"{if $tsMed.m_cond_foto == 4} selected{/if}>Visitas</option>
								<option value="5"{if $tsMed.m_cond_foto == 5} selected{/if}>Medallas</option>
							</select>
						</div>
				  	</div>
				</div>
											<hr />
										 {if $tsAct == 'nueva'}<p><input type="submit" name="save" value="Crear medalla" class="btn_g"/></p>{else}<p><input type="submit" name="edit" value="Guardar" class="btn_g"/>{/if}
										</fieldset>
										</form>
									{/if}
								</div>