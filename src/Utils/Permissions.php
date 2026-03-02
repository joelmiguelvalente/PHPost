<?php

final class Permissions {
	
	public const TREE = [
		'admin' => [
         'superadministrador' => ['code' => 'suad', 'type' => 'bool'],
         'supermoderador'     => ['code' => 'sumo', 'type' => 'bool'],
      ],
      'moderacion' => [
      	'panel' => [
            'acceso' => ['code' => 'moacp', 'type' => 'bool'],
         ],
			'denuncias' => [
            'cancelar' => [
               'fotos'    => ['code' => 'mocdf', 'type' => 'bool'],
               'mensajes' => ['code' => 'mocdm', 'type' => 'bool'],
               'posts'    => ['code' => 'mocdp', 'type' => 'bool'],
               'usuarios' => ['code' => 'mocdu', 'type' => 'bool'],
            ],
            'aceptar_mensajes' => ['code' => 'moadm', 'type' => 'bool'],
         ],
         'usuarios' => [
            'buscador'          => ['code' => 'moub', 	'type' => 'bool'],
            'desbanear'         => ['code' => 'modu', 	'type' => 'bool'],
            'suspender'         => ['code' => 'mosu', 	'type' => 'bool'],
            'ver_baneados'      => ['code' => 'movub', 	'type' => 'bool'],
            'ver_desactivados'  => ['code' => 'movcud', 	'type' => 'bool'],
            'ver_suspendidos'   => ['code' => 'movcus', 	'type' => 'bool'],
         ],
         'comentarios' => [
         	'desaprobados' => ['code' => 'mocc', 'type' => 'bool']
         ],
         'posts' => [
            'abrir_cerrar'         => ['code' => 'moayca', 	 'type' => 'bool'],
            'comentarios_cerrado'  => ['code' => 'mocepc', 	 'type' => 'bool'],
            'desaprobados'         => ['code' => 'mocp', 	 'type' => 'bool'],
            'editar'               => ['code' => 'moedpo', 	 'type' => 'bool'],
            'editar_comentarios'   => ['code' => 'moedcopo', 'type' => 'bool'],
            'eliminar'             => ['code' => 'moep', 	 'type' => 'bool'],
            'eliminar_comentarios' => ['code' => 'moecp', 	 'type' => 'bool'],
            'fijar'                => ['code' => 'most', 	 'type' => 'bool'],
            'ocultar'              => ['code' => 'moop', 	 'type' => 'bool'],
            'papelera'             => ['code' => 'morp', 	 'type' => 'bool'],
            'revision'          	  => ['code' => 'moaydcp',  'type' => 'bool'],
         ],
         'fotos' => [
            'editar'                => ['code' => 'moedfo', 'type' => 'bool'],
            'eliminar'              => ['code' => 'moef', 	'type' => 'bool'],
            'eliminar_comentarios'  => ['code' => 'moecf', 	'type' => 'bool'],
            'papelera'              => ['code' => 'morf', 	'type' => 'bool'],
         ],
         'muros' => [
            'eliminar_comentarios'   => ['code' => 'moecm', 'type' => 'bool'],
            'eliminar_publicaciones' => ['code' => 'moepm', 'type' => 'bool'],
         ],
      ],
      'global' => [
         'posts' => [
            'comentar'		  => ['code' => 'gopcp',  'type' => 'bool'],
            'publicar'		  => ['code' => 'gopp',   'type' => 'bool'],
            'puntuar'		  => ['code' => 'godp',   'type' => 'bool'],
            'revisar'		  => ['code' => 'gorpap', 'type' => 'bool'],
            'votar_negativo' => ['code' => 'govpn',  'type' => 'bool'],
            'votar_positivo' => ['code' => 'govpp',  'type' => 'bool'],
         ],
         'comentarios' => [
            'editar_propios'	 => ['code' => 'goepc', 'type' => 'bool'],
            'eliminar_propios' => ['code' => 'godpc', 'type' => 'bool'],
         ],
         'fotos' => [
            'publicar'	=> ['code' => 'gopf',  'type' => 'bool'],
            'comentar'	=> ['code' => 'gopcf', 'type' => 'bool'],
         ],
         'sistema' => [
            'modo_mantenimiento' => ['code' => 'govwm', 'type' => 'bool'],
         ],
      ],
      'limites' => [
         'antiflood'        => ['code' => 'goaf',  'type' => 'int'],
         'puntos_por_post'  => ['code' => 'gopfp', 'type' => 'int'],
         'puntos_por_dia'   => ['code' => 'gopfd', 'type' => 'int'],
      ],
   ];

