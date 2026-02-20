<div class="publish-wall">
	<div class="publish-options flex justify-start items-center gap-2">
		<div class="option">Compartir</div>
		<div class="option flex justify-center items-center gap-1 btnAction" data-action="load" data-argument="status">
			<i aria-hidden="true" class="stream {if $tsInfo.uid == $tsUser->uid}status{else}mpub{/if}"></i>
			<span role="button" id="stMain">{if $tsInfo.uid == $tsUser->uid}Estado{else}Publicaci&oacute;n{/if}</span>
		</div>
		<div class="option flex justify-center items-center gap-1 btnAction" data-action="load" data-argument="foto">
			<i aria-hidden="true" class="stream mfoto"></i>
			<span role="button">Foto</span>
		</div>
		<div class="option flex justify-center items-center gap-1 btnAction" data-action="load" data-argument="enlace">
			<i aria-hidden="true" class="stream mlink"></i>
			<span role="button">Enlace</span>
		</div>
		<div class="option flex justify-center items-center gap-1 btnAction" data-action="load" data-argument="video">
			<i aria-hidden="true" class="stream mvideo"></i>
			<span role="button">Video</span>
		</div>
	</div>
	<div class="publish-box p-2 rounded my-2">
		<div class="option-publish my-2 rounded none justify-start items-center"></div>
		<div id="result"></div>
		<textarea class="w-full form-control" id="publishText" placeholder="{if $tsInfo.uid == $tsUser->uid}&iquest;Qu&eacute; est&aacute;s pensando?{else}Escribe algo....{/if}"></textarea>
		<input type="button" data-action="compartir" class="mBtn btnOk btnAction" value="Compartir" />
	</div>
</div>