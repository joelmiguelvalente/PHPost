{include "main_header.tpl"}
	<div class="grid gap-3 grid-tops">
		{include "m.top_sidebar.tpl"}
		<div class="grid gap-3 subgrid-tops">
			{if $tsAction == 'posts'}
				{foreach $tsBoxes key=i item=box}
					{include "m.top_box.tpl" 
						header="Top post con m&aacute;s {$box.txt}" 
						icon="icon-noti {$box.icon}-n" 
						data=$tsTops.$i 
						type="posts" 
						count=$box.count
					}
				{/foreach}
			{elseif $tsAction == 'usuarios'}
				{foreach $tsBoxes key=i item=box}
					{include "m.top_box.tpl" 
						header="Top usuario con m&aacute;s {$box.txt}" 
						icon="icon-noti {$box.icon}-n" 
						data=$tsTops.$i 
						type="users"
					}
				{/foreach}
			{/if}
		</div>
	</div>
{include "main_footer.tpl"}