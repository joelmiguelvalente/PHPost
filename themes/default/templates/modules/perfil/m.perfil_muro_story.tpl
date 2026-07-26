{assign var=ajax value=$ajax|default:false}
{foreach from=$tsMuro.data item=p}
	<div class="Story grid gap-3" id="pub_{$p.pub_id}">
		<a href="{$tsConfig.url}/@{$p.user_name}" class="Story_Pic">
			{include "blocks/Avatar.tpl" alt=$p.user_name id=$p.p_user_pub size=50 placeholder=$ajax}
		</a>
		<div class="Story_Content">
			<div class="Story_Head relative">
				{if $p.p_user == $tsUser->uid || $p.p_user_pub == $tsUser->uid || $tsUser->is_admod || $tsUser->permiso('moderacion.muro.eliminar_publicaciones')}
					<div class="Story_Hide">
						<span onclick="muro.del_pub({$p.pub_id},1);" title="Eliminar la publicaci&oacute;n" class="qtip uiClose"></span>
					</div>
				{/if}
				<div class="Story_Message">
					<div class="autor">
						<a href="{$tsConfig.url}/@{$p.user_name}" class="a_blue">
							{if $p.user_name == $tsUser->nick}{$tsUser->nick}{else}{$p.user_name}{/if}
						</a>
					</div>
					<span class="block py-2 px-3">{$p.p_body|raw}</span>
					{if $p.p_type != 1}
						<div class="mvm p-2">
							{if $p.p_type == 2}
								<span role="button" data-action="loadAtta" data-type="foto" data-adjunto="{$p.adj_url}" class="uiPhoto block">
									{include "blocks/Image.tpl" alt="Imagen del perfil de {$p.user_name}" src=$p.adj_image class="rounded ratio 1x1"}
								</span>
							{elseif $p.p_type == 3}
								<a href="{$p.adj_url}" class="uiLink block" title="{$p.adj_title}" rel="external" target="_blank">
									<span class="uiLink-title">{$p.adj_title}</span>
									<span class="uiLink-description block">{$p.adj_url}</span>
								</a>
							{elseif $p.p_type == 4}
								<div class="uiVideo rounded overflow-hidden relative" data-action="loadAtta" data-type="video" data-adjunto="{$p.adj_url}">
									<img src="https://i.ytimg.com/vi/{$p.adj_url}/sddefault.jpg" data-src="https://i.ytimg.com/vi/{$p.adj_url}/maxresdefault.jpg" alt="{$p.adj_title}" class="object-fit-cover ratio ratio-4x3 rounded">
									<div class="video-description absolute">
										<span class="block">{$p.adj_title}</span>
										<p class="block">{$p.adj_description}...</p>
									</div>
								</div>
							{/if}
						</div>
					{/if}
				</div>
			</div>
		<div class="Story_Foot">
			<div class="Story_Info flex justify-start items-center gap-2 relative">
				<i class="stream w_{if $p.p_type == 1 && $p.p_user == $p.p_user_pub}0{else}{$p.p_type}{/if}"></i>
				<span class="text">{$p.p_date|hace:true}</span> &middot; 
				<a onclick="muro.like_this({$p.pub_id}, 'pub', this); return false;" class="a_blue">{$p.likes.link}</a> &middot; 
				<a onclick="muro.show_comment_box({$p.pub_id}); return false" class="a_blue">Comentar</a>
				{if $tsUser->is_admod} &middot;
					<span class="text">{$p.p_ip}</span>
				{/if}
			</div>
			<ul id="cb_{$p.pub_id}" class="Story_Comments" {if $p.p_comments == 0 && $p.p_likes == 0}style="display:none"{/if}>
				<li class="lifi"><i></i></li>
				<li class="ufiItem" {if $p.p_likes == 0}style="display:none"{/if}>
					<div class="likes clearfix">
						<i></i>
						<span class="floatL" id="lk_{$p.pub_id}">{$p.likes.text}</span>
					</div>
				</li>
				<li>
					<ul id="cl_{$p.pub_id}" class="commentList">
						{if $p.p_comments > 2 && !$p.hide_more_cm}
							<li class="ufiItem">
								<div class="more_comments clearfix">
									<i></i>
									<a href="#" class="a_blue floatL" onclick="muro.more_comments({$p.pub_id}, this); return false">Ver los {$p.p_comments} comentarios</a>
									<img width="20" height="20" src="{$tsRoutes['assets:images']}/loader.gif"/>
								</div>
							</li>
						{/if}
						{foreach from=$p.comments item=c}
							<li class="ufiItem" id="cmt_{$c.cid}">
								<div class="clearfix">
									<a href="{$tsConfig.url}/@{$c.user_name}" class="autorPic">
										{include "blocks/Avatar.tpl" id=$c.user_id size=32 alt=$c.user_name lazy=true class="avatar"}
									</a>
									{if $p.p_user == $tsUser->uid || $c.c_user == $tsUser->uid  || $tsUser->is_admod || $tsUser->permiso('moderacion.muro.eliminar_comentarios')}<span class="close"><a href="#" onclick="muro.del_pub({$c.cid}, 2); return false" class="uiClose" title="Eliminar"></a></span>{/if}
									<div class="mensaje">
										<a href="{$tsConfig.url}/@{$c.user_name}" class="autorName a_blue">{$c.user_name}</a>
										<span>{$c.c_body|html_decode}</span>
										<div class="cmInfo">{$c.c_date|fecha} &middot; <a onclick="muro.like_this({$c.cid}, 'com', this); return false;" class="a_blue">{$c.like}</a> <span class="cm_like"{if $c.c_likes == 0} style="display:none"{/if}>&middot; <i></i> <a onclick="muro.show_likes({$c.cid}, 'com'); return false;" id="lk_cm_{$c.cid}" class="a_blue">{$c.c_likes} persona{if $c.c_likes > 1}s{/if}</a></span>{if $tsUser->is_admod} &middot;<span class="cmInfo">{$c.c_ip}</span>{/if}</div>
									</div>
								</div>
							</li>
						{/foreach}
					</ul> 
				</li>
				{if $tsPrivacidad.muro_firma.status == true && $tsUser->is_member || $tsType == 'news'}
					<li class="ufiItem">
						<div class="newComment">
							<input type="text" title="Escribe un comentario...." name="hack" value="Escribe un comentario..." pid="{$p.pub_id}" />
							<div class="formulario" style="display:none">
								{include "blocks/Avatar.tpl" id=$tsUser->uid size=32 alt=$tsUser->nick lazy=true class="avatar"}
								<textarea class="comentar" placeholder="Escribe un comentario..." id="cf_{$p.pub_id}" pid="{$p.pub_id}" name="add_wall_comment"></textarea>
							</div>
						</div>
					</li>
				{/if}
			</ul>
		</div>
	</div>
</div>
{/foreach}
