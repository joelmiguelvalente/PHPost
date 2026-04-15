{if $tsPost.medallas}
	<div class="box">
		<div class="box-header">
			<span class="box_txt">Medallas</span>
		</div>
		<div class="box-content">
			<div style="display: flex;justify-content: flex-start;align-items: flex-start;gap:.5rem;flex-wrap: wrap;">
				{foreach from=$tsPost.medallas item=m}
					<img src="{$tsRoutes.assets.images}/icons/medals/{$m.m_image}_32.png" title="{$m.m_title} - {$m.m_description}"/>
				{/foreach}
			</div>
		</div>
	</div>
{/if}
