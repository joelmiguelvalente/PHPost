<?php

declare(strict_types=1);

/**
 * @package    src\Api
 * @author     PHPost Team & Miguel92
 * @copyright  2026
 */

if (!defined('TS_HEADER')) {
	exit('No se permite el acceso directo al script');
}

const ACTIONS = [
   'github-commit' => ['nivel' => 2, 'template' => '', 'ajax' => false]
];

if (!array_key_exists($action, ACTIONS)) {
   http_response_code(403);
   exit('Acción inválida');
}

$config  = ACTIONS[$action];
$tsLevel = $config['nivel'];
$tsAjax  = (int)$config['ajax'];

$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
   echo json_encode(['state' => 0, 'data' => 'Sin permisos']);
   die();
}

switch ($action) {
   case 'github-commit':
      $branch = rawurlencode(trim($_GET['branch'] ?? 'php-8-migration'));
      $url    = "https://api.github.com/repos/joelmiguelvalente/PHPost/commits/{$branch}";

      $context = stream_context_create([
         'http' => [
            'method' => 'GET',
            'header' => [
               'User-Agent: PHPostApp',
               'Accept: application/vnd.github.v3+json',
            ],
            'timeout' => 5,
         ]
      ]);

      $response = @file_get_contents($url, false, $context);

      if ($response === false) {
         echo json_encode(['state' => 0, 'data' => 'No se pudo conectar con la API de GitHub']);
         die();
      }

      $data = json_decode($response, true);

      if (empty($data) || isset($data['message'])) {
         echo json_encode(['state' => 0, 'data' => 'Rama no encontrada o sin commits']);
         die();
      }

      echo json_encode([
         'state' => 1,
         'data'  => [
            'sha'      => $data['sha'],
            'html_url' => $data['html_url'],
            'author'   => $data['commit']['author']['name'],
            'message'  => $data['commit']['message'],
            'date'     => $data['commit']['author']['date'],
            'verified' => $data['commit']['verification']['verified'],
            'reason'   => $data['commit']['verification']['reason'],
         ]
      ]);
   break;
}
