<div class="subMenu flex justify-between items-center relative p-2 pb-0">
	<ul class="tabsMenu flex justify-start items-center gap-2">
		{if $tsPage == 'fotos'}
			<li class="tabsItem{if $tsAction == '' || $tsAction == 'ver'} active{/if}">
				<a class="block px-4 py-1" href="{$tsConfig.url}/fotos/">Inicio</a>
			</li>
			{if $tsAction == 'album' && $tsFUser.0 != $tsUser->uid}
				<li class="tabsItem active">
					<a class="block px-4 py-1" href="{$tsConfig.url}/fotos/{$tsFUser.1}">&Aacute;lbum de {$tsFUser.1}</a>
				</li>
			{/if}
			{if $tsUser->is_admod || $tsUser->permiso('global.fotos.publicar')}
				<li class="tabsItem{if $tsAction == 'agregar'} active{/if}">
					<a class="block px-4 py-1" href="{$tsConfig.url}/fotos/agregar">Agregar Foto</a>
				</li>
			{/if}
			<li class="tabsItem{if $tsAction == 'album' && $tsFUser.0 == $tsUser->uid} active{/if}">
				<a class="block px-4 py-1" href="{$tsConfig.url}/fotos/{$tsUser->nick}">Mis Fotos</a>
			</li>
		{elseif $tsPage == 'tops'}
			<li class="tabsItem{if $tsAction == 'posts'} active{/if}">
				<a class="block px-4 py-1" href="{$tsConfig.url}/top/posts/">Posts</a>
			</li>
			<li class="tabsItem{if $tsAction == 'usuarios'} active{/if}">
				<a class="block px-4 py-1" href="{$tsConfig.url}/top/usuarios/">Usuarios</a>
			</li>
		{else}
			<li class="tabsItem{if $tsPage == 'home' || $tsPage == 'portal'} active{/if}">
				<a title="Inicio" class="block px-4 py-1" href="{$tsConfig.url}/{if $tsPage == 'home' || $tsPage == 'posts'}posts/{/if}">Inicio</a>
			</li>
			<li class="tabsItem{if $tsPage == 'buscador'} active{/if}">
				<a title="Buscador" class="block px-4 py-1" href="{$tsConfig.url}/buscador/">Buscador</a>
			</li>
			{if $tsUser->is_member}
				{if $tsUser->is_admod || $tsUser->permiso('global.posts.publicar')}
					<li class="tabsItem{if $tsSubmenu == 'agregar'} active{/if}">
						<a title="Agregar Post" class="block px-4 py-1" href="{$tsConfig.url}/agregar/">Agregar Post</a>
					</li>
				{/if}
				<li class="tabsItem{if $tsPage == 'mod-history'} active{/if}">
					<a title="Historial de Moderaci&oacute;n" class="block px-4 py-1" href="{$tsConfig.url}/mod-history/">Historial</a>
				</li>
				{if $tsUser->is_admod || $tsUser->permiso('moderacion.panel.acceso')}
					<li class="tabsItem{if $tsPage == 'moderacion'} active{/if}">
						<a title="Panel de Moderador" class="block px-4 py-1" href="{$tsConfig.url}/moderacion/">
							Moderaci&oacute;n
							{if $tsConfig.c_see_mod && $tsConfig.novemods.total}
								<span class="cadGe cadGe_{if $tsConfig.novemods.total < 10}green{elseif $tsConfig.novemods.total < 30}purple{else}red{/if}">{$tsConfig.novemods.total}</span>
							{/if}
						</a>
					</li>
				{/if}
			{/if}
		{/if}
	</ul>
	{if $tsPage != 'tops'}{include "head_categorias.tpl"}{/if}
</div>
