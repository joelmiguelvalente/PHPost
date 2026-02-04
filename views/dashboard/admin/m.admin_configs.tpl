<h1 class="text-xl font-semibold mb-4">Caracter&iacute;sticas y Opciones</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Configuraciones guardadas" color="green" show=$tsSave}
	<form method="POST" autocomplete="off" class="space-y-6">
		<fieldset class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-surface shadow-sm">
			{include "dashboard/Legend.tpl" text="Configuraci&oacute;n del Sitio"}

			{include "dashboard/FormGroup.tpl" id="titulo" label="Nombre del sitio" required=true name="titulo" value=$tsConfig.titulo}

			{include "dashboard/FormGroup.tpl" id="slogan" label="Descripci&oacute;n del Sitio" required=true name="slogan" value=$tsConfig.slogan}

			{include "dashboard/FormGroup.tpl" type="url" id="url" label="Direcci&oacute;n del Sitio" required=true name="url" value=$tsConfig.url}
	
			<!-- Modo mantenimiento -->
		   <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2 mb-3">
		      <div>
		        	<label class="font-medium text-gray-700 dark:text-gray-300">Modo mantenimiento</label>
		        	<p class="mt-1 text-xs text-gray-500">Hace el sitio inaccesible y muestra un mensaje opcional.</p>
		      </div>
		      <div class="md:col-span-2 space-y-3">
		        	<div class="flex gap-6">
		        		{include "dashboard/Check.tpl" type="radio" name="offline" value=1 checked=$tsConfig.offline label="Sí"}
		        		{include "dashboard/Check.tpl" type="radio" name="offline" value=0 checked=$tsConfig.offline label="No"}
		        	</div>
		        	{include "dashboard/Input.tpl" name="offline_message" id="offline_message" value=$tsConfig.offline_message placeholder="Mensaje de mantenimiento (opcional)"}
		      </div>
		   </div>

			<hr class="my-3" />

			{include "dashboard/FormGroup.tpl" id="c_last_active" label="Usuario online" helper="Tiempo que debe trascurrir para considerar que un usuario est&aacute; en linea." name="c_last_active" value=$tsConfig.c_last_active group=true suffix="min"}

			{include "dashboard/FormGroup.tpl" id="c_stats_cache" label="Estad&iacute;sticas en buffer" helper="Tiempo que debe trascurrir para considerar que un usuario est&aacute; en linea." name="c_stats_cache" value=$tsConfig.c_stats_cache group=true suffix="min"}

			{include "dashboard/FormGroup.tpl" type="radio" id="c_allow_sess_ip" label="Login por IP" helper="Por seguridad cada que un usuario cambie de IP se le pedir&aacute; loguearse nuevamente." name="c_allow_sess_ip" checked=$tsConfig.c_allow_sess_ip labels=["Sí", "No"] values=[1,0]}

			{include "dashboard/FormGroup.tpl" type="radio" id="c_count_guests" label="Los visitantes suman estad&iacute;sticas" helper="Contar a los visitantes en las estad&iacute;sticas generales." name="c_count_guests" checked=$tsConfig.c_count_guests labels=["Sí", "No"] values=[1,0]}

			{include "dashboard/FormGroup.tpl" type="radio" id="c_hits_guest" label="Los visitantes suman visitas" helper="Contar las visitas de los visitantes en posts y fotos." name="c_hits_guest" checked=$tsConfig.c_hits_guest labels=["Sí", "No"] values=[1,0]}

			<hr class="my-3" />

			{include "dashboard/FormGroup.tpl" type="radio" id="c_allow_firma" label="Firma de usuario" helper="Las firmas de los usuarios son visibles en los post." name="c_allow_firma" checked=$tsConfig.c_allow_firma labels=["Sí", "No"] values=[1,0]}
			
			<hr class="my-3" />

			{include "dashboard/FormGroup.tpl" type="radio" id="c_allow_upload" label="Carga externa" helper="Si cuentas con un servidor de pago o la librer&iacute;a CURL puedes subir im&aacute;genes remotamente a imgur.com" name="c_allow_upload" checked=$tsConfig.c_allow_upload labels=["Sí", "No"] values=[1,0]}

			{include "dashboard/FormGroup.tpl" type="radio" id="c_allow_portal" label="Activar portal" helper="Los usuarios podr&aacute;n tener un inicio perzonalizado." name="c_allow_portal" checked=$tsConfig.c_allow_portal labels=["Sí", "No"] values=[1,0]}
			
			{include "dashboard/FormGroup.tpl" type="radio" id="c_fotos_private" label="Secci&oacute;n de fotos oculta" helper="Si est&aacute; activado, los visitantes no podr&aacute;n ver la secci&oacute;n fotos." name="c_fotos_private" checked=$tsConfig.c_fotos_private labels=["Sí", "No"] values=[1,0]}
			
			{include "dashboard/FormGroup.tpl" type="radio" id="c_see_mod" label="Vista moderativa amplia" helper="Si est&aacute; activado, el equipo de moderaci&oacute;n podr&aacute; ver, diferenciado por colores, los distintos estados de los posts." name="c_see_mod" checked=$tsConfig.c_see_mod labels=["Sí", "No"] values=[1,0]}
			
			{include "dashboard/FormGroup.tpl" type="radio" id="c_desapprove_post" label="Revisi&oacute;n de posts tras su publicaci&oacute;n" helper="Si est&aacute; activado, el equipo de moderaci&oacute;n deber&aacute; aprobar un post antes de que &eacute;ste sea publicado." name="c_desapprove_post" checked=$tsConfig.c_desapprove_post labels=["Sí", "No"] values=[1,0]}
			
			<hr class="my-3" />

			{include "dashboard/FormGroup.tpl" type="radio" id="c_keep_points" label="Mantener los puntos" helper="Al momento de recargar los puntos, si est&aacute; habilitado se conservar&aacute;n los puntos que el usuario no haya gastado los puntos en el d&iacute;, si est&aacute; deshabilitado, se restablecer&aacute;n a los puntos asignados para cada rango." name="c_keep_points" checked=$tsConfig.c_keep_points labels=["Sí", "No"] values=[1,0]}

			{include "dashboard/FormGroup.tpl" type="radio" id="c_allow_live" label="Notificaciones Live" helper="Los usuarios podr&aacute;n ver en tiempo real sus notificaciones. (Esta opci&oacute;n puede consumir un poco m&aacute;s de recursos.)" name="c_allow_live" checked=$tsConfig.c_allow_live labels=["Sí", "No"] values=[1,0]}

			{include "dashboard/FormGroup.tpl" type="number" id="c_max_nots" label="M&aacute;ximo de notificaciones" helper="Cuantas notificaciones puede recibir un usuario." name="c_max_nots" value=$tsConfig.c_max_nots group=true suffix="max" maxlength=3}

			{include "dashboard/FormGroup.tpl" type="number" id="c_max_acts" label="M&aacute;ximo de actividades" helper="Cuantas actividades puede registrar un usuario." name="c_max_acts" value=$tsConfig.c_max_acts group=true suffix="max" maxlength=3}
			
			<hr class="my-3" />

			{include "dashboard/FormGroup.tpl" type="number" id="c_max_posts" label="Posts por p&aacute;gina" helper="N&uacute;mero m&aacute;ximo de posts a mostrar en cada p&aacute;gina de la home." name="c_max_posts" value=$tsConfig.c_max_posts group=true suffix="max" maxlength=3}

			{include "dashboard/FormGroup.tpl" type="number" id="c_max_com" label="Comentarios por post" helper="N&uacute;mero m&aacute;ximo de comentarios por p&aacute;gina en los post." name="c_max_com" value=$tsConfig.c_max_com group=true suffix="max" maxlength=3}

			{include "dashboard/FormGroup.tpl" id="c_allow_points" label="Puntos por post" helper="N&uacute;mero m&aacute;ximo de puntos que permitimos dar en los posts." name="c_allow_points" value=$tsConfig.c_allow_points group=true suffix="max" maxlength=3}
			<!-- Si introducimos '0', se permitir&aacute; dar los puntos definidos por el rango del usuario. <br /> <br />  Si introducimos '-1', se podr&aacute;n dar todos los puntos que el usuario tenga para dar hoy. <br /> <br /> Introduciendo un n&uacute;mero superior a 0, todos los usuarios sin importar su rango, tend&aacute;n esa cantidad para dar. -->

			{include "dashboard/FormGroup.tpl" type="radio" id="c_allow_sump" label="Los votos suman puntos" helper="Cada voto positivo en un comentario es un punto m&aacute;s para el usuario. <strong>Nota:</strong> Los votos negativos no restan puntos" name="c_allow_sump" checked=$tsConfig.c_allow_sump labels=["Sí", "No"] values=[1,0]}
			
			<hr class="my-3" />

			{include "dashboard/FormGroup.tpl" type="radio" id="c_newr_type" label="Cambio de rango" helper="Un usuario sube de rango cuando obtiene los puntos m&iacute;nimos en" name="c_newr_type" checked=$tsConfig.c_newr_type labels=["Todos sus post", "Solo en un post"] values=[1,0]}

			{include "dashboard/Button.tpl" submit_text="Guardar Cambios"}
		</fieldset>
	</form>
</div>