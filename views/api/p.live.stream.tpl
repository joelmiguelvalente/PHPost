<div id="live-stream" ntotal="{if !$tsStream.total}0{else}{$tsStream.total}{/if}" mtotal="{$tsMensajes.total}">
	{foreach from=$tsStream.data item=noti key=id}
		<div class="UIBeeper_Full" id="beep_{$id}" role="status" aria-atomic="true">
			<div class="Beeps">
				<div class="UIBeep beep-{$noti.style}">
					<div class="UIBeep_Avatar">
						<img src="{$noti.avatar}" width="36" height="36" alt="Avatar de {$noti.user}" />
						<div class="UIBeep_Icon_Badge">
							<span class="monac_icons ma_{$noti.style}" style="margin-right:auto" aria-hidden="true"></span>
						</div>
					</div>
					<div class="UIBeep_Body">
						<div class="UIBeep_Title">
							<span class="uname">{if $noti.total == 1}{$noti.user}{/if}</span> {$noti.text} <span class="uname">{$noti.ltext}</span>
						</div>
						<div class="UIBeep_Meta">ahora</div>
					</div>
					<button class="beeper_x" bid="{$id}" aria-label="Cerrar notificación">✕</button>
				</div>
			</div>
		</div>
	{/foreach}
	{foreach from=$tsMensajes.data item=mp key=id}
	<div class="UIBeeper_Full" id="beep_m{$id}" role="status" aria-atomic="true">
		<div class="Beeps">
			<div class="UIBeep beep-msg">
				<div class="UIBeep_Avatar">
					<img src="{$mp.avatar}" width="36" height="36" alt="Avatar de {$mp.user_name}" />
					<div class="UIBeep_Icon_Badge">
						<span class="monac_icons mps" style="margin-right:auto" aria-hidden="true"></span>
					</div>
				</div>
				<div class="UIBeep_Body">
					<div class="UIBeep_Title">
						<span class="uname" style="color: #db2777">{$mp.user_name}</span> te envió un mensaje
					</div>
					<div class="UIBeep_Preview">{$mp.mp_preview}</div>
					<div class="UIBeep_Meta">ahora</div>
				</div>
				<button class="beeper_x" bid="m{$id}" aria-label="Cerrar notificación">✕</button>
			</div>
		</div>
	</div>
	{/foreach}
</div>