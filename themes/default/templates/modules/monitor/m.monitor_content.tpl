<div id="centroDerecha">
	<div class="box">
		<div class="box-header">
			<span class="box_txt">&Uacute;ltimas {$tsData.total} notificaciones</span>
		</div>
		<div class="box-content">
			{if $tsData.data}
				<ul class="notification-detail listado-content">
					{foreach from=$tsData.data item=noti}
						<li class="listado-item grid gap-3 p-2 mb-2 rounded{if $noti.unread > 0} unread{/if}">
							<a class="block" href="{$tsConfig.url}/@{$noti.user}" title="{$noti.user}" rel="internal">
								<picture class="profile-avatar">
	  								<img src="{$tsRoutes.assets.images}/phpost/main-64.png" data-src="{$noti.avatar}" alt="{$noti.user}" width="48" height="48" loading="lazy" decoding="async">
	  							</picture>
							</a>
							<div class="notification-info flex justify-center items-start gap-1 flex-col">
								<span class="flex justify-between items-center w-full">
									{if $noti.total == 1}<a title="{$noti.user}" rel="internal" class="user" href="{$tsConfig.url}/@{$noti.user}">{$noti.user}</a>{else}<span></span>{/if} 
									<span title="{$noti.date|fecha}" class="time">{$noti.date|fecha}</span>
								</span>
								<span class="action flex justify-start items-center gap-1">
									<span class="monac_icons ma_{$noti.style}"></span> {$noti.text}
									<a href="{$noti.link}">{$noti.ltext}</a>
								</span>
							</div>
						</li>
					{/foreach}
				</ul>
			{else}
				<div class="alert-empty">No tienes notificaciones</div>
			{/if}
		</div>
	</div>
</div>