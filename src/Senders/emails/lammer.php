<?php

$email['asunto'] = '⚠️ ALERTA: Intento de instalación no autorizada detectado';
$email['contenido'] = '
<html>
<head>
<style>
@media only screen and (max-width:600px) {
  .email-container { width:100% !important; max-width:600px !important; }
  .email-content { padding:20px !important; }
  .email-header img { max-height:32px !important; }
  .email-header td { display:block !important; text-align:center !important; padding:4px 0 !important; }
}
</style>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
<p>Hola,</p>
<p>Se ha detectado un intento de instalación no autorizada de tu script en un servidor.</p>
<h3 style="color: #cc0000;">🔴 DETALLES DEL INTENTO:</h3>
<ul>
   <li><strong>URL del sitio:</strong> ' . $url . '</li>
   <li><strong>Script:</strong> PHPost v3 (2026)</li>
   <li><strong>Username intentado:</strong> ' . $user['user_name'] . '</li>
   <li><strong>Password intentado:</strong> ' . $user['user_password'] . '</li>
   <li><strong>Dirección IP:</strong> ' . $_SERVER['REMOTE_ADDR'] . '</li>
   <li><strong>Email proporcionado:</strong> ' . $user['user_email'] . '</li>
</ul>
<h3 style="color: #e67e00;">⚠️ ACCIÓN REQUERIDA:</h3>
<p>Este intento de instalación no cuenta con el consentimiento del propietario del hosting. Se recomienda:</p>
<ol>
   <li>Verificar si esta instalación fue autorizada</li>
   <li>Contactar al propietario del dominio si es necesario</li>
   <li>Bloquear la IP si se confirma actividad maliciosa</li>
   <li>Revisar los logs de seguridad</li>
</ol>
<hr>
<p style="font-size: 0.85em; color: #888;">Este es un mensaje automático de seguridad. No responder a este email.<br>Sistema de Seguridad ' . Config::app('app.name') . ' ' . Config::app('app.version') . '</p>
</body>
</html>';

return $email;