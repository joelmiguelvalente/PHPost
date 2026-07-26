<?php

/**
 * @package    Extras
 * @author     Miguel92
 * @copyright  2026
 */

/**
 * Denuncias
*/
$Denuncias = [
  'users' => [
      '',
      'Perfil falso/clon',
      'Usuario insultante y agresivo',
      'Publicaciones inapropiadas',
      'Foto del perfil inapropiada',
      'Publicidad no deseada (SPAM)',
   ],
   'posts' => [
      '',
        'Re-post',
        'Se hace Spam',
        'Tiene links muertos',
        'Es racista o irrespetuoso',
        'Contiene información personal',
        'El título esta en mayúscula',
        'Contiene pedofilia',
        'Es gore o asqueroso',
        'Está mal la fuente',
        'Post demasiado pobre / Crap',
        $tsCore->settings['titulo'].' no es un foro',
        'No cumple con el protocolo',
        'Otra razón (especificar)'
   ],
    'fotos' => [
      '',
        'Ya está publicada',
        'Se hace Spam',
        'La imagen está caída',
        'Es racista o irrespetuosa',
        'Contiene información personal',
        'Contiene pedofilia',
        'Es gore o asquerosa',
        'Otra razón (especificar)'
   ]
];