	public const DEFINITIONS = [
		// Admin / mod
		'suad' => 1, // superadministrador
		'sumo' => 2, // supermoderador

		// permisos para rangos (no full)
		'moacp' => false, 	// Acceso al Panel de Moderacion
		'moadm' => false, 	// Aceptar denuncias de mensajes
		'moayca'=> false, 	// Abrir/Cerrar Posts Ajax
		'moaydcp'=> false, 	// Acciones de revision
		'mocc'  => false,		// Comentarios desaprobados
		'mocdf' => false,		// Cancelar denuncias de fotos
		'mocdm' => false,		// Cancelar denuncias de mensajes
		'mocdp' => false,		// Cancelar denuncias de posts
		'mocdu' => false,		// Cancelar denuncias de usuarios
		'mocepc'=> false,		// Comentarios en Post Cerrado
		'mocp'  => false,		// Posts desaprobados
		'modu'  => false,		// Desbanear Usuarios
		'moecf'=> false, 		// Eliminar Comentarios de Fotos
		'moecm'=> false, 		// Eliminar Comentarios de Muros
		'moecp'=> false, 		// Eliminar Comentarios de Posts
		'moedcopo'=> false, 	// Editar Comentarios de Posts
		'moedfo'=> false,		// Editar Fotos
		'moedpo'=> false,		// Editar Posts
		'moef' => false, 		// Eliminar Fotos
		'moep'  => false, 	// Eliminar Posts
		'moepm'=> false, 		// Eliminar Publicaciones de Muros
		'moop'  => false, 	// Ocultar Posts
		'morf'  => false, 	// Papelera de fotos
		'morp'  => false, 	// Papelera de posts
		'most'  => false, 	// Fijar Posts
		'mosu'  => false, 	// Suspender Usuarios
		'moub'  => false, 	// Usar el buscador
		'movcud'=> false, 	// Ver cuentas desactivadas
		'movcus'=> false, 	// Ver cuentas baneadas
		'movub' => false, 	// Usuarios baneados

		// Globales
		'godp' => false, 	// Puntuar Posts
		'godpc'=> false, 	// Eliminar comentarios propios
		'goepc'=> false, 	// Editar comentarios propios
		'gopcf'=> false, 	// Publicar Comentarios en Fotos
		'gopcp'=> false, 	// Publicar Comentarios en Posts
		'gopf' => false, 	// Publicar Fotos
		'gopp' => false, 	// Publicar Posts
		'gorpap'=> false, // Revisar Posts
		'govpn'=> false, 	// Votar negativo
		'govpp'=> false, 	// Votar postivo
		'govwm'=> false, 	// Acceso en mantenimiento

		// Valores numéricos
		'goaf'  => 0, // Anti-flood
		'gopfd' => 0, // Puntos por dia
		'gopfp' => 0, // Puntos por post
	];

	public static function definitions(): array {
	   $out = [];
	   $walk = function(array $tree) use (&$out, &$walk) {
	      foreach ($tree as $node) {
	        	if (isset($node['code'])) {
	         	$out[$node['code']] = $node['type'] === 'bool' ? false : 0;
	            continue;
	         }
	         $walk($node);
	      }
	   };
	   $walk(self::TREE);
	   return $out;
	}


	public static function resolve(string $path): ?array {
	   // Primero intenta resolver por ruta (como '{categoria}.{subcategoria}.{item}')
	   $node = self::TREE;
	   foreach (explode('.', $path) as $segment) {
	      if (!isset($node[$segment])) {
	         break;
	      }
	      $node = $node[$segment];
	   }
	   // Si encontró el nodo con código, devuélvelo
	   if (isset($node['code'])) {
	      return $node;
	   }
	   // Si no encontró por ruta, busca directamente por código corto (como '{categoria_corta} = gopp')
	   foreach (self::TREE as $category) {
	      $found = self::searchByCode($category, $path);
	      if ($found) {
	         return $found;
	      }
	   }
	   
	   return null;
	}

	private static function searchByCode(array $node, string $code): ?array {
	   foreach ($node as $key => $value) {
	      if (isset($value['code'])) {
	         if ($value['code'] === $code) {
	            return $value;
	         }
	      } else {
	         $result = self::searchByCode($value, $code);
	         if ($result) {
	            return $result;
	         }
	      }
	   }
	   return null;
	}
}