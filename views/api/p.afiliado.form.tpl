<div id="AFStatus">
	{include "dashboard/Alert.tpl" text="Ingresa los datos de tu web para afiliarte." color="orange" show=true}
</div>
<div id="AFormInputs">
	{include "dashboard/FormGroup.tpl" id="a_titulo" label="T&iacute;tulo" name="a_titulo"}
	{include "dashboard/FormGroup.tpl" type="url" id="a_url" label="Direcci&oacute;n" name="a_url" placeholder="https:// o http://"}
	{include "dashboard/FormGroup.tpl" type="url" id="a_banner" label="Banner" name="a_banner" placeholder="https:// o http://" helper="(216x42px)"}
	{include "dashboard/FormGroupTextarea.tpl" id="a_descripcion" label="Descripci&oacute;n" name="a_descripcion" rows="5"}
	{include "dashboard/FormGroup.tpl" id="a_sid" label="RefID" name="a_sid"}
</div>