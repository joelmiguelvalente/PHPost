<div style="--image:100px;display:grid;grid-template-columns:var(--image) 1fr;gap:1rem;padding:1rem 0">
	<a href="{$tsConfig.url}/afiliado-url.php?ref={$tsAfiliado.aid}" onclick="dialog.close()" target="_blank" style="display:block;width:var(--image);height:var(--image);overflow:hidden;border-radius:.325rem">
		<img src="{$tsAfiliado.a_banner}" alt="{$tsAfiliado.a_titulo}" style="width: 100%;height: 100%;object-fit: cover;" />
	</a>
	<div style="display: flex;justify-content:center;align-items:flex-start;flex-direction: column;">
		<small><a href="{$tsConfig.url}/afiliado-url.php?ref={$tsAfiliado.aid}" onclick="dialog.close()" target="_blank">{$tsAfiliado.a_url}</a></small>
		<h2 style="font-size: 2rem;font-weight: 600;">{$tsAfiliado.a_titulo}</h2>
		<p style="display: block;margin-top: 0.325rem;">{$tsAfiliado.a_descripcion}</p>
	</div>	
</div>