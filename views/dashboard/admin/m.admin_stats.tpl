<h1 class="text-xl font-semibold mb-4">Estadísticas</h1>
<div class="rounded border bg-white dark:bg-surface p-4 shadow-sm">
	{include "dashboard/Alert.tpl" text="Tus cambios han sido guardados." color="green" show=$tsSave}

	<div class="columns-1 sm:columns-2 xl:columns-4 [column-gap:1.5rem] space-y-5">
		{include "dashboard/CardSection.tpl" header="Posts" total=$tsAdminStats.posts.total list=[
		   ['title' => 'Visibles',  'total' => $tsAdminStats.posts.visibles],
		   ['title' => 'Ocultos',  'total' => $tsAdminStats.posts.ocultos],
		   ['title' => 'Eliminados',  'total' => $tsAdminStats.posts.eliminados],
		   ['title' => 'Compartidos',  'total' => $tsAdminStats.posts.compartidos],
		   ['title' => 'Favoritos',  'total' => $tsAdminStats.posts.favoritos],
		]}

		{include "dashboard/CardSection.tpl" header="Fotos" total=$tsAdminStats.fotos.total list=[
		   ['title' => 'Visibles',  'total' => $tsAdminStats.fotos.visibles],
		   ['title' => 'Ocultas',  'total' => $tsAdminStats.fotos.ocultas],
		   ['title' => 'Eliminadas',  'total' => $tsAdminStats.fotos.eliminadas],
		   ['title' => 'Comentarios',  'total' => $tsAdminStats.fotos.comentarios]
		]}

		{include "dashboard/CardSection.tpl" header="Comentarios en Posts" total=$tsAdminStats.comentarios.total list=[
		   ['title' => 'Visibles',  'total' => $tsAdminStats.comentarios.visibles],
		   ['title' => 'Ocultos',  'total' => $tsAdminStats.comentarios.ocultos]
		]}

		{include "dashboard/CardSection.tpl" header="Usuarios" total=$tsAdminStats.usuarios.total list=[
		   ['title' => 'Activos',  'total' => $tsAdminStats.usuarios.activos],
		   ['title' => 'Inactivos',  'total' => $tsAdminStats.usuarios.inactivos],
		   ['title' => 'Baneados/Suspendidos',  'total' => $tsAdminStats.usuarios.baneados],
		]}

		{include "dashboard/CardSection.tpl" header="Muro" total=$tsAdminStats.muro.total list=[
		   ['title' => 'Estados',  'total' => $tsAdminStats.muro.estados],
		   ['title' => 'comentarios',  'total' => $tsAdminStats.muro.comentarios]
		]}

		{include "dashboard/CardSection.tpl" header="Afiliados" total=$tsAdminStats.afiliados.total list=[
		   ['title' => 'Activos',  'total' => $tsAdminStats.afiliados.activos],
		   ['title' => 'Inactivos',  'total' => $tsAdminStats.afiliados.inactivos]
		]}

		{include "dashboard/CardSection.tpl" header="Medallas" total=$tsAdminStats.medallas.total list=[
		   ['title' => 'Posts',  'total' => $tsAdminStats.medallas.posts],
		   ['title' => 'Fotos',  'total' => $tsAdminStats.medallas.fotos],
		   ['title' => 'Usuarios',  'total' => $tsAdminStats.medallas.usuarios],
		   ['title' => 'Asignadas',  'total' => $tsAdminStats.medallas.asignadas]
		]}

		{include "dashboard/CardSection.tpl" header="Seguimiento" total=$tsAdminStats.seguimientos.total list=[
		   ['title' => 'Posts',  'total' => $tsAdminStats.seguimientos.posts],
		   ['title' => 'Usuarios',  'total' => $tsAdminStats.seguimientos.usuarios]
		]}

		{include "dashboard/CardSection.tpl" header="Mensajes" total=$tsAdminStats.mensajes.total list=[
		   ['title' => 'Eliminados por receptor',  'total' => $tsAdminStats.mensajes.para_eliminados],
		   ['title' => 'Eliminados por autor',  'total' => $tsAdminStats.mensajes.de_eliminados],
		   ['title' => 'Respuestas',  'total' => $tsAdminStats.mensajes.respuestas]
		]}
	</div>
</div>
