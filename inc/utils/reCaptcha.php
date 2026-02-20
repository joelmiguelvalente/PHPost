<?php

/**
 * @name reCaptcha.php
 * @author PHPost Team
 * @copyright 2026
 */

declare(strict_types=1);

if (!defined('TS_HEADER')) {
   exit('No se permite el acceso directo al script');
}

class reCaptcha {

   protected tsCore $Core;

   public $RECAPTCHA_TOKEN;

   private string $SERVICE_TYPE;
   private string $USER_IP;

   private ?string $API_SECRET_KEY = null;
   private ?array $GOOGLE_CREDENTIALS = null;
   private ?string $PROJECT_ID = null;
   private ?string $SITE_KEY = null;

   public function __construct(tsCore $Core) {
      $this->Core = $Core;
      // Obtener las claves públicas y secretas desde la configuración
      $this->USER_IP = (new IP)->getIP();

      $this->getProvider(); 
   }

   private function getProvider() {
      $this->SERVICE_TYPE = $this->Core->reCaptchaConfig('captcha_provider');
      switch ($this->SERVICE_TYPE) {
         case 'recaptcha':
         case 'hcaptcha':
            $this->API_SECRET_KEY = $this->Core->reCaptchaConfig('secret_key');
         break;
         case 'recaptcha_enterprise':
            $this->PROJECT_ID = $this->Core->reCaptchaConfig('g_project_id');
            $this->SITE_KEY = $this->Core->reCaptchaConfig('publick_key');
            $json = file_get_contents(
               $this->Core->reCaptchaConfig('g_credentials_json')
            );
            $this->GOOGLE_CREDENTIALS = json_decode($json, true);
         break;
      }
   }

   /*
    ------------------------------------------
    LEGACY VERIFY (reCAPTCHA + hCaptcha)
    ------------------------------------------
   */
   private function verifyLegacy(): bool {
      $url = $this->SERVICE_TYPE === 'hcaptcha' ? 'https://hcaptcha.com/siteverify' : 'https://www.google.com/recaptcha/api/siteverify';
      $post = http_build_query([
         'secret'   => $this->API_SECRET_KEY,
         'response' => $this->RECAPTCHA_TOKEN,
         'remoteip' => $this->USER_IP
      ]);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
         CURLOPT_POST => true,
         CURLOPT_POSTFIELDS => $post,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_TIMEOUT => 10
      ]);

      $response = json_decode(curl_exec($ch), true);
      curl_close($ch);

      return $response['success'] ?? false;
   }

   /*
    ------------------------------------------
    ENTERPRISE TOKEN
    ------------------------------------------
   */
   private function getAccessToken(): string {
      $cred = $this->GOOGLE_CREDENTIALS;
      $header = ['alg'=>'RS256','typ'=>'JWT'];
      $now = time();
      $payload = [
         'iss' => $cred['client_email'],
         'scope' => 'https://www.googleapis.com/auth/cloud-platform',
         'aud' => $cred['token_uri'],
         'iat' => $now,
         'exp' => $now + 3600
      ];
      $base64 = fn($d) => rtrim(strtr(base64_encode(json_encode($d)), '+/', '-_'), '=');
      $jwtUnsigned = $base64($header) . '.' . $base64($payload);

      openssl_sign($jwtUnsigned, $signature, $cred['private_key'], 'SHA256');
      $jwt = $jwtUnsigned . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
      $ch = curl_init($cred['token_uri']);

      curl_setopt_array($ch, [
         CURLOPT_POST => true,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
         ])
      ]);

      $res = json_decode(curl_exec($ch), true);
      curl_close($ch);

      return $res['access_token'] ?? '';
   }

   /*
    ------------------------------------------
    ENTERPRISE VERIFY
    ------------------------------------------
   */
   private function verifyEnterprise(): bool {
      $accessToken = $this->getAccessToken();
      if (!$accessToken) return false;
      $url = "https://recaptchaenterprise.googleapis.com/v1/projects/{$this->PROJECT_ID}/assessments";
      $payload = [
         'event' => [
            'token' => $this->RECAPTCHA_TOKEN,
            'siteKey' => $this->SITE_KEY,
            'userIpAddress' => $this->USER_IP
         ]
      ];
      $ch = curl_init($url);
      curl_setopt_array($ch, [
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_POST => true,
         CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
         ],
         CURLOPT_POSTFIELDS => json_encode($payload)
      ]);
      $response = json_decode(curl_exec($ch), true);
      curl_close($ch);
      $score = $response['riskAnalysis']['score'] ?? 0;
      return $score >= 0.5;
   }

   /*
    ------------------------------------------
    PUBLIC VERIFY
    ------------------------------------------
   */
   public function verify_human(): bool|string {
      if (empty($this->RECAPTCHA_TOKEN)) {
         return 'No hemos podido validar tu humanidad';
      }
      return match($this->SERVICE_TYPE) {
         'recaptcha', 'hcaptcha' => $this->verifyLegacy(),
         'recaptcha_enterprise' => $this->verifyEnterprise(),
         default => false
      };
   }
}