{include "main_header.tpl"}
{load file=['login'] type="js" cache=true}
	<div class="privado-content">
		<div class="privado-login flex justify-center items-center flex-col">
			<h3>{if $tsType == 'post'}Este post es privado, s&oacute;lo los usuarios registrados de {$tsConfig.titulo} pueden acceder.{else}Registrate en {$tsConfig.titulo}{/if}</h3>
        	{if $tsType == 'post'}<p>Pero no te preocupes, tambi&eacute;n puedes formar parte de nuestra gran familia. <a title="Reg&iacute;strate!" href="{$tsConfig.url}/registro/">Reg&iacute;strate!</a></p>{/if}
			<div class="py-4 text-center display-message p-8" style="display:none;"></div>
			<form method="POST" class="flex flex-col gap-3" id="LoginForm">
				<div class="form-group">
					<label class="form-label" for="username">Usuario o Correo Electrónico</label>
					<input class="form-control" placeholder="JohnDoe / johndoe@example.com" name="username" id="username" type="text"/>
					<span data-label="username" class="form-helper"></span>
				</div>
				<div class="form-group">
					<label class="form-label" for="password">Contraseña</label>
					<input class="form-control" placeholder="••••••••" name="password" id="password" type="password"/>
					<span data-label="password" class="form-helper"></span>
				</div>
				<input name="remember" id="remember" type="hidden" value="true" />
				<!-- Login Button -->
				<button class="btn btn-primary" id="btn-login">Ingresar</button>
			</form>
		</div>
		<div class="privado-atencion flex justify-center items-center flex-col">
			<img src="{$tsRoutes.tema.images}/private-post.gif" alt="Post privado">
			<div class="privado-atencion-card">
				<h4 class="block">&iexcl;Atenci&oacute;n!</h4>
				<p>Antes de ingresar tus datos asegurate que la URL de esta p&aacute;gina pertenece a <strong>{$tsConfig.titulo}</strong></p>
			</div>
		</div>
	</div>
{include "main_footer.tpl"}