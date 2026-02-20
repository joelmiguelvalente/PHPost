<h1 class="text-xl font-semibold mb-4">Centro de Moderaci&oacute;n</h1>

<section class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-surface p-5 shadow-sm ">
	<h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Hola(a), {$tsUser->nick} 👋</h1>
	<p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Si est&aacute;s viendo esto significa que tienes el privilegio de ser moderador en <strong>{$tsConfig.titulo}</strong>, por favor lee el protocolo de moderadores para mantener todo en orden.</p>
</section>

<section class="rounded-lg border bg-white dark:bg-surface p-5 shadow-sm">
	<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
		<div class="lg:col-span-2">
			<h3 class="px-2 text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Protocolo :: Un moderador:</h3>
			<ul class="list-disc pl-5 space-y-2">
				<li>Debe leer y llevar al pie de la letra cada punto que se describe a continuaci&oacute;n, su desconocimiento de este protocolo no ser&aacute; excusa para no cumplir con el mismo.</li>
				<li>No pasar&aacute; por arriba de las decisiones de los Administradores.</li>
				<li>No deber&aacute; abusar jam&aacute;s de su poder y deber&aacute; ser imparcial y criterioso a la hora de los conflictos.</li>
				<li>Todos los moderadores deben estar atentos a cualquier consulta realizada por los miembros.</li>
				<li>Podr&aacute; editar o eliminar posts que tengan t&iacute;tulos poco descriptivos.</li>
				<li>Borrar&aacute; todo post que haya sido denunciado o que detecte que no cumple con las reglas de <b>{$tsConfig.titulo}</b>.</li>
				<li>Deber&aacute; indicar detalladamente la causa por la cual se elimina un post y las faltas cometidas por el usuario para que este pueda rehacer su post correctamente.</li>
				<li>Si quiere o tiene ganas, puede modificar un post que errores de cualquier &iacute;ndole relacionada con la ortograf&iacute;a o dise&ntilde;o del post, pero en ning&uacute;n momento esto es algo obligatorio.</li>
				<li>En lo posible intentar&aacute; que el usuario edite su post para quedar acorde a las reglas, evitar a toda costa el borrado en masa de posts.</li>
				<li>No se encarga de arreglar cada uno de los posts mal hechos o que rompan reglas. Cada usuario debe encargarse de que su post est&eacute; realizado correctamente ya que tiene la posibilidad y responsabilidad de hacerlo.</li>
				<li>Eliminar&aacute; autom&aacute;ticamente todo tipo de spam intencional.</li>
				<li>Suspender&aacute; directamente en caso de discriminaci&oacute;n, spam masivo, suplantaci&oacute;n de identidad y/o difamaci&oacute;n hacia terceros.</li>
				<li>Decidir&aacute; seg&uacute;n su criterio (en caso de suspensi&oacute;n) la cantidad de d&iacute;as asignados teniendo en cuenta la gravedad de la falta.</li>
				<li>Deber&aacute; informar al administrador toda acci&oacute;n que influya en la v&iacute;a normal de la comunidad. Ej.: suspensiones,  stickys y cualquier otro motivo importante.</li>
				<li>De no cumplirse cualquiera de estos puntos el moderador puede sufrir una pena que va desde tres d&iacute;as de suspensi&oacute;n hasta la p&eacute;rdida del cargo y/o expulsi&oacute;n definitiva de <b>{$tsConfig.titulo}</b>.</li>
			</ul>
		</div>
		{if $tsMods}
			<div class="lg:col-span-1">
				<h4 class="px-2 text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Tus colegas moderadores:</h4>
				{foreach from=$tsMods item=admin}
					<div class="flex justify-start items-center gap-2 mb-3">
						<div class="avatar">
							{include "blocks/Avatar.tpl" id=$admin.user_id size=64 alt="Ver perfil de {$admin.user_name}" lazy=true class="avatar rounded overflow"}
						</div>
						<div class="data">
							<a href="{$tsConfig.url}/@{$admin.user_name}" class="font-bold" title="Perfil del usuario">{$admin.user_name}</a>
							<a href="mailto:{$admin.user_email}" class="block" title="Email del usuario">{$admin.user_email}</a>
						</div>
					</div>
				{/foreach}
			</div>
		{/if}
	</div>
</section>