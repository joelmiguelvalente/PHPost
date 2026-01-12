
function remind_password(gew){
   close_login_box();

	 if(!gew){
	 
	var form = '';
        form += '<div style="padding:0 35px;" id="AFormInputs">'
        form += '<div class="form-line">'
        form += '<label for="r_email">Correo electr&oacute;nico:</label>'
        form += '<input type="text" tabindex="1" name="r_email" id="r_email" maxlength="35"/>'
  		form += '</div>'
		form += '</div>'
        //
        mydialog.class_aux = 'registro';
		mydialog.show(true);
		mydialog.title('Recuperar Contrase&ntilde;a');
		mydialog.body(form);
		mydialog.buttons(true, true, 'Continuar', 'javascript:remind_password(true)', true, true, true, 'Cancelar', 'close', true, false);		
		mydialog.center();
	
	 }else{
	 
	var r_email = $('#r_email').val(); 
	
	$.post(route.url + '/recover-pass.php', 'r_email=' + r_email, function(a){
		   
           mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
		   
           mydialog.center();
		    
		  });
	}
	
}

function resend_validation(gew){
    close_login_box();

	 if(!gew){
	 
	var form = '';
        form += '<div style="padding:0 35px;" id="AFormInputs">'
        form += '<div class="form-line">'
        form += '<label for="r_email">Correo electr&oacute;nico:</label>'
        form += '<input type="text" tabindex="1" name="r_email" id="r_email" maxlength="35"/>'
  		form += '</div>'
		form += '</div>'
        //
        mydialog.class_aux = 'registro';
		mydialog.show(true);
		mydialog.title('Reenviar validaci&oacute;n');
		mydialog.body(form);
		mydialog.buttons(true, true, 'Reenviar', 'javascript:resend_validation(true)', true, true, true, 'Cancelar', 'close', true, false);		
		mydialog.center();
	
	 }else{
	 
	var r_email = $('#r_email').val(); 
    
    $('#loading').fadeIn(250); 
	
	$.post(route.url + '/recover-validation.php', 'r_email=' + r_email, function(a){
		   
           mydialog.alert((a.charAt(0) == '0' ? 'Opps!' : 'Hecho'), a.substring(3), false);
		   
           mydialog.center();
		    
            $('#loading').fadeOut(350); 
		  });
	}
	
}