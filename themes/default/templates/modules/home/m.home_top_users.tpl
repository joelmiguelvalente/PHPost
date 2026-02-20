<div id="topsUserBox" class="box">
	<div class="box-header">
		<span class="box_txt">TOPs usuarios <a class="size9" href="{$tsConfig.url}/top/usuarios/">(Ver m&aacute;s)</a></span>
		<div class="box_icon">
			<a href="{$tsConfig.url}/rss/top-usuarios-semana"><span class="systemicons sRss"></span></a>
		</div>
	</div>
	<div class="box-filter">
		<span data-box="topsUserBox" id="Ayer">Ayer</span>
		<span data-box="topsUserBox" id="Semana"{if $tsTopPosts.semana} class="active"{/if}>Semana</span>
		<span data-box="topsUserBox" id="Mes">Mes</span>
		<span data-box="topsUserBox" id="Historico"{if !$tsTopPosts.semana} class="active"{/if}>Hist&oacute;rico</span>
	</div>
	<div class="box-content" style="height:calc(24px * 15);">
		<ol id="filterAyer" class="filter-list tops" style="display:none;">
			{foreach from=$tsTopUsers.ayer key=i item=u}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a href="{$tsConfig.url}/@{$u.user_name}">{$u.user_name}</a>
					<span>{$u.total}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops ayer</div>
			{/foreach}
		</ol>
		<ol id="filterSemana" class="filter-list tops" style="display:none;">
			{foreach from=$tsTopUsers.semana key=i item=u}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a href="{$tsConfig.url}/@{$u.user_name}">{$u.user_name}</a>
					<span>{$u.total}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops semana</div>
			{/foreach}
		</ol>
		<ol id="filterMes" class="filter-list tops" style="display:{if $tsTopUsers.mes}block{else}none{/if};">
			{foreach from=$tsTopUsers.mes key=i item=u}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a href="{$tsConfig.url}/@{$u.user_name}">{$u.user_name}</a>
					<span>{$u.total}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops mes</div>
			{/foreach}
		</ol>
		<ol id="filterHistorico" class="filter-list tops" style="display:{if !$tsTopUsers.mes}block{else}none{/if};">
			{foreach from=$tsTopUsers.historico key=i item=u}
				<li>
					{if $i+1 < 10}0{/if}{$i+1}.
					<a href="{$tsConfig.url}/@{$u.user_name}">{$u.user_name}</a>
					<span>{$u.total}</span>
				</li>
			{foreachelse}
				<div class="alert-empty">Sin tops historicos</div>
			{/foreach}
		</ol>
	</div>
</div>