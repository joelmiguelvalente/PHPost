<div id="live-stream" ntotal="{if !$tsStream.total}0{else}{$tsStream.total}{/if}" mtotal="{$tsMensajes.total}">
	{foreach from=$tsStream.data item=noti key=id}
		<div class="UIBeeper_Full" id="beep_{$id}">
			<div class="Beeps">
				<div class="UIBeep beep-{$noti.style}">
					<div class="UIBeep_Avatar">
						<img src="{$noti.avatar}" width="36" height="36" />
						<div class="UIBeep_Icon_Badge">
							<span class="monac_icons ma_{$noti.style}" style="margin-right:auto"></span>
						</div>
					</div>
					<div class="UIBeep_Body">
						<div class="UIBeep_Title">
							<span class="uname">{if $noti.total == 1}{$noti.user}{/if}</span> {$noti.text} <span class="uname">{$noti.ltext}</span>
						</div>
						<div class="UIBeep_Meta">ahora</div>
					</div>
					<span class="beeper_x" bid="{$id}">✕</span>
				</div>
			</div>
		</div>
	{/foreach}
	{foreach from=$tsMensajes.data item=mp key=id}
	<div class="UIBeeper_Full" id="beep_m{$id}">
		<div class="Beeps">
			<div class="UIBeep beep-msg">
				<div class="UIBeep_Avatar">
					<img src="{$mp.avatar}" width="36" height="36" />
					<div class="UIBeep_Icon_Badge">
						<span class="monac_icons mps" style="margin-right:auto"></span>
					</div>
				</div>
				<div class="UIBeep_Body">
					<div class="UIBeep_Title">
						<span class="uname" style="color: #db2777">{$mp.user_name}</span> te envió un mensaje
					</div>
					<div class="UIBeep_Preview">{$mp.mp_preview}</div>
					<div class="UIBeep_Meta">ahora</div>
				</div>
				<span class="beeper_x" bid="m{$id}">✕</span>
			</div>
		</div>
	</div>
	{/foreach}
</div>