<?php

/**
 * @package    Extras
 * @author     Miguel92
 * @copyright  2026
 */

/**
 * Privacidad
*/
$tsPrivacidad = [
   'nobody'        => 'Nadie',
   'friends_mutual'=> 'Usuarios que sigo y me siguen',
   'friends_any'   => 'Usuarios que sigo o me siguen',
   'followers'     => 'Mis seguidores',
   'following'     => 'Usuarios que sigo',
   'registered'    => 'Usuarios registrados',
   'everyone'      => 'Todos'
];

# Eliminar contenido (Admistrar usuario)
$tsContenido = [
   [
      'for' => 'posts', 
      'title' => 'Posts', 
      'desc' => 'Se eliminarán todos sus posts y sus comentarios.'
   ], [
      'for' => 'fotos', 
      'title' => 'Fotos', 
      'desc' => 'Se eliminarán todas sus fotos publicadas y sus comentarios.'
   ], [
      'for' => 'estados', 
      'title' => 'Estados', 
      'desc' => 'Se eliminarán todas sus publicaciones de muros'
   ], [
      'for' => 'composts', 
      'title' => 'Comentarios de Posts', 
      'desc' => 'Se eliminarán todos sus comentarios en posts.'
   ], [
      'for' => 'comfotos', 
      'title' => 'Comentarios de Fotos',
      'desc' => 'Se eliminarán todos sus comentarios en fotos.'
   ], [
      'for' => 'comestados', 
      'title' => 'Comentarios en Estados',
      'desc' => 'Se eliminarán todos sus comentarios en estados'
   ], [
      'for' => 'like', 
      'title' => 'Like',
      'desc' => 'Se eliminarán sus likes en estados y comentarios en estados'
   ], [
      'for' => 'seguidores', 
      'title' => 'Seguidores',
      'desc' => 'Se eliminará la lista de todos sus seguidores.'
   ], [
      'for' => 'siguiendo', 
      'title' => 'Siguiendo',
      'desc' => 'Se eliminará la lista de todos a los que sigue.'
   ], [
      'for' => 'favoritos', 
      'title' => 'Favoritos',
      'desc' => 'Se eliminará la lista de favoritos que haya agregado.'
   ], [
      'for' => 'votosposts', 
      'title' => 'Votos en Posts',
      'desc' => 'Se eliminarán los votos de puntos que haya dejado en posts.'
   ], [
      'for' => 'votosfotos', 
      'title' => 'Votos en Fotos',
      'desc' => 'Se eliminarán los votos positivos y negativos que haya dejado en fotos.'
   ], [
      'for' => 'actividad', 
      'title' => 'Actividad',
      'desc' => 'Se eliminará toda su actividad.'
   ], [
      'for' => 'avisos', 
      'title' => 'Avisos',
      'desc' => 'Se eliminarán todos los avisos que ha recibido.'
   ], [
      'for' => 'bloqueos', 
      'title' => 'Bloqueos',
      'desc' => 'Se eliminarán todos los bloqueos que ha recibido.'
   ], [
      'for' => 'mensajes', 
      'title' => 'Mensajes Privados',
      'desc' => 'Se eliminarán todos los mensajes que ha enviado y recibido.'
   ], [
      'for' => 'sesiones', 
      'title' => 'Sesiones',
      'desc' => 'Se eliminarán todas las sesiones.'
   ], [
      'for' => 'visitas', 
      'title' => 'Visitas',
      'desc' => 'Se eliminarán todo rastro de visitas de este usuario en perfiles, posts y fotos.'
   ]
];
